<?php
/**
 * Body file for extension WikiSEO.
 *
 * @file
 * @ingroup Extensions
 */

namespace Octfx\WikiSEO;

use Octfx\WikiSEO\Generator\MetaTag;
use OutputPage;
use Parser;
use PPFrame;

class WikiSEO {
	private static $namespace = '\Octfx\WikiSEO';

	//array of valid parameter names
	protected static $valid_params = [ 'title', 'title_mode', //append, prepend, replace
		'title_separator', 'keywords', 'description', 'google-site-verification', 'robots', 'google', 'googlebot', 'og:image', 'og:image:width', 'og:image:height', 'og:type', 'og:site_name', 'og:locale', 'og:url', 'og:title', 'og:updated_time', 'article:author', 'article:publisher', 'article:published_time', 'article:modified_time', 'article:section', 'article:tag', 'twitter:card', 'twitter:site', 'twitter:domain', 'twitter:creator', 'twitter:image:src', 'twitter:description', 'DC.date.issued', 'DC.date.created', 'name', ];

	protected static $tag_types = [ 'title' => 'title', 'keywords' => 'meta', 'description' => 'meta', 'google-site-verification' => 'meta', 'robots' => 'meta', 'google' => 'meta', 'googlebot' => 'meta', 'og:image' => 'property', 'og:image:width' => 'property', 'og:image:height' => 'property', 'og:type' => 'property', 'og:site_name' => 'property', 'og:locale' => 'property', 'og:url' => 'property', 'og:title' => 'property', 'og:updated_time' => 'property', 'article:author' => 'property', 'article:publisher' => 'property', 'article:published_time' => 'property', 'article:modified_time' => 'property', 'article:section' => 'property', 'article:tag' => 'property', 'fb:admins' => 'property', 'fb:app_id' => 'property', 'twitter:card' => 'meta', 'twitter:site' => 'meta', 'twitter:domain' => 'meta', 'twitter:creator' => 'meta', 'twitter:image:src' => 'meta', 'twitter:description' => 'meta', 'DC.date.issued' => 'property', 'DC.date.created' => 'property', 'name' => 'property', ];
	//valid title modes
	protected static $valid_title_modes = [ 'prepend', 'append', 'replace' ];
	//allow other parameter names... these will be converted internally
	protected static $convert_params = [ 'metakeywords' => 'keywords', 'metak' => 'keywords', 'metadescription' => 'description', 'metad' => 'description', 'titlemode' => 'title_mode', 'title mode' => 'title_mode' ];
	//parameters which should be parsed if possible to allow for the expansion of templates
	protected static $parse_params = [ 'title', 'description', 'keywords' ];

	//the value for the html title tag
	protected static $title = '';
	//prepend, append or replace the new title to the existing title
	protected static $title_mode = 'replace';
	//the separator to use when using append or prepend modes
	protected static $title_separator = ' - ';

	//array of meta name values
	protected static $meta = [];
	//array of meta property values
	protected static $property = [];


	private $googleSiteKey;
	private $generators;
	private $generatorInstances = [];

	private $errors = [];


	/**
	 * @var Parser
	 */
	private $parser;

	/**
	 * @var TagParser
	 */
	private $tagParser;

	/**
	 * @var array
	 */
	private $parsedInput;

	public function __construct( Parser $parser ) {
		global $wgGoogleSiteVerificationKey, $wgMetadataGenerators;

		$this->googleSiteKey = $wgGoogleSiteVerificationKey;
		$this->generators = $wgMetadataGenerators;
		$this->parser = $parser;
		$this->tagParser = new TagParser();

		$this->createGenerators();
	}

	public function addSeoTagContent( $inputString ) {
		$this->parsedInput = $this->tagParser->parseText( $inputString );
	}

	public function addParserFunctionArgs( array $args ) {
		$this->parsedInput = $this->tagParser->parseArgs( $args );
	}

	private function createGenerators() {
		$this->generatorInstances[] = new MetaTag();

		foreach ( $this->generators as $generator ) {
			$classPath = "\Octfx\WikiSEO\Generator\{$generator}";

			if ( !class_exists( $classPath ) ) {
				$this->errors[] = wfMessage( 'wiki-seo-invalid-generator', $generator )->parse();
			} else {
				$this->generatorInstances[] = new $classPath();
			}
		}
	}

	public function getHtmlOutput() {
		if ( empty( $this->parsedInput ) && $this->googleSiteKey === null ) {
			$this->errors[] = wfMessage( 'wiki-seo-empty-attr' );

			return $this->makeErrorHtml();
		}

		return $this->makeMetadataHtml();
	}

