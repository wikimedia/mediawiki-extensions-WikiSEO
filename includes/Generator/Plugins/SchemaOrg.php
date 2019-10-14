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

namespace MediaWiki\Extension\WikiSEO\Generator\Plugins;

use MediaWiki\Extension\WikiSEO\Generator\GeneratorInterface;
use MediaWiki\Extension\WikiSEO\WikiSEO;
use OutputPage;

class SchemaOrg implements GeneratorInterface {
	/**
	 * Valid Tags for this generator
	 *
	 * @var array
	 */
	protected $tags = [
		'type',
		'image',
		'description',
		'keywords',
		'published_time',
		'modified_time',
		'section'
	];

	/**
	 * Tag name conversions for this generator
	 *
	 * @var array
	 */
	protected $conversions = [
		'type' => '@type',

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
	public function init( array $metadata, OutputPage $out ) {
		$this->metadata = $metadata;
		$this->outputPage = $out;
	}

	/**
	 * @var array
	 */
	protected $metadata;

	/**
	 * @var OutputPage
	 */
	protected $outputPage;

	/**
	 * Add the metadata to the OutputPage
	 *
	 * @return void
	 */
	public function addMetadata() {
		$template = '<script type="application/ld+json">%s</script>';

		$meta = [
			'@context' => 'http://schema.org',
			'@type' => 'WebPage',
			'name'     => $this->outputPage->getHTMLTitle(),
			'headline' => $this->outputPage->getHTMLTitle(),
		];

		if ( $this->outputPage->getTitle() !== null ) {
			$url = $this->outputPage->getTitle()->getFullURL();

			$url = WikiSEO::protocolizeUrl( $url, $this->outputPage->getRequest() );

			$meta['identifier'] = $url;
			$meta['url'] = $url;
		}

		foreach ( $this->tags as $tag ) {
			if ( array_key_exists( $tag, $this->metadata ) ) {
				$convertedTag = $tag;
				if ( isset( $this->conversions[$tag] ) ) {
					$convertedTag = $this->conversions[$tag];
				}

				$meta[$convertedTag] = $this->metadata[$tag];
			}
		}

		$this->outputPage->addHeadItem(
			'jsonld-metadata',
			sprintf( $template, json_encode( $meta ) )
		);
	}
}
