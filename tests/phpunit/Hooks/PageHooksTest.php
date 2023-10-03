<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\WikiSEO\Tests\Hooks;

use MediaWiki\Extension\WikiSEO\Hooks\PageHooks;
use MediaWiki\Html\Html;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;
use MWException;
use OutputPage;
use RequestContext;

/**
 * @group Database
 */
class PageHooksTest extends MediaWikiIntegrationTestCase {
	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Hooks\PageHooks::onOutputPageAfterGetHeadLinksArray
	 * @return void
	 * @throws MWException
	 */
	public function testOnOutputPageAfterGetHeadLinksArrayCanonicalNotSet() {
		$tags = [];

		RequestContext::resetMain();
		RequestContext::getMain()->setTitle( Title::makeTitle( NS_MAIN, 'Foo' ) );

		$out = new OutputPage( RequestContext::getMain() );

		$hooks = new PageHooks( $this->getServiceContainer()->getMainConfig() );
		$hooks->onOutputPageAfterGetHeadLinksArray( $tags, $out );

		$this->assertCount( 0, $tags );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Hooks\PageHooks::onOutputPageAfterGetHeadLinksArray
	 * @return void
	 * @throws MWException
	 */
	public function testOnOutputPageAfterGetHeadLinksArrayCanonicalSet() {
		$tags = [];

		RequestContext::resetMain();
		RequestContext::getMain()->setTitle( Title::makeTitle( NS_MAIN, 'Foo' ) );

		$out = new OutputPage( RequestContext::getMain() );
		$out->setProperty( 'canonical', 'http://foo' );

		$hooks = new PageHooks( $this->getServiceContainer()->getMainConfig() );
		$hooks->onOutputPageAfterGetHeadLinksArray( $tags, $out );

		$this->assertArrayHasKey( 'canonical', $tags );
		$this->assertStringContainsString( 'http://foo', $tags['canonical'] );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Hooks\PageHooks::onOutputPageAfterGetHeadLinksArray
	 * @return void
	 * @throws MWException
	 */
	public function testOnOutputPageAfterGetHeadLinksArrayOverwriteCanonical() {
		$tags = [
			'something' => 'content',
			Html::element( 'link', [ 'rel' => 'canonical', 'href' => 'http://bar' ] ),
		];

		RequestContext::resetMain();
		RequestContext::getMain()->setTitle( Title::makeTitle( NS_MAIN, 'Foo' ) );

		$out = new OutputPage( RequestContext::getMain() );
		$out->setProperty( 'canonical', 'http://foo' );

		$hooks = new PageHooks( $this->getServiceContainer()->getMainConfig() );
		$hooks->onOutputPageAfterGetHeadLinksArray( $tags, $out );

		$this->assertArrayHasKey( 0, $tags );
		$this->assertStringContainsString( 'http://foo', $tags[0] );
		$this->assertStringNotContainsString( 'http://bar', $tags[0] );
	}
}
