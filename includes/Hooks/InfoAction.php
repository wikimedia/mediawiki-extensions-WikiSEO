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

use Html;
use IContextSource;
use MediaWiki\Extension\WikiSEO\Validator;
use MediaWiki\Hook\InfoActionHook;
use MediaWiki\MediaWikiServices;
use Message;
use PageProps;
use RepoGroup;
use Title;

class InfoAction implements InfoActionHook {
	/**
	 * @var RepoGroup
	 */
	private $repoGroup;

	/**
	 * InfoAction constructor.
	 *
	 * @param RepoGroup $repoGroup
	 */
	public function __construct( RepoGroup $repoGroup ) {
		$this->repoGroup = $repoGroup;
	}

	/**
	 * Adds SEO page props as a table to the page when calling with ?action=info
	 *
	 * @param IContextSource $context
	 * @param array &$pageInfo
	 * @return bool|void
	 */
	public function onInfoAction( $context, &$pageInfo ) {
		if ( method_exists( MediaWikiServices::class, 'getPageProps' ) ) {
			// MW 1.36+
			$properties = MediaWikiServices::getInstance()->getPageProps()->getProperties(
				$context->getTitle(),
				Validator::getValidParams()
			);
		} else {
			$properties = PageProps::getInstance()->getProperties(
				$context->getTitle(),
				Validator::getValidParams()
			);
		}

		$properties = array_shift( $properties );

		if ( $properties === null || count( $properties ) === 0 ) {
			return;
		}

		$pageInfo['header-seo'] = [
			[
				sprintf(
					'<h3>%s</h3>',
					( new Message( 'wiki-seo-pageinfo-header-description' ) )->escaped()
				),
				sprintf(
					'<h3>%s</h3>',
					( new Message( 'wiki-seo-pageinfo-header-content' ) )->escaped()
				),
			]
		];

		foreach ( $properties as $param => $value ) {
			switch ( $param ) {
				case 'keywords':
					$content = $this->formatKeywords( $value );
					break;

				case 'image':
					$content = $this->formatImage( $value );
					break;

				case 'author':
					$content = $this->formatAuthor( $value );
					break;

				default:
					$content = sprintf( '%s', strip_tags( $value ) );
					break;
			}

			$description = new Message( sprintf( 'wiki-seo-param-%s-description', $param ) );
			if ( $description->exists() ) {
				$description = sprintf(
					'<b>%s</b> (<code>%s</code>)<br>%s',
					( new Message( sprintf( 'wiki-seo-param-%s', $param ) ) )->escaped(),
					$param,
					$description->parse()
				);
			} else {
				$description = sprintf(
					'<b>%s</b> (<code>%s</code>)',
					( new Message( sprintf( 'wiki-seo-param-%s', $param ) ) )->escaped(),
					$param
				);
			}

			$pageInfo['header-seo'][] = [
				$description,
				$content
			];
		}

		$belowMessage = new Message( 'wiki-seo-pageinfo-below' );

		$pageInfo['header-seo'][] = [
			'below',
			$belowMessage->parse()
		];
	}

	/**
	 * Explodes a comma separated list and maps it into an ul list
	 *
	 * @param string|null $value
	 * @return string
	 */
	private function formatKeywords( ?string $value ): string {
		return sprintf( '<ul>%s</ul>', implode( '', array_map( static function ( $keyword ) {
			return sprintf( '<li>%s</li>', trim( strip_tags( $keyword ) ) );
		}, explode( ',', $value ?? '' ) ) ) );
	}

	/**
	 * Formats an image to a 200px thumbnail for display
	 *
	 * @param string|null $value
	 * @return string
	 */
	private function formatImage( ?string $value ): string {
		$title = Title::newFromText( $value, NS_FILE );

		if ( $title === null || !$title->exists() || !$title->inNamespace( NS_FILE ) ) {
			return $value;
		}

		$file = $this->repoGroup->findFile( $title->getDBkey() );

		return Html::rawElement( 'img', [
			'src' => $file->transform( [ 'width' => 200 ] )->getUrl(),
			'alt' => $title->getBaseText(),
			'width' => 200,
			'style' => 'height: auto',
		] );
	}

	/**
	 * Formats the author link into an internal link
	 *
	 * @param string|null $value
	 * @return string
	 */
	private function formatAuthor( ?string $value ): string {
		$parsed = parse_url( $value ?? '' );
		if ( $parsed === false || empty( $parsed['path'] ) ) {
			return $value;
		}

		$title = Title::newFromText( $parsed['path'], NS_USER );

		if ( $title === null ) {
			return $value;
		}

		return Html::rawElement( 'a', [
			'href' => $title->getFullURL(),
		], $title->prefixedText );
	}
}
