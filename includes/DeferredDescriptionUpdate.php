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

namespace MediaWiki\Extension\WikiSEO;

use DeferrableUpdate;
use Exception;
use ExtensionDependencyError;
use MediaWiki\MediaWikiServices;
use MWException;
use PageProps;
use Title;

/**
 * This runs through the onRevisionDataUpdates hook but only if $wgWikiSeoEnableAutoDescription is enabled
 * and no manual description was set
 *
 * The goal of this class is to automatically set a description for each page after if has been edited.
 * Currently, only TextExtracts is available
 */
class DeferredDescriptionUpdate implements DeferrableUpdate {

	/**
	 * @var Title The title to work on
	 */
	private $title;

	/**
	 * @var bool Whether to cut of dangling sentences
	 */
	private $clean;

	/**
	 * Current description property from ParserOutput
	 *
	 * @var string
	 */
	private $currentDescription;

	/**
	 * Do a deferred update to the specified title.
	 * Usually runs when RevisionDataUpdates occurs
	 *
	 * @param Title $title
	 * @param string|null $currentDescription
	 * @param bool $cleanDescription
	 */
	public function __construct( Title $title, ?string $currentDescription, bool $cleanDescription = false ) {
		$this->title = $title;
		$this->currentDescription = $currentDescription ?? '';
		$this->clean = $cleanDescription;
	}

	/**
	 * We do have to manually set the page properties, as we have no way of getting the parser or outputpage
	 * in a deferred update
	 */
	public function doUpdate(): void {
		try {
			$apiDescription = $this->loadDescriptionFromApi();
		} catch ( Exception $e ) {
			return;
		}

		$apiDescription = trim( $apiDescription ?? '' );
		$emptyLikeDescriptions = [ '', '…', '\u2026' ];

		// If API response is empty like, or current description is equal to api description, exit early
		if ( in_array( $apiDescription, $emptyLikeDescriptions, true ) ||
			strcmp( $this->currentDescription, $apiDescription ) === 0 ) {
			return;
		}

		if ( method_exists( MediaWikiServices::class, 'getPageProps' ) ) {
			// MW 1.36+
			$propertyDescriptions = MediaWikiServices::getInstance()->getPageProps()
				->getProperties( $this->title, 'description' );
		} else {
			$propertyDescriptions = PageProps::getInstance()->getProperties( $this->title, 'description' );
		}

		$dbl = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$db = $dbl->getConnection( $dbl->getWriterIndex() );

		// Flag indicating if an insert or update should happen
		$shouldInsert = false;
		switch ( true ) {
			case count( $propertyDescriptions ) > 1:
				// There are multiple page props with the name 'description' present
				// This shouldn't happen, but we'll try to clean it here
				$db->delete(
					'page_props',
					[
						'pp_page' => $this->title->getArticleID(),
						'pp_propname' => 'description',
					]
				);
			// Intentional fall-through, as deleting all 'description' props requires inserting a new row
			case empty( $propertyDescriptions ):
				$shouldInsert = true;
				break;

			default:
				break;
		}

		if ( count( $propertyDescriptions ) === 1 ) {
			// Sanity check
			$descriptionEqual = strcmp( $propertyDescriptions[0], $apiDescription ) === 0;
			if ( $descriptionEqual ) {
				return;
			}
		}

		if ( $shouldInsert ) {
			$db->insert(
				'page_props',
				[
					'pp_page' => $this->title->getArticleID(),
					'pp_propname' => 'description',
					'pp_value' => $apiDescription,
					'pp_sortkey' => null,
				],
				__METHOD__
			);
		} else {
			$db->update(
				'page_props',
				[
					'pp_value' => $apiDescription,
				],
				[
					'pp_page' => $this->title->getArticleID(),
					'pp_propname' => 'description',
				],
				__METHOD__
			);
		}
	}

	/**
	 * @return string|null
	 * @throws ExtensionDependencyError
	 * @throws MWException
	 */
	private function loadDescriptionFromApi(): ?string {
		$descriptor = new ApiDescription(
			$this->title,
			$this->clean
		);

		return $descriptor->getDescription();
	}
}
