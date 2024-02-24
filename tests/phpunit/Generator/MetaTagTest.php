<?php

namespace MediaWiki\Extension\WikiSEO\Tests\Generator;

use MediaWiki\Extension\WikiSEO\Generator\MetaTag;
use MediaWiki\Html\Html;

/**
 * @group Database
 */
class MetaTagTest extends GeneratorTest {
	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\MetaTag::init
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\MetaTag::addMetadata
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::getConfigValue
	 */
	public function testAddMetadata() {
		$metadata = [
			'description' => 'Example Description',
			'keywords'    => 'Keyword 1, Keyword 2',
		];

		$out = $this->newInstance();

		$generator = new MetaTag();
		$generator->init( $metadata, $out );
		$generator->addMetadata();

		self::assertContains( [ 'description', 'Example Description' ], $out->getMetaTags() );
		self::assertContains( [ 'keywords', 'Keyword 1, Keyword 2' ], $out->getMetaTags() );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\MetaTag::init
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\MetaTag::addGoogleSiteVerification
	 */
	public function testAddGoogleSiteKey() {
		$this->setMwGlobals( 'wgGoogleSiteVerificationKey', 'google-key' );

		$out = $this->newInstance();

		$generator = new MetaTag();
		$generator->init( [], $out );
		$generator->addMetadata();

		self::assertContains( [ 'google-site-verification', 'google-key' ], $out->getMetaTags() );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\MetaTag::addNortonSiteVerification
	 */
	public function testAddNortonSiteVerification() {
		$this->setMwGlobals( 'wgNortonSiteVerificationKey', 'norton-key' );

		$out = $this->newInstance();

		$generator = new MetaTag();
		$generator->init( [], $out );
		$generator->addMetadata();

		self::assertContains(
			[
				'norton-safeweb-site-verification',
				'norton-key',
			], $out->getMetaTags()
		);
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\MetaTag::addNaverSiteVerification
	 */
	public function testAddNaverSiteVerification() {
		$this->setMwGlobals( 'wgNaverSiteVerificationKey', 'naver-key' );

		$out = $this->newInstance();

		$generator = new MetaTag();
		$generator->init( [], $out );
		$generator->addMetadata();

		self::assertContains(
			[
				'naver-site-verification',
				'naver-key',
			], $out->getMetaTags()
		);
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\MetaTag::addPinterestSiteVerification
	 */
	public function testAddPinterestSiteVerification() {
		$this->setMwGlobals( 'wgPinterestSiteVerificationKey', 'pinterest-key' );

		$out = $this->newInstance();

		$generator = new MetaTag();
		$generator->init( [], $out );
		$generator->addMetadata();

		self::assertContains( [ 'p:domain_verify', 'pinterest-key' ], $out->getMetaTags() );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\MetaTag::addYandexSiteVerification
	 */
	public function testAddYandexSiteVerification() {
		$this->setMwGlobals( 'wgYandexSiteVerificationKey', 'yandex-key' );

		$out = $this->newInstance();

		$generator = new MetaTag();
		$generator->init( [], $out );
		$generator->addMetadata();

		self::assertContains( [ 'yandex-verification', 'yandex-key' ], $out->getMetaTags() );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\MetaTag::addBingSiteVerification
	 */
	public function testAddBingSiteVerification() {
		$this->setMwGlobals( 'wgBingSiteVerificationKey', 'bing-key' );

		$out = $this->newInstance();

		$generator = new MetaTag();
		$generator->init( [], $out );
		$generator->addMetadata();

		self::assertContains( [ 'msvalidate.01', 'bing-key' ], $out->getMetaTags() );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\MetaTag::init
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\MetaTag::addFacebookAppId
	 */
	public function testAddFacebookAppId() {
		$this->setMwGlobals( 'wgFacebookAppId', '0011223344' );

		$out = $this->newInstance();

		$generator = new MetaTag();
		$generator->init( [], $out );
		$generator->addMetadata();

		$appId = Html::element( 'meta',
			[
				'property' => 'fb:app_id',
				'content' => '0011223344',
			]
		);

		self::assertArrayHasKey( 'fb:app_id', $out->getHeadItemsArray() );
		self::assertEquals(
			$appId,
			$out->getHeadItemsArray()['fb:app_id']
		);
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\MetaTag::init
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\MetaTag::addFacebookAdmins
	 */
	public function testAddFacebookAdmins() {
		$this->setMwGlobals( 'wgFacebookAdmins', '0011223344' );

		$out = $this->newInstance();

		$generator = new MetaTag();
		$generator->init( [], $out );
		$generator->addMetadata();

		$admins = Html::element( 'meta',
			[
				'property' => 'fb:admins',
				'content' => '0011223344',
			]
		);

		self::assertArrayHasKey( 'fb:admins', $out->getHeadItemsArray() );
		self::assertEquals(
			$admins,
			$out->getHeadItemsArray()['fb:admins']
		);
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\MetaTag::addHrefLangs
	 */
	public function testAddDefaultLanguageLink() {
		$this->setMwGlobals( 'wgWikiSeoDefaultLanguage', 'de-de' );

		$out = $this->newInstance();

		$generator = new MetaTag();
		$generator->init( [], $out );
		$generator->addMetadata();

		self::assertArrayHasKey( 'de-de', $out->getHeadItemsArray() );
		self::assertStringContainsString( 'hreflang="de-de"', $out->getHeadItemsArray()['de-de'] );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\MetaTag::addHrefLangs
	 */
	public function testAddLanguageLinks() {
		$this->setMwGlobals( 'wgWikiSeoDefaultLanguage', 'de-de' );

		$out = $this->newInstance();

		$generator = new MetaTag();
		$generator->init(
			[
				'hreflang_de-de' => 'https://example.de',
				'hreflang_nl-nl' => 'https://example.nl',
				'hreflang_en-us' => 'https://example.com',
			], $out
		);
		$generator->addMetadata();

		self::assertArrayHasKey( 'hreflang_de-de', $out->getHeadItemsArray() );
		self::assertArrayHasKey( 'hreflang_nl-nl', $out->getHeadItemsArray() );
		self::assertArrayHasKey( 'hreflang_en-us', $out->getHeadItemsArray() );

		self::assertStringContainsString(
			'https://example.de"',
			$out->getHeadItemsArray()['hreflang_de-de']
		);
		self::assertStringContainsString(
			'https://example.nl"',
			$out->getHeadItemsArray()['hreflang_nl-nl']
		);
		self::assertStringContainsString(
			'https://example.com"', $out->getHeadItemsArray()['hreflang_en-us']
		);
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\MetaTag::addHrefLangs
	 */
	public function testAddLanguageLinksWrongFormatted() {
		$out = $this->newInstance();

		$generator = new MetaTag();
		$generator->init(
			[
				'hrefWRONGlang_de-de' => 'https://example.de',
			], $out
		);
		$generator->addMetadata();

		self::assertArrayNotHasKey( 'hrefWRONGlang_de-de', $out->getHeadItemsArray() );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\MetaTag::addNoIndex
	 */
	public function testAddNoIndex() {
		$this->setMwGlobals( 'wgWikiSeoNoindexPageTitles', [
			'CustomTitle',
		] );

		$out = $this->newInstance( [], null, [], 'CustomTitle' );

		$generator = new MetaTag();
		$generator->init( [], $out );
		$generator->addMetadata();

		self::assertStringContainsStringIgnoringCase( 'noindex', $out->getRobotPolicy() );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\MetaTag::addNoIndex
	 */
	public function testAddNoIndexThroughParser() {
		$out = $this->newInstance();

		$generator = new MetaTag();
		$generator->init( [ 'robots' => 'noindex' ], $out );
		$generator->addMetadata();

		self::assertEquals( 'noindex', $out->getIndexPolicy() );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\MetaTag::addNoIndex
	 */
	public function testAddNoIndexNoFollowThroughParser() {
		$out = $this->newInstance();

		$generator = new MetaTag();
		$generator->init( [ 'robots' => 'noindex,nofollow' ], $out );
		$generator->addMetadata();

		self::assertEquals( 'noindex', $out->getIndexPolicy() );
		self::assertEquals( 'nofollow', $out->getFollowPolicy() );
	}
}
