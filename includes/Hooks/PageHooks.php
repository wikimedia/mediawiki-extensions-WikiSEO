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

use IContextSource;
use MediaWiki\Extension\WikiSEO\Validator;
use MediaWiki\Extension\WikiSEO\WikiSEO;
use MediaWiki\Hook\BeforePageDisplayHook;
use MediaWiki\Hook\InfoActionHook;
use Message;
use OutputPage;
use PageProps;
use Skin;

/**
 * Hooks to run relating the page
 */
class PageHooks implements BeforePageDisplayHook, InfoActionHook {

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
	 * Adds SEO page props as a table to the page when calling with ?action=info
	 *
	 * @param IContextSource $context
	 * @param array &$pageInfo
	 * @return bool|void
	 */
	public function onInfoAction( $context, &$pageInfo ) {
		$properties = PageProps::getInstance()->getProperties(
			$context->getTitle(),
			Validator::$validParams
		);

		$properties = array_shift( $properties );

		if ( count( $properties ) === 0 ) {
			return;
		}

		$pageInfo['header-seo'] = [];

		foreach ( $properties as $param => $value ) {
			$content = sprintf( '%s', strip_tags( $value ) );
			// Explodes a comma separated list and maps it into an ul list
			if ( $param === 'keywords' ) {
				$content = sprintf( '<ul>%s</ul>', implode( '', array_map( function ( $keyword ) {
					return sprintf( '<li>%s</li>', trim( strip_tags( $keyword ) ) );
				}, explode( ',', $value ) ) ) );
			}

			$pageInfo['header-seo'][] = [
				new Message( sprintf( 'wiki-seo-param-%s', $param ) ),
				$content
			];
		}

		$belowMessage = new Message( 'wiki-seo-pageinfo-below' );

		$pageInfo['header-seo'][] = [
			'below',
			$belowMessage->parse()
		];
	}
}
