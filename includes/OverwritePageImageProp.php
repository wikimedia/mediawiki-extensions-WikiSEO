<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\WikiSEO;

use DeferrableUpdate;
use MediaWiki\MediaWikiServices;
use PageImages\PageImages;
use Title;

class OverwritePageImageProp implements DeferrableUpdate {

	/**
	 * @var Title The title to work on
	 */
	private $title;

	/**
	 * @var mixed|string
	 */
	private $pageImage;

	/**
	 * @param Title $title
	 * @param string $pageImage
	 */
	public function __construct( Title $title, string $pageImage ) {
		$this->title = $title;

		if ( strpos( $pageImage, ':' ) !== -1 ) {
			$pageImage = explode( ':', $pageImage )[1];
		}

		$this->pageImage = $pageImage;
	}

	/**
	 * Overwrite 'page_image_free' page prop
	 *
	 * @return void
	 */
	public function doUpdate() {
		$dbl = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$db = $dbl->getConnection( $dbl->getWriterIndex() );

		$db->update(
			'page_props',
			[
				'pp_value' => $this->pageImage,
			],
			[
				'pp_page' => $this->title->getArticleID(),
				'pp_propname' => PageImages::PROP_NAME_FREE,
			],
			__METHOD__
		);
	}
}
