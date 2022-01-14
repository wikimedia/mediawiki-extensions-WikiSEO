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

namespace MediaWiki\Extension\WikiSEO\Generator;

use Html;
use MediaWiki\Extension\WikiSEO\Validator;
use MediaWiki\Extension\WikiSEO\WikiSEO;
use MediaWiki\MediaWikiServices;
use OutputPage;

/**
 * Basic metadata tag generator
 * Adds metadata for description, keywords and robots
 *
 * @package MediaWiki\Extension\WikiSEO\Generator
 */
class MetaTag extends AbstractBaseGenerator implements GeneratorInterface {
	protected $tags = [ 'description', 'keywords', 'google_bot' ];

	/**
	 * Initialize the generator with all metadata and the page to output the metadata onto
	 *
	 * @param array $metadata All metadata
	 * @param OutputPage $out The page to add the metadata to
	 *
	 * @return void
	 */
	public function init( array $metadata, OutputPage $out ): void {
		$this->metadata = $metadata;
		$this->outputPage = $out;
	}

	/**
	 * Add the metadata to the OutputPage
	 *
	 * @return void
	 */
	public function addMetadata(): void {
		$this->addGoogleSiteVerification();
		$this->addBingSiteVerification();
		$this->addYandexSiteVerification();
		$this->addAlexaSiteVerification();
		$this->addPinterestSiteVerification();
		$this->addNortonSiteVerification();
		$this->addNaverSiteVerification();
		$this->addFacebookAppId();
		$this->addFacebookAdmins();
		$this->addHrefLangs();
		$this->addNoIndex();

		// Meta tags already set in the page
		$outputMeta = [];
		foreach ( $this->outputPage->getMetaTags() as $metaTag ) {
			$outputMeta[$metaTag[0]] = $metaTag[1];
		}

		foreach ( $this->tags as $tag ) {
			// Only add tag if it doesn't already exist in the output page
			if ( array_key_exists( $tag, $this->metadata ) && !array_key_exists( $tag, $outputMeta ) ) {
				$this->outputPage->addMeta( $tag, $this->metadata[$tag] );
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getAllowedParameterNames(): array {
		return $this->tags;
	}

	/**
	 * Add $wgGoogleSiteVerificationKey from LocalSettings
	 */
	private function addGoogleSiteVerification(): void {
		$googleSiteVerificationKey = $this->getConfigValue( 'GoogleSiteVerificationKey' );

		if ( !empty( $googleSiteVerificationKey ) ) {
			$this->outputPage->addMeta( 'google-site-verification', $googleSiteVerificationKey );
		}
	}

	/**
	 * Add $wgBingSiteVerificationKey from LocalSettings
	 */
	private function addBingSiteVerification(): void {
		$bingSiteVerificationKey = $this->getConfigValue( 'BingSiteVerificationKey' );

		if ( !empty( $bingSiteVerificationKey ) ) {
			$this->outputPage->addMeta( 'msvalidate.01', $bingSiteVerificationKey );
		}
	}

	/**
	 * Add $wgYandexSiteVerificationKey from LocalSettings
	 */
	private function addYandexSiteVerification(): void {
		$yandexSiteVerificationKey = $this->getConfigValue( 'YandexSiteVerificationKey' );

		if ( !empty( $yandexSiteVerificationKey ) ) {
			$this->outputPage->addMeta( 'yandex-verification', $yandexSiteVerificationKey );
		}
	}

	/**
	 * Add $wgAlexaSiteVerificationKey from LocalSettings
	 */
	private function addAlexaSiteVerification(): void {
		$alexaSiteVerificationKey = $this->getConfigValue( 'AlexaSiteVerificationKey' );

		if ( !empty( $alexaSiteVerificationKey ) ) {
			$this->outputPage->addMeta( 'alexaVerifyID', $alexaSiteVerificationKey );
		}
	}

	/**
	 * Add $wgPinterestSiteVerificationKey from LocalSettings
	 */
	private function addPinterestSiteVerification(): void {
		$pinterestSiteVerificationKey = $this->getConfigValue( 'PinterestSiteVerificationKey' );

		if ( !empty( $pinterestSiteVerificationKey ) ) {
			$this->outputPage->addMeta( 'p:domain_verify', $pinterestSiteVerificationKey );
		}
	}

	/**
	 * Add $wgNortonSiteVerificationKey from LocalSettings
	 */
	private function addNortonSiteVerification(): void {
		$nortonSiteVerificationKey = $this->getConfigValue( 'NortonSiteVerificationKey' );

		if ( !empty( $nortonSiteVerificationKey ) ) {
			$this->outputPage->addMeta(
				'norton-safeweb-site-verification',
				$nortonSiteVerificationKey
			);
		}
	}

	/**
	 * Add $wgNaverSiteVerificationKey from LocalSettings
	 */
	private function addNaverSiteVerification(): void {
		$naverSiteVerificationKey = $this->getConfigValue( 'NaverSiteVerificationKey' );

		if ( !empty( $naverSiteVerificationKey ) ) {
			$this->outputPage->addMeta(
				'naver-site-verification',
				$naverSiteVerificationKey
			);
		}
	}

	/**
	 * Add $wgFacebookAppId from LocalSettings
	 */
	private function addFacebookAppId(): void {
		$facebookAppId = $this->getConfigValue( 'FacebookAppId' );

		if ( !empty( $facebookAppId ) ) {
			$this->outputPage->addHeadItem(
				'fb:app_id', Html::element(
					'meta', [
					'property' => 'fb:app_id',
					'content' => $facebookAppId,
					]
				)
			);
		}
	}

	/**
	 * Add $wgFacebookAdmins from LocalSettings
	 */
	private function addFacebookAdmins(): void {
		$facebookAdmins = $this->getConfigValue( 'FacebookAdmins' );

		if ( !empty( $facebookAdmins ) ) {
			$this->outputPage->addHeadItem(
				'fb:admins', Html::element(
					'meta', [
					'property' => 'fb:admins',
					'content' => $facebookAdmins,
					]
				)
			);
		}
	}

	/**
	 * Sets <link rel="alternate" href="url" hreflang="language-area"> elements
	 * Will add a link element for the current page if $wgWikiSeoDefaultLanguage is set
	 */
	private function addHrefLangs(): void {
		$language = $this->getConfigValue( 'WikiSeoDefaultLanguage' );

		$title = $this->outputPage->getTitle();
		if ( !empty( $language ) && $title !== null && in_array( $language, Validator::$isoLanguageCodes, true ) ) {
			$subpage = $title->getSubpageText();
			$languageUtils = MediaWikiServices::getInstance()->getLanguageNameUtils();
			$languageFactory = MediaWikiServices::getInstance()->getLanguageFactory();

			// Title might be a page containing a translation.
			// Change the language and add an alternate link To the root page with the defined default language
			if ( $title->isSubpage() && $languageUtils->isSupportedLanguage( $subpage ) ) {
				$this->outputPage->addHeadItem(
					$language, Html::element(
					'link',
						[
							'rel' => 'alternate',
							'href' => WikiSEO::protocolizeUrl(
								$title->getBaseTitle()->getFullURL(),
								$this->outputPage->getRequest()
							),
							'hreflang' => $language,
						]
					)
				);

				$language = $languageFactory->getLanguage( $subpage )->getHtmlCode();
			}

			$this->outputPage->addHeadItem(
				$language, Html::element(
				'link',
					[
						'rel' => 'alternate',
						'href' => WikiSEO::protocolizeUrl(
							$title->getFullURL(),
							$this->outputPage->getRequest()
						),
						'hreflang' => $language,
					]
				)
			);
		}

		foreach ( $this->metadata as $metaKey => $url ) {
			if ( strpos( $metaKey, 'hreflang' ) === false ) {
				continue;
			}

			$this->outputPage->addHeadItem(
				$metaKey, Html::element(
					'link', [
					'rel' => 'alternate',
					'href' => $url,
					'hreflang' => substr( $metaKey, 9 ),
					]
				)
			);
		}
	}

	/**
	 * Sets the robot policy to noindex
	 */
	private function addNoIndex(): void {
		if ( $this->shouldAddNoIndex() ) {
			$this->outputPage->setIndexPolicy( 'noindex' );
		}
	}

	/**
	 * Check a blacklist of URL parameters and values to see if we should add a noindex meta tag
	 * Based on https://gitlab.com/hydrawiki/extensions/seo/blob/master/SEOHooks.php#L84
	 *
	 * Special Pages are 'noindex,nofollow' by default
	 * @see \Article::getRobotPolicy
	 *
	 * @return bool
	 */
	private function shouldAddNoIndex(): bool {
		$blockedURLParamKeys = [
			'curid', 'diff', 'from', 'group', 'mobileaction', 'oldid',
			'printable', 'profile', 'redirect', 'redlink', 'stableid'
		];

		$blockedURLParamKeyValuePairs = [
			'action' => [
				'delete', 'edit', 'history', 'info',
				'pagevalues', 'purge', 'visualeditor', 'watch'
			],
			'feed' => [ 'rss' ],
			'limit' => [ '500' ],
			'title' => $this->getConfigValue( 'WikiSeoNoindexPageTitles' ) ?? [],
			'veaction' => [
				'edit', 'editsource'
			]
		];

		if ( $this->outputPage->getTitle() === null ) {
			// Bail out
			return false;
		}

		if ( in_array( $this->outputPage->getTitle()->getText(), $blockedURLParamKeyValuePairs['title'], true ) ) {
			return true;
		}

		foreach ( $this->outputPage->getRequest()->getValues() as $key => $value ) {
			if ( in_array( $key, $blockedURLParamKeys, true ) ) {
				return true;
			}

			if ( isset( $blockedURLParamKeyValuePairs[$key] ) && in_array(
					$value,
					$blockedURLParamKeyValuePairs[$key],
					true
				) ) {
				return true;
			}
		}

		return false;
	}
}
