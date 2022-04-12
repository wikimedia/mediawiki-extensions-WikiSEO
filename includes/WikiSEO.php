<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * @file
 */

declare( strict_types=1 );

namespace MediaWiki\Extension\WikiSEO;

use ConfigException;
use MediaWiki\Extension\WikiSEO\Generator\GeneratorInterface;
use MediaWiki\Extension\WikiSEO\Generator\MetaTag;
use MediaWiki\MediaWikiServices;
use OutputPage;
use PageProps;
use Parser;
use ParserOutput;
use PPFrame;
use ReflectionClass;
use ReflectionException;
use Title;
use WebRequest;
use Wikimedia\AtEase\AtEase;

class WikiSEO {
	private const MODE_TAG = 'tag';
	private const MODE_PARSER = 'parser';

	/**
	 * @var string 'tag' or 'parser' used to determine the error message
	 */
	private $mode;

	/**
	 * prepend, append or replace the new title to the existing title
	 *
	 * @var string
	 */
	private $titleMode = 'replace';

	/**
	 * the separator to use when using append or prepend modes
	 *
	 * @var string
	 */
	private $titleSeparator = ' - ';

	/**
	 * @var string[] Array with generator names
	 */
	private $generators;

	/**
	 * @var GeneratorInterface[]
	 */
	private $generatorInstances = [];

	/**
	 * @var string[] Possible error messages
	 */
	private $errors = [];

	/**
	 * @var array
	 */
	private $metadata = [];

	/**
	 * WikiSEO constructor.
	 * Loads generator names from LocalSettings
	 *
	 * @param string $mode the parser mode
	 */
	public function __construct( $mode = self::MODE_PARSER ) {
		$this->setMetadataGenerators();
		$this->instantiateMetadataPlugins();

		$this->mode = $mode;
	}

	/**
	 * Set the metadata by loading the page props from the db or the OutputPage object
	 *
	 * @param OutputPage $outputPage
	 */
	public function setMetadataFromPageProps( OutputPage $outputPage ): void {
		if ( $outputPage->getTitle() === null ) {
			$this->errors[] = wfMessage( 'wiki-seo-missing-page-title' );

			return;
		}

		$result =
		$this->loadPagePropsFromDb( $outputPage->getTitle() ) ??
		$this->loadPagePropsFromOutputPage( $outputPage ) ?? [];

		$this->setMetadata( $result );
	}

	/**
	 * Set an array with metadata key value pairs
	 * Gets validated by Validator
	 *
	 * @param array $metadataArray
	 * @param ParserOutput|null $out ParserOutput is used to set a extension data flag to disable auto description,
	 * even when the flag is active.
	 * The reason is, if a description was provided and does not equal 'auto' or 'textextracts' we want to use it.
	 * @see Validator
	 */
	public function setMetadata( array $metadataArray, ParserOutput $out = null ): void {
		$validMetadata = [];

		// We'll set a flag to don't overwrite manual descriptions
		// If the AutoDescription setting is set
		if ( $out !== null ) {
			if ( isset( $metadataArray['manualDescription'] ) &&
				!in_array( $metadataArray['manualDescription'], [ 'auto', 'textextracts' ], true ) ) {

				// MW 1.38+
				if ( method_exists( $out, 'setPageProperty' ) ) {
					$out->setPageProperty( 'manualDescription', true );
				} else {
					$out->setProperty( 'manualDescription', true );
				}

				$metadataArray['description'] = $metadataArray['manualDescription'];
				unset( $metadataArray['manualDescription'] );
			} else {
				// MW 1.38+
				if ( method_exists( $out, 'unsetPageProperty' ) ) {
					$out->unsetPageProperty( 'manualDescription' );
				} else {
					$out->unsetProperty( 'manualDescription' );
				}
			}
		}

		foreach ( Validator::validateParams( $metadataArray ) as $k => $v ) {
			if ( !empty( $v ) ) {
				$validMetadata[$k] = $v;
			}
		}

		$this->metadata = $validMetadata;
	}

	/**
	 * Add the metadata array as meta tags to the page
	 *
	 * @param OutputPage $out
	 */
	public function addMetadataToPage( OutputPage $out ): void {
		if ( $out->isArticle() && !isset( $out->getRequest()->getQueryValues()['diff'] ) ) {
			$this->modifyPageTitle( $out );
		}

		MediaWikiServices::getInstance()->getHookContainer()->run(
			'WikiSEOPreAddMetadata',
			[
				&$this->metadata,
			]
		);

		foreach ( $this->generatorInstances as $generatorInstance ) {
			$generatorInstance->init( $this->metadata, $out );
			$generatorInstance->addMetadata();
		}
	}

