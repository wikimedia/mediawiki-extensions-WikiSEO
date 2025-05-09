<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\WikiSEO;

use DeferrableUpdate;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use PageImages\PageImages;

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
	 * @param mixed|string $pageImage
	 */
	public function __construct( Title $title, $pageImage ) {
		$this->title = $title;

		if ( !empty( $pageImage ) && str_contains( $pageImage, ':' ) ) {
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
		if ( empty( $this->pageImage ) ) {
			return;
		}

		$dbl = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$db = $dbl->getConnection( DB_PRIMARY );

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
