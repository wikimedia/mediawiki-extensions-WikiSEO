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

use MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator;
use MediaWiki\Extension\WikiSEO\Generator\GeneratorInterface;
use OutputPage;

/**
 * Citation metatags
 * Mainly taken from https://gist.github.com/hubgit/f3e359ab51da6d15118b
 */
class Citation extends AbstractBaseGenerator implements GeneratorInterface {
	/**
	 * @inheritDoc
	 */
	protected $tags = [
		'description',
		'citation_author',
		'citation_abstract_html_url',
		'citation_conference_title',
		'citation_creation_date',
		'citation_doi',
		'citation_firstpage',
		'citation_headline',
		'citation_isbn',
		'citation_issn',
		'citation_issue',
		'citation_journal_title',
		'citation_keywords',
		'citation_lastpage',
		'citation_license',
		'citation_name',
		'citation_pdf_url',
		'citation_publication_date',
		'citation_publisher',
		'citation_title',
		'citation_type',
		'citation_volume',
	];

	/**
	 * @inheritDoc
	 */
	public function init( array $metadata, OutputPage $out ): void {
		$this->metadata = $metadata;
		$this->outputPage = $out;
	}

	/**
	 * @inheritDoc
	 */
	public function addMetadata(): void {
		$this->addJsonLD();
		$this->addMetaTags();
	}

	/**
	 * @inheritDoc
	 */
	public function getAllowedParameterNames(): array {
		return $this->tags;
	}

	/**
	 * Adds ld+json metadata
	 *
	 * @return void
	 */
	private function addJsonLD(): void {
		$template = '<script type="application/ld+json">%s</script>';

		$jsonLd = [
			'@context' => 'https://schema.org',
			'@graph' => [
				[
					'@type' => $this->metadata['citation_type'] ?? 'ScholarlyArticle',
					'name' => $this->metadata['citation_name'] ?? null,
					'headline' => $this->metadata['citation_headline'] ?? null,
					'datePublished' => $this->metadata['citation_publication_date'] ?? null,
					'dateCreated' => $this->metadata['citation_creation_date'] ?? null,
					'isPartOf' => $this->getIsPartOf(),
					'pageStart' => $this->metadata['citation_firstpage'] ?? null,
					'sameAs' => $this->metadata['citation_doi'] ?? null,
					'description' => $this->metadata['description'] ?? null,
					'copyrightHolder' => $this->metadata['citation_author'] ?? null,
					'license' => $this->metadata['citation_license'] ?? null,

					'publisher' => $this->getPublisher(),
					'keywords' => $this->metadata['citation_keywords'] ?? null,

					'author' => []
				]
			],

		];

		foreach ( explode( ';', $this->metadata['citation_author'] ?? '' ) as $author ) {
			$parts = explode( ',', $author );
			$jsonLd['@graph'][0]['author'][] = [
				'@type' => 'Person',
				'givenName' => $parts[1] ?? null,
				'familyName' => $parts[0] ?? null,
				'name' => $author,
			];
		}

		$this->outputPage->addHeadItem(
			'jsonld-metadata-citation',
			sprintf(
				$template,
				json_encode( $jsonLd )
			)
		);
	}

	/**
	 * Adds <meta> citation data
	 *
	 * @return void
	 */
	private function addMetaTags(): void {
		foreach ( $this->tags as $tag ) {
			if ( !array_key_exists( $tag, $this->metadata ) || $tag === 'description' ) {
				continue;
			}

			if ( $tag === 'citation_author' ) {
				foreach ( explode( ';', $this->metadata[$tag] ) as $part ) {
					$this->outputPage->addMeta( $tag, trim( $part ) );
				}
			} else {
				$this->outputPage->addMeta( $tag, $this->metadata[$tag] );
			}
		}
	}

	/**
	 * Part of JsonLD
	 *
	 * @return array|null
	 */
	private function getIsPartOf(): ?array {
		if ( !isset( $this->metadata['citation_issue'] ) && !isset( $this->metadata['citation_volume'] ) ) {
			return null;
		}

		$partOf = null;
		if ( isset( $this->metadata['citation_volume'] ) ) {
			$journal = null;

			if ( isset( $this->metadata['citation_publisher'] ) ) {
				$journal = [
					'@type' => 'Periodical',
					'name' => $this->metadata['citation_publisher'],
				];
			}

			$partOf = [
				'@type' => 'PublicationVolume',
				'volumeNumber' => $this->metadata['citation_volume'],
				'isPartOf' => $journal,
			];
		}

		return [
			'@type' => 'PublicationIssue',
			'issueNumber' => $this->metadata['citation_issue'] ?? null,
			'isPartOf' => $partOf
		];
	}

	/**
	 * Publisher Json LD
	 *
	 * @return array|null
	 */
	private function getPublisher(): ?array {
		if ( !isset( $this->metadata['citation_publisher'] ) ) {
			return null;
		}

		return [
			'@type' => 'Organization',
			'name' => $this->metadata['citation_publisher'] ?? null,
			'logo' => '',
		];
	}
}
