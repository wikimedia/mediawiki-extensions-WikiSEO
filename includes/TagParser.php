<?php

namespace Octfx\WikiSEO;

class TagParser {
	private $validParameters = [ 'title', 'title_mode', //append, prepend, replace
		'title_separator', 'keywords', 'description', 'google-site-verification', 'robots', 'google', 'googlebot', 'og:image', 'og:image:width', 'og:image:height', 'og:type', 'og:site_name', 'og:locale', 'og:url', 'og:title', 'og:updated_time', 'article:author', 'article:publisher', 'article:published_time', 'article:modified_time', 'article:section', 'article:tag', 'twitter:card', 'twitter:site', 'twitter:domain', 'twitter:creator', 'twitter:image:src', 'twitter:description', 'DC.date.issued', 'DC.date.created', 'name', ];

	public function parseArgs( array $args ) {
		$results = [];

		foreach ( $args as $arg ) {
			$pair = explode( '=', $arg, 2 );
			$pair = array_map( 'trim', $pair );

			if ( count( $pair ) === 2 ) {
				list( $name, $value ) = $pair;
				$results[$name] = $value;
			}

			if ( count( $pair ) === 1 ) {
				$results[$pair[0]] = true;
			}
		}

		return array_filter($results, 'mb_strlen');
	}

	public function parseText( $text ) {
		$lines = explode( '|', $text );

		return $this->parseArgs( $lines );
	}
}