	private function makeErrorHtml() {
		$text = implode( '<br>', $this->errors );

		return "<div class='errorbox'>{$text}</div>";
	}

	/**
	 * Renders the parameters as HTML comment tags in order to cache them in the Wiki text.
	 *
	 * When MediaWiki caches pages it does not cache the contents of the <head> tag, so
	 * to propagate the information in cached pages, the information is stored
	 * as HTML comments in the Wiki text.
	 *
	 * @return string A HTML string of comments
	 */
	private function makeMetadataHtml() {
		$template = "<!--wiki-seo-data-start\n%swiki-seo-data-end-->";

		$validator = new Validator();

		$data = '';

		foreach ( $validator->validateParams( $this->parsedInput ) as $k => $v ) {
			if ( !empty( $v ) ) {
				$data .= 'WikiSEO:' . $k . ';' . base64_encode( $v ) . "\n";
			}
		}

		return sprintf( $template, $data );
	}

	/**
	 * Parse the values input from the <seo> tag extension
	 *
	 * @param string $input The text content of the tag
	 * @param array $args The HTML attributes of the tag
	 * @param Parser $parser The active Parser instance
	 *
	 * @return string The HTML comments of cached attributes
	 */
	public static function fromTag( $input, array $args, Parser $parser ) {
		$seo = new WikiSEO( $parser );
		$seo->addSeoTagContent( $input );

		return $seo->getHtmlOutput();
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
	public static function fromParserFunction( Parser $parser, PPFrame $frame, array $args ) {
		$parsedArgs = [];

		foreach ( $args as $arg ) {
			$parsedArgs[] = trim( $frame->expand( $arg ) );
		}

		$seo = new WikiSEO( $parser );
		$seo->addParserFunctionArgs( $parsedArgs );

		return [ $seo->getHtmlOutput(), 'noparse' => true, 'isHTML' => true ];
	}


	/**
	 * Processes params (assumed valid) and sets them as class properties
	 * @param array $params Array of pre-validated params
	 * @param Parser $parser If passed, the parser will be used to recursively parse all params
	 * @return array An array of processed params
	 */
	protected static function processParams( $params, $parser = null ) {
		global $wgGoogleSiteVerificationKey, $wgFacebookAdmins, $wgFacebookAppID;

		//correct params for compatibility with "HTML Meta and Title" extension
		foreach ( self::$convert_params as $from => $to ) {
			if ( isset( $params[$from] ) ) {
				$params[$to] = $params[$from];
				unset( $params[$from] );
			}
		}

		$processed = [];

		if ( $wgGoogleSiteVerificationKey !== null ) {
			$processed['google-site-verification'] = $wgGoogleSiteVerificationKey;
		}

		if ( $wgFacebookAppID !== null ) {
			$processed['fb:app_id'] = filter_var( $wgFacebookAppID, FILTER_SANITIZE_NUMBER_INT );
		}

		if ( $wgFacebookAdmins !== null && is_array( $wgFacebookAdmins ) ) {
			$admins = '';
			foreach ( $wgFacebookAdmins as $admin ) {
				$admins .= filter_var( $admin, FILTER_SANITIZE_NUMBER_INT ) . ',';
			}
			rtrim( $admins, ',' );
			$processed['fb:admins'] = $admins;
		} elseif ( $wgFacebookAdmins !== null ) {
			$processed['fb:admins'] = filter_var( $wgFacebookAdmins, FILTER_SANITIZE_NUMBER_INT );
		}

		//ensure only valid parameter names are processed
		foreach ( self::$valid_params as $p ) {
			if ( isset( $params[$p] ) ) {
				//if the parser has been passed and the param is parsable parse it, else simply assign it
				if ( $parser !== null && in_array( $p, self::$parse_params ) ) {
					$processed[$p] = $parser->recursiveTagParseFully( $params[$p] );
					$processed[$p] = strip_tags( $processed[$p] );
				} else {
					$processed[$p] = $params[$p];
				}
			}
		}
		//set the processed values as class properties
		foreach ( $processed as $k => $v ) {
			if ( $k === 'title' ) {
				self::$title = $v;
			} else {
				if ( $k === 'title_mode' && in_array( $v, self::$valid_title_modes ) ) {
					self::$title_mode = $v;
				} else {
					if ( $k === 'title_separator' ) {
						self::$title_separator = ' ' . $v . ' ';
					} else {
						if ( isset( self::$tag_types[$k] ) && self::$tag_types[$k] === 'meta' ) {
							self::$meta[$k] = $v;
						} else {
							if ( isset( self::$tag_types[$k] ) && self::$tag_types[$k] === 'property' ) {
								self::$property[$k] = $v;
							}
						}
					}
				}
			}
		}

		return $processed;
	}

	/**
	 * Convert the attributed cached as HTML comments back into an attribute array
	 *
	 * This method is called by the OutputPageBeforeHTML hook
	 *
	 * @param OutputPage $out
	 * @param string $text
	 */
	public static function loadDataFromWikiText( OutputPage $out, &$text ) {
		# Extract meta keywords
		if ( !preg_match_all( '/^(?:<p>)?WikiSEO:([\.:a-zA-Z_-]+);([0-9a-zA-Z\+\/]+=*)\n?\r?$/m', $text, $matches, PREG_SET_ORDER ) ) {
			return;
		}

		$params = [];
		foreach ( $matches as $match ) {
			$params[$match[1]] = base64_decode( $match[2] );
		}
		$text = preg_replace( '/<!--wiki-seo-data-start-->.*<!--wiki-seo-data-end-->/s', '', $text );
		self::processParams( $params );
	}

	/**
	 * Modify the HTML to set the relevant tags to the specified values
	 *
	 * This method is called by the BeforePageDisplay hook
	 *
	 * @param OutputPage $out
	 */
	public static function modifyHTML( OutputPage $out ) {
		global $wgAddJSONLD;

		$jsonLD = '<script type="application/ld+json">{"@context" : "http://schema.org",';

		//set title
		if ( !empty( self::$title ) ) {
			switch ( self::$title_mode ) {
				case 'append':
					$title = $out->getPageTitle() . self::$title_separator . self::$title;
					break;
				case 'prepend':
					$title = self::$title . self::$title_separator . $out->getPageTitle();
					break;
				case 'replace':
				default:
					$title = self::$title;
			}
			$title = preg_replace( "/\r|\n/", "", $title );
			$out->setHTMLTitle( $title );
			$out->addHeadItem( "og:title", Html::element( 'meta', [ 'property' => 'og:title', 'content' => $title ] ) );
			$jsonLD .= '"name":"' . $title . '","headline":"' . $title . '",';
		}
		//set meta tags
		if ( !empty( self::$meta ) ) {
			foreach ( self::$meta as $name => $content ) {
				$content = preg_replace( "/\r|\n/", "", $content );
				if ( $name == 'description' ) {
					if ( strlen( $content ) > 150 ) {
						$content = substr( $content, 0, 150 ) . '...';
					}
					$out->addMeta( $name, $content );
					$out->addMeta( "twitter:description", $content );
					$out->addHeadItem( "og:description", Html::element( 'meta', [ 'property' => 'og:description', 'content' => $content ] ) );
					$jsonLD .= '"description":"' . $content . '",';
				} else {
					$out->addMeta( $name, $content );
				}
			}
		}
		//set property tags
		if ( !empty( self::$property ) ) {
			if ( isset( self::$property['og:type'] ) ) {
				$jsonLD .= '"@type" : "' . ucfirst( self::$property['og:type'] ) . '"';
			}

			if ( isset( self::$property['name'] ) ) {
				$jsonLD .= ',"name" : "' . self::$property['name'] . '","headline":"' . self::$property['name'] . '"';
			}

			if ( isset( self::$property['article:modified_time'] ) ) {
				$jsonLD .= ',"datePublished" : "' . self::$property['article:modified_time'] . '","dateModified" : "' . self::$property['article:modified_time'] . '"';
			}

			if ( isset( self::$property['og:image'] ) ) {
				$jsonLD .= ',"image" : "' . self::$property['og:image'] . '"';
			}

			if ( isset( self::$property['og:url'] ) ) {
				$jsonLD .= ',"url" : "' . self::$property['og:url'] . '", "mainEntityOfPage":"' . self::$property['og:url'] . '"';
			}

			if ( isset( self::$property['article:author'] ) ) {
				$jsonLD .= ',"publisher":{"@type" : "Organization","name" : "Star Citizen Wiki", "logo": { "@type": "ImageObject", "url": "https://v3.star-citizen.wiki/images/e/ef/Star_Citizen_Wiki_Logo.png"}}, "author":{"@type":"Person","name":"' . self::$property['article:author'] . '"}';
			}

			$jsonLD = $jsonLD . '}</script>';

			if ( $wgAddJSONLD === true ) {
				$out->addHeadItem( 'jsonld', $jsonLD );
			}

			foreach ( self::$property as $property => $content ) {
				$content = preg_replace( "/\r|\n/", "", $content );
				$out->addHeadItem( $property, Html::element( 'meta', [ 'property' => $property, 'content' => $content ] ) . "\n" );
			}
		}

		return true;
	}
}
