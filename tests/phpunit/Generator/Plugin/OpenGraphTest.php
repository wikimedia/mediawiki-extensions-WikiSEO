<?php

namespace MediaWiki\Extension\WikiSEO\Tests\Generator\Plugin;

use MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph;
use MediaWiki\Extension\WikiSEO\Tests\Generator\GeneratorTest;

class OpenGraphTest extends GeneratorTest {
	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::init
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\OpenGraph::addMetadata
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

		$this->assertArrayHasKey( 'og:title', $out->getHeadItemsArray() );
		$this->assertArrayHasKey( 'og:description', $out->getHeadItemsArray() );
		$this->assertArrayHasKey( 'article:tag', $out->getHeadItemsArray() );

		$this->assertContains( $out->getTitle()->getFullURL(), $out->getHeadItemsArray()['og:url'] );
	}
}
