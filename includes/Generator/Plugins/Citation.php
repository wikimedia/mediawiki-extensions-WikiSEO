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

use MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator;
use MediaWiki\Extension\WikiSEO\Generator\GeneratorInterface;
use OutputPage;

/**
 * Citation metatags
 * Mainly taken from https://gist.github.com/hubgit/f3e359ab51da6d15118b
 */
class Citation extends AbstractBaseGenerator implements GeneratorInterface {
	/**
	 * @inheritdoc
	 */
	protected $tags = [
		'citation_title',
		'citation_date',
		'citation_doi',
		'citation_language',
		'citation_pdf_url',
		'citation_fulltext_html_url',
		'citation_volume',
		'citation_firstpage',
		'citation_keywords',
		'citation_journal_title',
		'citation_publisher',
		'citation_issn',
		'citation_author',
		'citation_author_institution',
		'citation_author_institution',
		'citation_author_email',
		'citation_author',
		'citation_author_institution',
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
		foreach ( $this->tags as $tag ) {
			if ( array_key_exists( $tag, $this->metadata ) ) {
				$this->outputPage->addMeta( $tag, $this->metadata[$tag] );
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getAllowedTagNames(): array {
		return $this->tags;
	}
}
