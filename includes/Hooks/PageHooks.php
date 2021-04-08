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
use MediaWiki\Extension\WikiSEO\DeferredDescriptionUpdate;
use MediaWiki\Extension\WikiSEO\WikiSEO;
use MediaWiki\Hook\BeforePageDisplayHook;
use MediaWiki\Revision\RenderedRevision;
use MediaWiki\Storage\Hook\RevisionDataUpdatesHook;
use OutputPage;
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
	 * Extracts the generated SEO HTML comments form the page and adds them as meta tags
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
	 * We'll push an deferred DescriptionUpdate
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

		$autoEnabled = $this->mainConfig->get( 'WikiSeoEnableAutoDescription' );
		if ( (bool)$autoEnabled === false || $output->getProperty( 'manualDescription' ) === true ) {
			return;
		}

		$updates[] = new DeferredDescriptionUpdate(
			$title,
			$this->mainConfig->get( 'WikiSeoTryCleanAutoDescription' )
		);
	}
}