	/**
	 * Set active metadata generators defined in $wgMetdataGenerators
	 * And merges all valid parameter names from the generator to the validator
	 */
	private function setMetadataGenerators(): void {
		$defaultGenerators = [
			'OpenGraph',
			'Twitter',
			'SchemaOrg',
		];

		try {
			$generators = MediaWikiServices::getInstance()
				->getConfigFactory()
				->makeConfig( 'WikiSEO' )
				->get( 'MetadataGenerators' );

			if ( empty( $generators ) ) {
				$generators = $defaultGenerators;
			}
		} catch ( ConfigException $e ) {
			wfLogWarning(
				sprintf(
					'Could not get config for "$wgMetadataGenerators", using default. %s',
					$e->getMessage()
				)
			);

			$generators = $defaultGenerators;
		}

		$this->generators = $generators;
		$this->mergeValidParameterNames();
	}

	/**
	 * Loads all page props for the given page with pp_propnames in Validator::getValidParams()
	 *
	 * @param Title $title
	 * @return null|array Null if empty
	 * @see Validator::getValidParams()
	 */
	private function loadPagePropsFromDb( Title $title ): ?array {
		if ( method_exists( MediaWikiServices::class, 'getPageProps' ) ) {
			// MW 1.36+
			$properties = MediaWikiServices::getInstance()->getPageProps()->getProperties(
				$title,
				Validator::getValidParams()
			);
		} else {
			$properties = PageProps::getInstance()->getProperties(
				$title,
				Validator::getValidParams()
			);
		}

		$properties = array_shift( $properties );

		if ( $properties === null || count( $properties ) === 0 ) {
			return null;
		}

		$result = [];

		foreach ( $properties as $key => $value ) {
			$result[$key] = $value;
		}

		return empty( $result ) ? null : $result;
	}

	/**
	 * Tries to load the page props from OutputPage with keys from Validator::getValidParams()
	 *
	 * @see Validator::getValidParams()
	 *
	 * @param OutputPage $page
	 * @return array|null
	 */
	private function loadPagePropsFromOutputPage( OutputPage $page ): ?array {
		$result = [];

		foreach ( Validator::getValidParams() as $param ) {
			$prop = $page->getProperty( $param );

			if ( $prop !== null ) {
				AtEase::suppressWarnings();
				$value = unserialize( $prop, [ 'allowed_classes' => false ] );
				AtEase::restoreWarnings();

				// Value was serialized
				if ( $value !== false ) {
					$prop = $value;
				}

				$result[$param] = $prop;
			}
		}

		return empty( $result ) ? null : $result;
	}

	/**
	 * Instantiates the metadata generators from $wgMetadataGenerators
	 */
	private function instantiateMetadataPlugins(): void {
		$this->generatorInstances[] = new MetaTag();

		foreach ( $this->generators as $generator ) {
			$classPath = "MediaWiki\\Extension\\WikiSEO\\Generator\\Plugins\\$generator";

			try {
				$class = new ReflectionClass( $classPath );
				$this->generatorInstances[] = $class->newInstance();
			} catch ( ReflectionException $e ) {
				$this->errors[] = wfMessage( 'wiki-seo-invalid-generator', $generator )->parse();
			}
		}
	}

	/**
	 * Finalize everything.
	 * Check for errors and save to props if everything is ok.
	 *
	 * @param ParserOutput $output
	 *
	 * @return string String with errors that happened or empty
	 */
	public function finalize( ParserOutput $output ): string {
		if ( empty( $this->metadata ) ) {
			$message = sprintf( 'wiki-seo-empty-attr-%s', $this->mode );
			$this->errors[] = wfMessage( $message );

			return $this->makeErrorHtml();
		}

		$this->saveMetadataToProps( $output );

		return '';
	}

	/**
	 * @return string Concatenated error strings
	 */
	private function makeErrorHtml(): string {
		$text = implode( '<br>', $this->errors );

		return sprintf( '<div class="errorbox">%s</div>', $text );
	}

	/**
	 * Modifies the page title based on 'titleMode'
	 *
	 * @param OutputPage $out
	 */
	private function modifyPageTitle( OutputPage $out ): void {
		if ( !array_key_exists( 'title', $this->metadata ) ) {
			return;
		}

		$metaTitle = $this->metadata['title'];

		if ( array_key_exists( 'title_separator', $this->metadata ) ) {
			$this->titleSeparator = $this->metadata['title_separator'];
		}

		if ( array_key_exists( 'title_mode', $this->metadata ) ) {
			$this->titleMode = $this->metadata['title_mode'];
		}

		switch ( $this->titleMode ) {
			case 'append':
				$pageTitle = sprintf( '%s%s%s', $out->getPageTitle(), $this->titleSeparator, $metaTitle );
				break;

			case 'prepend':
				$pageTitle = sprintf( '%s%s%s', $metaTitle, $this->titleSeparator, $out->getPageTitle() );
				break;

			case 'replace':
			default:
				$pageTitle = $metaTitle;
				break;
		}

		$pageTitle = preg_replace( "/[\r\n]/", '', $pageTitle );
		$pageTitle = html_entity_decode( $pageTitle, ENT_QUOTES );

		$out->setHTMLTitle( $pageTitle );
	}

