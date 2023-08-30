<?php

namespace MediaWiki\Extension\WikiSEO\Tests\Hooks;

use LocalFile;
use MediaTransformOutput;
use MediaWiki\Extension\WikiSEO\Hooks\InfoAction;
use MediaWiki\Page\PageProps;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWikiIntegrationTestCase;
use RepoGroup;
use RequestContext;

class InfoActionTest extends MediaWikiIntegrationTestCase {
	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Hooks\InfoAction
	 * @return void
	 * @throws \Exception
	 */
	public function testOnInfoActionNoProps() {
		$propsMock = $this->getMockBuilder( PageProps::class )->disableOriginalConstructor()->getMock();
		$propsMock->expects( $this->once() )->method( 'getProperties' )->willReturn( [] );

		$this->setService( 'PageProps', $propsMock );

		$pageInfo = [];

		$hook = new InfoAction(
			$this->getServiceContainer()->getRepoGroup(),
			$propsMock,
			$this->getServiceContainer()->getTitleFactory()
		);

		$hook->onInfoAction( RequestContext::getMain(), $pageInfo );

		$this->assertCount( 0, $pageInfo );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Hooks\InfoAction
	 * @return void
	 * @throws \Exception
	 */
	public function testOnInfoAction() {
		$titleMock = $this->getMockBuilder( Title::class )->disableOriginalConstructor()->getMock();
		$titleMock->expects( $this->once() )->method( 'exists' )->willReturn( true );
		$titleMock->expects( $this->once() )->method( 'inNamespace' )->willReturn( true );

		$mediaTransformMock = $this->getMockBuilder( MediaTransformOutput::class )
			->disableOriginalConstructor()
			->getMock();
		$mediaTransformMock->expects( $this->once() )->method( 'getUrl' )->willReturn( '' );

		$fileMock = $this->getMockBuilder( LocalFile::class )->disableOriginalConstructor()->getMock();
		$fileMock->expects( $this->once() )
			->method( 'transform' )
			->with( [ 'width' => 200 ] )
			->willReturn( $mediaTransformMock );

		$propsMock = $this->getMockBuilder( PageProps::class )->disableOriginalConstructor()->getMock();
		$propsMock->expects( $this->once() )->method( 'getProperties' )->willReturn( [ [
			'keywords' => 'Foo,Bar,Baz',
			'title' => 'Foo Title',
			'image' => 'Foo.jpg',
			'author' => 'Admin',
		] ] );

		$repoMock = $this->getMockBuilder( RepoGroup::class )->disableOriginalConstructor()->getMock();
		$repoMock->expects( $this->once() )->method( 'findFile' )->willReturn( $fileMock );

		$factoryMock = $this->getMockBuilder( TitleFactory::class )->disableOriginalConstructor()->getMock();
		$factoryMock->method( 'newFromText' )->willReturn( $titleMock );

		$this->setService( 'PageProps', $propsMock );
		$this->setService( 'TitleFactory', $factoryMock );
		$this->setService( 'RepoGroup', $repoMock );

		$pageInfo = [];

		$hook = new InfoAction(
			$this->getServiceContainer()->getRepoGroup(),
			$propsMock,
			$this->getServiceContainer()->getTitleFactory()
		);

		$hook->onInfoAction( RequestContext::getMain(), $pageInfo );

		$this->assertArrayHasKey( 'header-seo', $pageInfo );
		$this->assertCount( 6, $pageInfo['header-seo'] );
	}
}
