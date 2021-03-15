<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\WikiSEO\Hooks;

use Html;
use IContextSource;
use MediaWiki\Extension\WikiSEO\Validator;
use MediaWiki\Hook\InfoActionHook;
use MediaWiki\MediaWikiServices;
use Message;
use PageProps;
use Title;

class InfoAction implements InfoActionHook {

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

		$pageInfo['header-seo'] = [
			[
				sprintf(
					'<h3>%s</h3>',
					( new Message( 'wiki-seo-pageinfo-header-description' ) )->plain()
				),
				sprintf(
					'<h3>%s</h3>',
					( new Message( 'wiki-seo-pageinfo-header-content' ) )->plain()
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

				default:
					$content = sprintf( '%s', strip_tags( $value ) );
					break;
			}

			$description = new Message( sprintf( 'wiki-seo-param-%s-description', $param ) );
			if ( $description->exists() ) {
				$description = sprintf(
					'<b>%s</b> (<code>%s</code>)<br>%s',
					( new Message( sprintf( 'wiki-seo-param-%s', $param ) ) )->plain(),
					$param,
					$description->text()
				);
			} else {
				$description = sprintf(
					'<b>%s</b> (<code>%s</code>)',
					( new Message( sprintf( 'wiki-seo-param-%s', $param ) ) )->text(),
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
	 * @param string $value
	 * @return string
	 */
	private function formatKeywords( $value ): string {
		return sprintf( '<ul>%s</ul>', implode( '', array_map( static function ( $keyword ) {
			return sprintf( '<li>%s</li>', trim( strip_tags( $keyword ) ) );
		}, explode( ',', $value ) ) ) );
	}

	/**
	 * Formats an image to a 200px thumbnail for display
	 *
	 * @param string $value
	 * @return string
	 */
	private function formatImage( $value ): string {
		$title = Title::newFromText( $value );

		if ( $title === null ) {
			return $value;
		}

		$file = MediaWikiServices::getInstance()->getRepoGroup()->findFile( $title->getDBkey() );

		return Html::rawElement( 'img', [
			'src' => $file->transform( [ 'width' => 200 ] )->getUrl(),
			'alt' => $title->getBaseText(),
			'width' => 200,
			'style' => 'height: auto',
		] );
	}
}
