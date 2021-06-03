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

/**
 * Twitter metadata generator
 *
 * @package MediaWiki\Extension\WikiSEO\Generator\Plugins
 */
class Twitter extends OpenGraph {
	/**
	 * Twitter constructor.
	 * Updates some tag name conversions
	 */
	public function __construct() {
		$this->tags[] = 'twitter_site';

		$this->conversions = array_merge(
			$this->conversions, [
				'twitter_site' => 'twitter:site'
			]
		);
	}

	/**
	 * Add the metadata to the OutputPage
	 *
	 * @return void
	 */
	public function addMetadata(): void {
		$this->addTwitterSiteHandleTag();

		parent::addMetadata();

		$twitterCardType = $this->getConfigValue( 'TwitterCardType' ) ?? 'summary_large_image';

		$this->outputPage->addMeta( 'twitter:card', $twitterCardType );
	}

	/**
	 * Add the global twitter site handle from $wgTwitterSiteHandle to the meta tags
	 * If $wgTwitterSiteHandle is not null setting the handle via tag or hook is ignored
	 */
	private function addTwitterSiteHandleTag(): void {
		$twitterSiteHandle = $this->getConfigValue( 'TwitterSiteHandle' ) ??
			$this->metadata['twitter_site'] ??
			null;

		if ( $twitterSiteHandle === null ) {
			return;
		}

		unset(
			$this->metadata['twitter_site'],
			$this->tags['twitter_site'],
			$this->conversions['twitter_site']
		);

		$this->outputPage->addMeta( 'twitter:site', $twitterSiteHandle );
	}
}
