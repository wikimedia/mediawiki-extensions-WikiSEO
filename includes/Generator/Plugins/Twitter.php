<?php

namespace Octfx\WikiSEO\Generator\Plugins;

use Html;

/**
 * Twitter metadata generator
 *
 * @package Octfx\WikiSEO\Generator\Plugins
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
		parent::addMetadata();

		$this->outputPage->addHeadItem( 'twitter:card', Html::element( 'meta', [
			self::$htmlElementPropertyKey => 'twitter:card',
			self::$htmlElementContentKey  => 'summary'
		] ) );
	}
}