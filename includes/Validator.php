<?php

namespace Octfx\WikiSEO;

class Validator {
	private static $VALID_PARAMS = [ 'title', 'title_mode', 'title_separator', 'keywords', 'description',

		'robots',

		'image', 'image_width', 'image_height',

		'type', 'site_name', 'locale', 'url',

		'published_time', 'modified_time',

		'twitter:card',

	];

	public function validateParams( array $params ) {
		$validatedParams = [];

		foreach ( $params as $paramKey => $paramData ) {
			if ( in_array( $paramKey, self::$VALID_PARAMS, true ) ) {
				$validatedParams[$paramKey] = $paramData;
			}
		}

		return $validatedParams;
	}
}