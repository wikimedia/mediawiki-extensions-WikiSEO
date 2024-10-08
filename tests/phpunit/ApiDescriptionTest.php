<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\WikiSEO\Tests;

use ExtensionDependencyError;
use InvalidArgumentException;
use MediaWiki\Extension\WikiSEO\ApiDescription;
use MediaWiki\Title\Title;

/**
 * @group Database
 */
class ApiDescriptionTest extends \MediaWikiIntegrationTestCase {

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\ApiDescription
	 * @return void
	 */
	public function testGetDescriptionInvalidSource() {
		$this->expectException( InvalidArgumentException::class );

		new ApiDescription(
			Title::makeTitle( NS_MAIN, 'Foo' ),
			false,
			'<invalid>'
		);
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\ApiDescription
	 * @return void
	 */
	public function testGetDescriptionTENotLoaded() {
		$this->markTestSkipped();
		$this->expectException( ExtensionDependencyError::class );

		$desc = new ApiDescription( Title::makeTitle( NS_MAIN, 'Foo' ) );
		$desc->getDescription();
	}
}
