<?php

namespace Octfx\WikiSEO\Tests\Generator\Plugin;

use Octfx\WikiSEO\Generator\Plugins\JsonLD;
use Octfx\WikiSEO\Tests\Generator\GeneratorTest;

class JsonLDTest extends GeneratorTest {
	/**
	 * @covers \Octfx\WikiSEO\Generator\Plugins\JsonLD::init
	 * @covers \Octfx\WikiSEO\Generator\Plugins\JsonLD::addMetadata
	 */
	public function testAddMetadata() {
		$metadata = [
			'description' => 'Example Description',
			'type'        => 'website',
		];

		$out = $this->newInstance();

		$generator = new JsonLD();
		$generator->init( $metadata, $out );
		$generator->addMetadata();

		$this->assertArrayHasKey( 'jsonld-metadata', $out->getHeadItemsArray() );

		$this->assertContains( '@type', $out->getHeadItemsArray()['jsonld-metadata'] );
	}
}