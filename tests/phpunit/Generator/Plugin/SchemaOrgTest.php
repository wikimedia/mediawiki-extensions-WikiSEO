<?php

namespace MediaWiki\Extension\WikiSEO\Tests\Generator\Plugin;

use MediaWiki\Extension\WikiSEO\Generator\Plugins\SchemaOrg;
use MediaWiki\Extension\WikiSEO\Tests\Generator\GeneratorTest;

class SchemaOrgTest extends GeneratorTest {
	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\SchemaOrg::init
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\SchemaOrg::addMetadata
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

		$this->assertArrayHasKey( 'jsonld-metadata', $out->getHeadItemsArray() );

		$this->assertContains( '@type', $out->getHeadItemsArray()['jsonld-metadata'] );
	}
}