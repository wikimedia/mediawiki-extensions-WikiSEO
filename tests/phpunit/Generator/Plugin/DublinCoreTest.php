<?php

namespace MediaWiki\Extension\WikiSEO\Tests\Generator\Plugin;

use MediaWiki\Extension\WikiSEO\Generator\Plugins\DublinCore;
use MediaWiki\Extension\WikiSEO\Tests\Generator\GeneratorTest;

class DublinCoreTest extends GeneratorTest {
	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\DublinCore::init
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\DublinCore::addMetadata
	 */
	public function testAddMetadata() {
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
}
