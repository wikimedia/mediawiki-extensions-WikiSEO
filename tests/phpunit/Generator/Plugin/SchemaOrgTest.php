<?php

namespace MediaWiki\Extension\WikiSEO\Tests\Generator\Plugin;

use MediaWiki\Extension\WikiSEO\Generator\Plugins\SchemaOrg;
use MediaWiki\Extension\WikiSEO\Tests\Generator\GeneratorTest;

class SchemaOrgTest extends GeneratorTest {
	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\SchemaOrg::init
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\SchemaOrg::addMetadata
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\SchemaOrg::getRevisionTimestamp
	 */
	public function testAddMetadata() {
		$metadata = [
			'description' => 'Example Description',
			'type'        => 'website',
		];

		$out = $this->newInstance();

		$generator = new SchemaOrg();
		$generator->init( $metadata, $out );
		$generator->addMetadata();

		self::assertArrayHasKey( 'jsonld-metadata', $out->getHeadItemsArray() );

		self::assertStringContainsString( '@type', $out->getHeadItemsArray()['jsonld-metadata'] );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\SchemaOrg::getAuthorMetadata
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\SchemaOrg::getConfigValue
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\SchemaOrg::getLogoMetadata
	 */
	public function testContainsOrganization() {
		$out = $this->newInstance();

		$generator = new SchemaOrg();
		$generator->init( [], $out );
		$generator->addMetadata();

		self::assertStringContainsString( 'Organization', $out->getHeadItemsArray()['jsonld-metadata'] );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\SchemaOrg::getSearchActionMetadata
	 */
	public function testContainsSearchAction() {
		$out = $this->newInstance();

		$generator = new SchemaOrg();
		$generator->init( [], $out );
		$generator->addMetadata();

		self::assertStringContainsString( 'SearchAction', $out->getHeadItemsArray()['jsonld-metadata'] );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\SchemaOrg::getAuthorMetadata
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\SchemaOrg::getConfigValue
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\SchemaOrg::getLogoMetadata
	 */
	public function testContainsAuthorAndPublisher() {
		$out = $this->newInstance();

		$generator = new SchemaOrg();
		$generator->init( [], $out );
		$generator->addMetadata();

		self::assertStringContainsString( 'author', $out->getHeadItemsArray()['jsonld-metadata'] );
		self::assertStringContainsString( 'publisher', $out->getHeadItemsArray()['jsonld-metadata'] );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\SchemaOrg::init
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\SchemaOrg::getRevisionTimestamp
	 */
	public function testContainsRevisionTimestamp() {
		$out = $this->newInstance();

		$generator = new SchemaOrg();
		$generator->init( [], $out );
		$generator->addMetadata();

		self::assertStringContainsString( 'datePublished', $out->getHeadItemsArray()['jsonld-metadata'] );
		self::assertStringContainsString( 'dateModified', $out->getHeadItemsArray()['jsonld-metadata'] );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\SchemaOrg::init
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\SchemaOrg::getRevisionTimestamp
	 */
	public function testContainsPublishedTimestampManual() {
		$out = $this->newInstance();

		$generator = new SchemaOrg();
		$generator->init(
			[
				'published_time' => '2012-01-01',
			], $out
		);
		$generator->addMetadata();

		self::assertStringContainsString( '2012-01-01', $out->getHeadItemsArray()['jsonld-metadata'] );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\SchemaOrg::getImageMetadata
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::preprocessFileMetadata
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::getFileInfo
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::getFileObject
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\AbstractBaseGenerator::getRevisionTimestamp
	 */
	public function testContainsImageObject() {
		$this->setMwGlobals( 'wgWikiSeoDisableLogoFallbackImage', false );

		$out = $this->newInstance();

		$generator = new SchemaOrg();
		$generator->init( [], $out );
		$generator->addMetadata();

		self::assertStringContainsString( 'change-your-logo.svg', $out->getHeadItemsArray()['jsonld-metadata'] );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\SchemaOrg::init
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\SchemaOrg::getTypeMetadata
	 */
	public function testTypeMetadata() {
		$out = $this->newInstance();

		$generator = new SchemaOrg();
		$generator->init(
			[
				'type' => 'test-type',
			], $out
		);
		$generator->addMetadata();

		self::assertStringContainsString( 'test-type', $out->getHeadItemsArray()['jsonld-metadata'] );
	}
}
