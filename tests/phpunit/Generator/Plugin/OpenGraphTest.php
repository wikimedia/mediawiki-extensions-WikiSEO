<?php

namespace MediaWiki\Extension\WikiSEO\Tests\Generator\Plugin;

use MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph;
use MediaWiki\Extension\WikiSEO\Tests\Generator\GeneratorTest;

class OpenGraphTest extends GeneratorTest {
	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::init
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::addMetadata
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::addTitleMeta
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::addSiteName
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::getConfigValue
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::getRevisionTimestamp
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::setFallbackImageIfEnabled
	 * @covers \MediaWiki\Extension\WikiSEO\WikiSEO::protocolizeUrl
	 */
	public function testAddMetadata() {
		$metadata = [
			'description' => 'Example Description',
			'keywords'    => 'Keyword 1, Keyword 2',
		];

		$out = $this->newInstance();

		$generator = new OpenGraph();
		$generator->init( $metadata, $out );
		$generator->addMetadata();

		self::assertArrayHasKey( 'og:title', $out->getHeadItemsArray() );
		self::assertArrayHasKey( 'og:description', $out->getHeadItemsArray() );
		self::assertArrayHasKey( 'article:tag', $out->getHeadItemsArray() );

		self::assertStringContainsString( $out->getTitle()->getFullURL(), $out->getHeadItemsArray()['og:url'] );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::init
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::getRevisionTimestamp
	 */
	public function testRevisionTimestamp() {
		$out = $this->newInstance();

		$generator = new OpenGraph();
		$generator->init( [], $out );
		$generator->addMetadata();

		self::assertArrayHasKey( 'article:published_time', $out->getHeadItemsArray() );
		self::assertArrayHasKey( 'article:modified_time', $out->getHeadItemsArray() );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::init
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::getRevisionTimestamp
	 */
	public function testPublishedTimestampManual() {
		$out = $this->newInstance();

		$generator = new OpenGraph();
		$generator->init(
			[
				'published_time' => '2012-01-01',
			], $out
		);
		$generator->addMetadata();

		self::assertArrayHasKey( 'article:published_time', $out->getHeadItemsArray() );
		self::assertStringContainsString(
			'2012-01-01',
			$out->getHeadItemsArray()['article:published_time']
		);
		self::assertArrayHasKey( 'article:modified_time', $out->getHeadItemsArray() );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::init
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::preprocessFileMetadata
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::getFileInfo
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::getFileObject
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::getRevisionTimestamp
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::setFallbackImageIfEnabled
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::addMetadata
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::addTitleMeta
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::addSiteName
	 */
	public function testContainsImage() {
		// Unset default image if set
		$this->setMwGlobals( 'wgWikiSeoDefaultImage', null );
		$this->setMwGlobals( 'wgWikiSeoDisableLogoFallbackImage', false );
		$this->setMwGlobals( 'wgLogos', false );
		$this->setMwGlobals( 'wgLogo', '/resources/assets/wiki.png' );

		$out = $this->newInstance();

		$generator = new OpenGraph();
		$generator->init( [], $out );
		$generator->addMetadata();

		self::assertArrayHasKey( 'og:image', $out->getHeadItemsArray() );
		self::assertStringContainsString( 'wiki.png', $out->getHeadItemsArray()['og:image'] );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::init
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::preprocessFileMetadata
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::setFallbackImageIfEnabled
	 */
	public function testDefaultImage() {
		$this->setMwGlobals( 'wgWikiSeoDefaultImage', 'wiki.png' );
		$this->setMwGlobals( 'wgWikiSeoDisableLogoFallbackImage', true );

		$out = $this->newInstance();

		$generator = new OpenGraph();
		$generator->init( [], $out );
		$generator->addMetadata();

		self::assertArrayHasKey( 'og:image', $out->getHeadItemsArray() );
		self::assertStringContainsString( 'wiki.png', $out->getHeadItemsArray()['og:image'] );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::init
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::preprocessFileMetadata
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::getFileInfo
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::getFileObject
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::getRevisionTimestamp
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::setFallbackImageIfEnabled
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::getWikiLogo
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::addMetadata
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::addTitleMeta
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::addSiteName
	 */
	public function testWgLogosImage() {
		$this->setMwGlobals( 'wgLogos', [
			'1x' => "path/to/1x_version.png",
			'1.5x' => "path/to/1.5x_version.png",
			'2x' => "path/to/2x_version.png",
			'svg' => "path/to/svg_version.svg",
			'icon' => "path/to/icon.png",
			'wordmark' => [
				'src' => "path/to/wordmark_version.png",
				'1x' => "path/to/wordmark_version.svg",
				'width' => 135,
				'height' => 20,
			],
		] );

		$out = $this->newInstance();

		$generator = new OpenGraph();
		$generator->init( [], $out );
		$generator->addMetadata();

		self::assertArrayHasKey( 'og:image', $out->getHeadItemsArray() );
		self::assertStringContainsString( '1x_version.png', $out->getHeadItemsArray()['og:image'] );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::init
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::preprocessFileMetadata
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::getFileInfo
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::getFileObject
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::getRevisionTimestamp
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::setFallbackImageIfEnabled
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::getWikiLogo
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::addMetadata
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::addTitleMeta
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::addSiteName
	 */
	public function testWgLogosImageAllSvg() {
		$this->setMwGlobals( 'wgLogos', [
			'1x' => "path/to/1x_version.svg",
			'1.5x' => "path/to/1.5x_version.svg",
			'2x' => "path/to/2x_version.svg",
			'svg' => "path/to/svg_version.svg",
			'icon' => "path/to/icon.svg",
		] );

		$out = $this->newInstance();

		$generator = new OpenGraph();
		$generator->init( [], $out );
		$generator->addMetadata();

		self::assertArrayNotHasKey( 'og:image', $out->getHeadItemsArray() );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::init
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::preprocessFileMetadata
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::getFileInfo
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::getFileObject
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::getRevisionTimestamp
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::setFallbackImageIfEnabled
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::getWikiLogo
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::addMetadata
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::addTitleMeta
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::addSiteName
	 */
	public function testWgLogosImageOneUsable() {
		$this->setMwGlobals( 'wgLogos', [
			'1x' => "path/to/1x_version.svg",
			'1.5x' => "path/to/1.5x_version.svg",
			'2x' => "path/to/2x_version.webp",
			'svg' => "path/to/svg_version.svg",
			'icon' => "path/to/icon.svg",
		] );

		$out = $this->newInstance();

		$generator = new OpenGraph();
		$generator->init( [], $out );
		$generator->addMetadata();

		self::assertArrayHasKey( 'og:image', $out->getHeadItemsArray() );
		self::assertStringContainsString( '2x_version.webp', $out->getHeadItemsArray()['og:image'] );
	}
}
