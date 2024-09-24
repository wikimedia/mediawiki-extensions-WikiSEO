<?php

declare( strict_types=1 );

namespace MediaWiki\Extension\WikiSEO\Tests\Generator\Plugin;

use MediaWiki\Extension\WikiSEO\Generator\Plugins\Citation;
use MediaWiki\Extension\WikiSEO\Tests\Generator\GeneratorTestBase;

/**
 * @group Database
 */
class CitationTest extends GeneratorTestBase {
	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\Citation::init
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\Citation::addMetadata
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\Citation::addMetaTags
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\Citation::addJsonLD
	 */
	public function testAddSingleAuthor() {
		$metadata = [
			'citation_author'  => 'Foo, Bar',
		];

		$out = $this->newInstance();

		$generator = new Citation();
		$generator->init( $metadata, $out );
		$generator->addMetadata();

		$this->assertArraySubmapSame( [ [
			'citation_author', 'Foo, Bar'
		] ], $out->getMetaTags() );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\Citation::init
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\Citation::addMetadata
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\Citation::addMetaTags
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\Citation::addJsonLD
	 */
	public function testAddMultipleAuthors() {
		$metadata = [
			'citation_author'  => 'Foo, Bar; Bar, Baz',
		];

		$out = $this->newInstance();

		$generator = new Citation();
		$generator->init( $metadata, $out );
		$generator->addMetadata();

		$this->assertArraySubmapSame( [
			[ 'citation_author', 'Foo, Bar' ],
			[ 'citation_author', 'Bar, Baz' ],
		], $out->getMetaTags() );
	}

	/**
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\Citation::init
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\Citation::addMetadata
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\Citation::addMetaTags
	 * @covers \MediaWiki\Extension\WikiSEO\Generator\Plugins\Citation::addJsonLD
	 */
	public function testAddMetadata() {
		$metadata = [
			'citation_author' => 'author',
			'citation_conference_title' => 'conference_title',
			'citation_creation_date' => 'creation_date',
			'citation_doi' => 'doi',
			'citation_firstpage' => 'firstpage',
			'citation_headline' => 'headline',
			'citation_isbn' => 'isbn',
			'citation_issn' => 'issn',
			'citation_issue' => 'issue',
			'citation_keywords' => 'keywords',
			'citation_journal_title' => 'journal_title',
			'citation_lastpage' => 'lastpage',
			'citation_license' => 'license',
			'citation_name' => 'name',
			'citation_pdf_url' => 'pdf_url',
			'citation_publication_date' => 'publication_date',
			'citation_publisher' => 'publisher',
			'citation_title' => 'title',
			'citation_type' => 'type',
			'citation_volume' => 'volume',
		];

		$out = $this->newInstance();

		$generator = new Citation();
		$generator->init( $metadata, $out );
		$generator->addMetadata();

		$this->assertArraySubmapSame( [
			[ 'citation_author', 'author' ],
			[ 'citation_conference_title', 'conference_title' ],
			[ 'citation_creation_date', 'creation_date' ],
			[ 'citation_doi', 'doi' ],
			[ 'citation_firstpage', 'firstpage' ],
			[ 'citation_headline', 'headline' ],
			[ 'citation_isbn', 'isbn' ],
			[ 'citation_issn', 'issn' ],
			[ 'citation_issue', 'issue' ],
			[ 'citation_journal_title', 'journal_title' ],
			[ 'citation_keywords', 'keywords' ],
			[ 'citation_lastpage', 'lastpage' ],
			[ 'citation_license', 'license' ],
			[ 'citation_name', 'name' ],
			[ 'citation_pdf_url', 'pdf_url' ],
			[ 'citation_publication_date', 'publication_date' ],
			[ 'citation_publisher', 'publisher' ],
			[ 'citation_title', 'title' ],
			[ 'citation_type', 'type' ],
			[ 'citation_volume', 'volume' ],
		], $out->getMetaTags() );
	}
}
