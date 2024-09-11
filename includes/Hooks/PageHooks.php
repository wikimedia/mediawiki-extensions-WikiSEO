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

namespace MediaWiki\Extension\WikiSEO\Hooks;

use Config;
use DeferrableUpdate;
use ExtensionRegistry;
use Html;
use MediaWiki\Extension\WikiSEO\DeferredDescriptionUpdate;
use MediaWiki\Extension\WikiSEO\OverwritePageImageProp;
use MediaWiki\Extension\WikiSEO\WikiSEO;
use MediaWiki\Hook\BeforePageDisplayHook;
use MediaWiki\Revision\RenderedRevision;
use MediaWiki\Storage\Hook\RevisionDataUpdatesHook;
use OutputPage;
use ParserOutput;
use Skin;
use Title;

/**
 * Hooks to run relating the page
 */
class PageHooks implements BeforePageDisplayHook, RevisionDataUpdatesHook {
	/**
	 * @var Config
	 */
	private $mainConfig;

	/**
	 * PageHooks constructor.
	 *
	 * @param Config $config
	 */
	public function __construct( Config $config ) {
		$this->mainConfig = $config;
	}

	/**
	 * Queries the page properties for WikiSEO data and adds it as meta tags
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		$seo = new WikiSEO();
		$seo->setMetadataFromPageProps( $out );
		$seo->addMetadataToPage( $out );
	}

	/**
	 * If WikiSeoEnableAutoDescription is enabled _and_ no manual description was defined
	 * We'll push a deferred DescriptionUpdate
	 *
	 * @param Title $title
	 * @param RenderedRevision $renderedRevision
	 * @param DeferrableUpdate[] &$updates
	 * @return void
	 * @see DeferredDescriptionUpdate
	 */
	public function onRevisionDataUpdates( $title, $renderedRevision, &$updates ): void {
		$output = $renderedRevision->getRevisionParserOutput();

		if ( $output === null ) {
			return;
		}

		$this->overwritePageImageProp( $title, $output, $updates );

		$autoEnabled = $this->mainConfig->get( 'WikiSeoEnableAutoDescription' );

		$currentDescription = $output->getPageProperty( 'description' );
		$manualDescription = $output->getPageProperty( 'manualDescription' );

		if ( !$autoEnabled || $manualDescription === true ) {
			return;
		}

		$updates[] = new DeferredDescriptionUpdate(
			$title,
			$currentDescription !== false ? $currentDescription : null,
			$this->mainConfig->get( 'WikiSeoTryCleanAutoDescription' )
		);
	}

	/**
	 * Overwrite page prop 'page_imgaes_free' if PageImages is loaded and 'wgWikiSeoOverwritePageImage' is true
	 *
	 * @param Title $title
	 * @param ParserOutput $output
	 * @param array &$updates
	 * @return void
	 */
	private function overwritePageImageProp( Title $title, ParserOutput $output, &$updates ) {
		$imageProp = $output->getPageProperty( 'image' );

		if ( $imageProp === null || !$this->mainConfig->get( 'WikiSeoOverwritePageImage' ) ||
			!ExtensionRegistry::getInstance()->isLoaded( 'PageImages' ) ) {
			return;
		}

		$updates[] = new OverwritePageImageProp( $title, $imageProp );
	}

	/**
	 * Sets, or overwrites, the '<link rel="canonical"' tag within the page HTML head,
	 * if the "canonical" parameter was set.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/OutputPageAfterGetHeadLinksArray
	 *
	 * @param string[] &$tags
	 * @param OutputPage $output
	 */
	public function onOutputPageAfterGetHeadLinksArray( &$tags, OutputPage $output ) {
		$seo = new WikiSEO();
		$seo->setMetadataFromPageProps( $output );
		$canonicalTitle = $seo->getMetadataValue( 'canonical' );
		if ( empty( $canonicalTitle ) ) {
			return;
		}

		// Annoyingly, when MediaWiki adds a canonical link (when
		// $wgEnableCanonicalServerLink is true), it uses a numeric
		// key for it, rather than an obvious key like 'canonical'.
		// So in order to overwrite it,
		// we have to manually find this tag in the array.
		$canonicalKey = 'canonical';
		foreach ( $tags as $key => $tag ) {
			if ( strpos( $tag, '<link rel="canonical"' ) !== false ) {
				$canonicalKey = $key;
				break;
			}
		}
		$tags[$canonicalKey] = Html::element(
			'link', [
				'rel' => 'canonical',
				'href' => $canonicalTitle
			]
		);
	}
}
