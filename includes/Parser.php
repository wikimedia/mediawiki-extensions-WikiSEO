<?php

namespace Octfx\WikiSEO;

class Parser {
	private $validParameters = [ 'title', 'title_mode', //append, prepend, replace
	                             'title_separator', 'keywords', 'description', 'google-site-verification', 'robots', 'google', 'googlebot', 'og:image', 'og:image:width', 'og:image:height', 'og:type', 'og:site_name', 'og:locale', 'og:url', 'og:title', 'og:updated_time', 'article:author', 'article:publisher', 'article:published_time', 'article:modified_time', 'article:section', 'article:tag', 'twitter:card', 'twitter:site', 'twitter:domain', 'twitter:creator', 'twitter:image:src', 'twitter:description', 'DC.date.issued', 'DC.date.created', 'name', ];

	public function parseArgs( $args ) {
		$args = array_slice( $args, 1, count( $args ) );
		$results = [];

		foreach ( $args as $arg ) {
			$pair = explode( '=', $arg, 2 );
			$pair = array_map( 'trim', $pair );

			if ( count( $pair ) === 2 ) {
				$name = $pair[0];
				$value = $pair[1];
				$results[$name] = $value;
			}

			if ( count( $pair ) === 1 ) {
				$name = $pair[0];
				$results[$name] = true;
			}
		}

		return $results;
	}
}