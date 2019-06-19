<?php

namespace Octfx\WikiSEO\Generator\Plugins;

use Octfx\WikiSEO\Generator\GeneratorInterface;
use OutputPage;

class JsonLD implements GeneratorInterface {
	/**
	 * Valid Tags for this generator
	 *
	 * @var array
	 */
	protected static $tags = [
		'type',
		'image',
		'description',
		'published_time',
		'modified_time',
	];

	/**
	 * Tag name conversions for this generator
	 *
	 * @var array
	 */
	protected static $conversions = [
		'type' => '@type',

		'published_time' => 'datePublished',
		'modified_time'  => 'dateModified'
	];

	/**
	 * Initialize the generator with all metadata and the page to output the metadata onto
	 *
	 * @param array $metadata All metadata
	 * @param OutputPage $out The page to add the metadata to
	 *
	 * @return void
	 */
	public function init( array $metadata, OutputPage $out ) {
		$this->metadata = $metadata;
		$this->outputPage = $out;
	}

	/**
	 * @var array
	 */
	protected $metadata;

	/**
	 * @var OutputPage
	 */
	protected $outputPage;

	/**
	 * Add the metadata to the OutputPage
	 *
	 * @return void
	 */
	public function addMetadata() {
		$template = '<script type="application/ld+json">%s</script>';

		$meta = [
			'@context' => 'http://schema.org',
			'name'     => $this->outputPage->getHTMLTitle(),
		];

		foreach ( static::$tags as $tag ) {
			if ( array_key_exists( $tag, $this->metadata ) ) {
				$convertedTag = $tag;
				if ( isset( static::$conversions[$tag] ) ) {
					$convertedTag = static::$conversions[$tag];
				}

				$meta[$convertedTag] = $this->metadata[$tag];
			}
		}

		$this->outputPage->addHeadItem( 'jsonld-metadata', sprintf( $template, json_encode( $meta ) ) );
	}
}