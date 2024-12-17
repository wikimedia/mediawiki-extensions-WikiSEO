<?php

namespace MediaWiki\Extension\WikiSEO\Tests;

use MediaWiki\Extension\WikiSEO\TagParser;
use MediaWikiIntegrationTestCase;
use Parser;
use ParserOptions;

class TagParserTest extends MediaWikiIntegrationTestCase {
	/**
	 * @var TagParser
	 */
	private $tagParser;

	/**
	 * @var Parser
	 */
	private $parser;

	protected function setUp(): void {
		parent::setUp();
		$this->tagParser = new TagParser();

		$factory = $this->getServiceContainer()->getParserFactory();
		$parser = $factory->create();
		$parser->setOptions( ParserOptions::newFromAnon() );

		$this->parser = $parser;
	}

	protected function tearDown(): void {
		unset( $this->tagParser );
		parent::tearDown();
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\TagParser::parseArgs
	 */
	public function testParseArgs() {
		$this->markTestSkipped( 'TODO: Fix  Call to a member function merge() on null' );

		$args = [
			'title=Test Title',
			'=',
			'keywords=a,b ,  c , d',
			'=emptyKey',
			'emptyContent='
		];

		$parsedArgs = $this->tagParser->parseArgs(
			$args,
			$this->parser,
			false
		);

		self::assertCount( 2, $parsedArgs );
		self::assertArrayHasKey( 'title', $parsedArgs );
		self::assertArrayNotHasKey( 'emptyContent', $parsedArgs );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\TagParser::parseArgs
	 */
	public function testParseArgsMultipleEquals() {
		$this->markTestSkipped( 'TODO: Fix  Call to a member function merge() on null' );

		$args = [
			'description=First Equal separates = Second Equal is included',
			'====',
			'==emptyKey',
		];

		$parsedArgs = $this->tagParser->parseArgs(
			$args,
			$this->parser,
			false
		);

		self::assertCount( 1, $parsedArgs );
		self::assertArrayHasKey( 'description', $parsedArgs );
		self::assertEquals(
			'First Equal separates = Second Equal is included',
			$parsedArgs['description']
		);
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\TagParser::parseText
	 */
	public function testParseText() {
		$this->markTestSkipped( 'TODO: Fix  Call to a member function merge() on null' );

		$text = <<<EOL
|title= Test Title
|keywords=A,B,C,D
|description=
|=emptyKey
|emptyContent=
EOL;

		$parsedArgs = $this->tagParser->parseText( $text, $this->parser, false );

		self::assertCount( 2, $parsedArgs );
		self::assertArrayHasKey( 'title', $parsedArgs );
		self::assertArrayNotHasKey( 'emptyContent', $parsedArgs );
	}
}
