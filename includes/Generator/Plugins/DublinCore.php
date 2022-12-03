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

class DublinCore extends AbstractBaseGenerator implements GeneratorInterface {

	/**
	 * Valid Tags for this generator
	 *
	 * @var array
	 */
	protected $tags = [
		'dc.identifier.wikidata',
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
	public function getAllowedParameterNames(): array {
		return $this->tags;
	}
}
