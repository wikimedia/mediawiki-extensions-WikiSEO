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

		$currentDescription = $this->getPageProperty( $output, 'description' );
		$manualDescription = $this->getPageProperty( $output, 'manualDescription' );

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
		if ( !$this->mainConfig->get( 'WikiSeoOverwritePageImage' ) ||
			$output->getPageProperty( 'image' ) === null ||
			!ExtensionRegistry::getInstance()->isLoaded( 'PageImages' ) ) {
			return;
		}

		$updates[] = new OverwritePageImageProp(
			$title,
			$this->getPageProperty( $output, 'image' )
		);
	}

	/**
	 * TODO: Remove when min version is set to 1.39
	 *
	 * @param ParserOutput $output
	 * @param string $propName
	 * @return mixed
	 */
	private function getPageProperty( ParserOutput $output, string $propName ) {
		if ( method_exists( $output, 'getPageProperty' ) ) {
			return $output->getPageProperty( $propName );
		}

		return $output->getProperty( $propName );
	}
}
