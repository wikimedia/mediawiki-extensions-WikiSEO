<?php

namespace MediaWiki\Extension\WikiSEO\Tests\Generator\Plugin;

use MediaWiki\Extension\WikiSEO\Generator\Plugins\DublinCore;
use MediaWiki\Extension\WikiSEO\Tests\Generator\GeneratorTest;

/**
 * @group Database
 */
class DublinCoreTest extends GeneratorTest {
	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\DublinCore::init
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\DublinCore::addMetadata
	 */
	public function testAddIdentifierWikiData() {
		$metadata = [
			'dc.identifier.wikidata'  => 'Q105955627',
		];

		$out = $this->newInstance();

		$generator = new DublinCore();
		$generator->init( $metadata, $out );
		$generator->addMetadata();

		$this->assertArraySubmapSame( [ [
			'dc.identifier.wikidata', 'Q105955627'
		] ], $out->getMetaTags() );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\DublinCore::init
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\DublinCore::addMetadata
	 */
	public function testAddMetadata() {
		$metadata = [
			'description' => 'Example Description',
			'author'      => 'Foo Bar',
			'title'       => 'Example Title',
			'site_name'   => 'Seo Inc.',
		];

		$out = $this->newInstance();

		$generator = new DublinCore();
		$generator->init( $metadata, $out );
		$generator->addMetadata();

		$this->assertArraySubmapSame( [
			[ 'DC.creator', 'Foo Bar' ],
			[ 'DC.description', 'Example Description' ],
			[ 'DC.publisher', 'Seo Inc.' ],
			[ 'DC.title', 'Example Title' ],
		], $out->getMetaTags() );
	}
}
