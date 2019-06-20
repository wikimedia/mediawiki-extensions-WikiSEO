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

use Html;

/**
 * Twitter metadata generator
 *
 * @package MediaWiki\Extension\WikiSEO\Generator\Plugins
 */
class Twitter extends OpenGraph {
	protected static $tags = [
		'type',
		'image',
		'image_width',
		'image_height',
		'description',
		'keywords',
		'locale',
		'site_name',
		'published_time',
		'modified_time',
		'twitter_site',
	];

	/**
	 * Page title property name
	 *
	 * @var string
	 */
	protected $titlePropertyName = 'twitter:title';

	/**
	 * Twitter constructor.
	 * Updates some tag name conversions
	 */
	public function __construct() {
		self::$conversions['twitter_site'] = 'twitter:site';

		self::$conversions['description'] = 'twitter:description';
		self::$conversions['image'] = 'twitter:image';
	}

	/**
	 * Add the metadata to the OutputPage
	 *
	 * @return void
	 */
	public function addMetadata() {
		$this->addTwitterSiteHandleTag();

		parent::addMetadata();

		$this->outputPage->addHeadItem( 'twitter:card', Html::element( 'meta', [
			self::$htmlElementPropertyKey => 'twitter:card',
			self::$htmlElementContentKey => 'summary',
		] ) );
	}

	/**
	 * Add the global twitter site handle from $wgTwitterSiteHandle to the meta tags
	 * If $wgTwitterSiteHandle is not null setting the handle via tag or hook is ignored
	 */
	private function addTwitterSiteHandleTag() {
		global $wgTwitterSiteHandle;

		if ( $wgTwitterSiteHandle !== null ) {
			unset( $this->metadata['twitter_site'] );
			unset( self::$tags['twitter_site'], self::$conversions['twitter_site'] );

			$this->outputPage->addHeadItem( 'twitter:site', Html::element( 'meta', [
				self::$htmlElementPropertyKey => 'twitter:site',
				self::$htmlElementContentKey => $wgTwitterSiteHandle,
			] ) );
		}
	}
}
