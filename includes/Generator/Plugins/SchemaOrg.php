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

namespace MediaWiki\Extension\WikiSEO\Generator\Plugins;

use Exception;
use InvalidArgumentException;
use MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator;
use MediaWiki\Extension\WikiSEO\Generator\GeneratorInterface;
use MediaWiki\Extension\WikiSEO\WikiSEO;
use MediaWiki\Title\Title;
use OutputPage;

class SchemaOrg extends AbstractBaseGenerator implements GeneratorInterface {
	/**
	 * Valid Tags for this generator
	 *
	 * @var array
	 */
	protected $tags = [
		'type',
		'description',
		'keywords',
		'modified_time',
		'published_time',
		'section'
	];

	/**
	 * Tag name conversions for this generator
	 *
	 * @var array
	 */
	protected $conversions = [
		'type' => '@type',

		'section' => 'articleSection',

		'published_time' => 'datePublished',
		'modified_time'  => 'dateModified'
	];

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

		$this->setModifiedPublishedTime();
	}

	/**
	 * Add the metadata to the OutputPage
	 *
	 * @return void
	 */
	public function addMetadata(): void {
		$template = '<script type="application/ld+json">%s</script>';

		$meta = [
			'@context' => 'http://schema.org',
			'@type' => $this->getTypeMetadata(),
			'name' => $this->outputPage->getHTMLTitle(),
			'headline' => $this->outputPage->getHTMLTitle(),
			'mainEntityOfPage' => strip_tags( $this->outputPage->getPageTitle() ),
		];

		if ( $this->outputPage->getTitle() !== null ) {
			$url = $this->outputPage->getTitle()->getFullURL();

			$url = WikiSEO::protocolizeUrl( $url, $this->outputPage->getRequest() );

			$meta['identifier'] = $url;
			$meta['url'] = $url;
		}

		foreach ( $this->tags as $tag ) {
			if ( array_key_exists( $tag, $this->metadata ) ) {
				$convertedTag = $this->conversions[$tag] ?? $tag;

				$meta[$convertedTag] = $this->metadata[$tag];
			}
		}

		$meta['image'] = $this->getImageMetadata();
		$meta['author'] = $this->getAuthorMetadata();
		$meta['publisher'] = $this->getAuthorMetadata();
		$meta['potentialAction'] = $this->getSearchActionMetadata();

		$this->outputPage->addHeadItem(
			'jsonld-metadata',
			sprintf(
				$template,
				json_encode( $meta ),
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getAllowedParameterNames(): array {
		return $this->tags;
	}

	/**
	 * Generate proper schema.org type in order to pass validation
	 *
	 * @return string
	 */
	private function getTypeMetadata(): string {
		return $this->metadata['type'] ?? 'Article';
	}

	/**
	 * Generate jsonld metadata from the supplied file name, configured default image or wiki logo
	 *
	 * @return array
	 */
	private function getImageMetadata(): array {
		$data = [
			'@type' => 'ImageObject',
		];

		$this->setFallbackImageIfEnabled();

		if ( isset( $this->metadata['image'] ) ) {
			$image = $this->metadata['image'];

			try {
				$file = $this->getFileObject( $image );

				return array_merge( $data, $this->getFileInfo( $file ) );
			} catch ( InvalidArgumentException ) {
				// Fallthrough
			}
		}

		// Logo as Fallback
		return $this->getLogoMetadata();
	}

	/**
	 * Add the sitename as the author
	 *
	 * @return array
	 */
	private function getAuthorMetadata(): array {
		$sitename = $this->getConfigValue( 'Sitename' ) ?? '';
		$server = $this->getConfigValue( 'Server' ) ?? '';

		$logo = $this->getLogoMetadata();

		if ( !empty( $logo ) ) {
			$logo['caption'] = $sitename;
		}

		return [
			'@type' => 'Organization',
			'name' => $sitename,
			'url' => $server,
			'logo' => $logo,
		];
	}

	/**
	 * Tries to get the main logo form config as an expanded url
	 *
	 * @return array
	 */
	private function getLogoMetadata(): array {
		$data = [
			'@type' => 'ImageObject',
		];

		if ( $this->getConfigValue( 'WikiSeoDisableLogoFallbackImage' ) === true ) {
			return $data;
		}

		try {
			$logo = $this->getWikiLogo();
			if ( $logo !== false ) {
				$data['url'] = $logo;
			} else {
				$data = [];
			}
		} catch ( Exception ) {
			// Uh oh either there was a ConfigException or there was an error expanding the URL.
			// We'll bail out.
			$data = [];
		}

		return $data;
	}

	/**
	 * Add search action metadata
	 * https://gitlab.com/hydrawiki/extensions/seo/blob/master/SEOHooks.php
	 *
	 * @return array
	 */
	private function getSearchActionMetadata(): array {
		$searchPage = Title::newFromText( 'Special:Search' );

		if ( $searchPage !== null ) {
			$search =
				$searchPage->getFullURL( [ 'search' => 'search_term' ], false,
					sprintf( '%s://', $this->outputPage->getRequest()->getProtocol() ) );
			$search = str_replace( 'search_term', '{search_term}', $search );

			return [
				'@type' => 'SearchAction',
				'target' => $search,
				'query-input' => 'required name=search_term',
			];
		}

		return [];
	}
}