	/**
	 * Save the metadata array json encoded to the page props table
	 *
	 * @param ParserOutput $outputPage
	 */
	private function saveMetadataToProps( ParserOutput $outputPage ): void {
		MediaWikiServices::getInstance()->getHookContainer()->run(
			'WikiSEOPreAddPageProps',
			[
				&$this->metadata,
			]
		);

		foreach ( $this->metadata as $key => $value ) {
			// MW 1.38+
			if ( method_exists( $outputPage, 'setPageProperty' ) &&
				method_exists( $outputPage, 'getPageProperty' ) ) {
				if ( $outputPage->getPageProperty( $key ) === null ) {
					$outputPage->setPageProperty( $key, $value );
				}
			} else {
				if ( $outputPage->getProperty( $key ) === false ) {
					$outputPage->setProperty( $key, $value );
				}
			}
		}
	}

	/**
	 * Adds valid tags from all generator instances to the Validator
	 * Automatically called after instantiating all active generators
	 */
	private function mergeValidParameterNames(): void {
		Validator::$validParameterNames = array_unique(
			array_merge(
				Validator::$validParameterNames,
				array_reduce(
					array_map(
						static function ( GeneratorInterface $generator ) {
							return $generator->getAllowedParameterNames();
						},
						$this->generatorInstances
					),
					static function ( array $carry, array $item ) {
						return array_merge( $carry, $item );
					},
					[]
				)
			)
		);
	}

	/**
	 * Parse the values input from the <seo> tag extension
	 *
	 * @param string|null $input The text content of the tag
	 * @param array $args The HTML attributes of the tag
	 * @param Parser $parser The active Parser instance
	 * @param PPFrame $frame
	 *
	 * @return string The HTML comments of cached attributes
	 */
	public static function fromTag( ?string $input, array $args, Parser $parser, PPFrame $frame ): string {
		$seo = new WikiSEO( self::MODE_TAG );
		$tagParser = new TagParser();

		$parsedInput = $tagParser->parseText( $input );
		$parsedInput = array_merge( $parsedInput, $args );
		$tags = $tagParser->expandWikiTextTagArray( $parsedInput, $parser, $frame );

		if ( isset( $tags['description'] ) ) {
			$tags['manualDescription'] = $tags['description'];
			unset( $tags['description'] );
		}
		$tags = array_merge( $seo->loadPagePropsFromDb( $frame->getTitle() ) ?? [], $tags );

		$seo->setMetadata( $tags, $parser->getOutput() );

		return $seo->finalize( $parser->getOutput() );
	}

	/**
	 * Parse the values input from the {{#seo}} parser function
	 *
	 * @param Parser $parser The active Parser instance
	 * @param PPFrame $frame Frame
	 * @param array $args Arguments
	 *
	 * @return array Parser options and the HTML comments of cached attributes
	 */
	public static function fromParserFunction( $parser, PPFrame $frame, array $args ): array {
		$expandedArgs = [];

		foreach ( $args as $arg ) {
			$expandedArgs[] = trim( $frame->expand( $arg ) );
		}

		$seo = new WikiSEO( self::MODE_PARSER );
		$tagParser = new TagParser();

		$args = $tagParser->parseArgs( $expandedArgs );
		if ( isset( $args['description'] ) ) {
			$args['manualDescription'] = $args['description'];
			unset( $args['description'] );
		}

		$args = array_merge( $seo->loadPagePropsFromDb( $frame->getTitle() ) ?? [], $args );

		$seo->setMetadata( $args, $parser->getOutput() );

		$fin = $seo->finalize( $parser->getOutput() );
		if ( !empty( $fin ) ) {
			return [
				$fin,
				'noparse' => true,
				'isHTML' => true,
			];
		}

		// See https://github.com/octfx/wiki-seo/issues/30
		return [ '<!-- WikiSEO -->' ];
	}

	/**
	 * Add the server protocol to the URL if it is missing
	 *
	 * @param string $url URL from getFullURL()
	 * @param WebRequest $request
	 *
	 * @return string
	 */
	public static function protocolizeUrl( string $url, WebRequest $request ): string {
		if ( parse_url( $url, PHP_URL_SCHEME ) === null ) {
			$url = sprintf( '%s:%s', $request->getProtocol(), $url );
		}

		return $url;
	}
}
