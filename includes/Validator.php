<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * @file
 */

namespace MediaWiki\Extension\WikiSEO;

/**
 * Class Validator
 *
 * @package MediaWiki\Extension\WikiSEO
 */
class Validator {
	private static $validParams = [
		'title',
		'title_mode',
		'title_separator',

		'keywords',
		'description',

		'robots',
		'google_bot',

		'image', 'image_width', 'image_height', 'image_alt',

		'type',
		'site_name',
		'locale',
		'section',
		'author',

		'published_time',

		'twitter_site',
	];

	/**
	 * Removes all params that are not in $valid_params
	 *
	 * @param array $params Raw params
	 * @return array Validated params
	 */
	public function validateParams( array $params ) {
		wfDebugLog( 'wikiseo',
			sprintf( '%s: %s (%d) | %s', __METHOD__, 'Validating parameters', count( $params ),
				json_encode( $params ) ), 'all', $params );

		$validatedParams = [];

		foreach ( $params as $paramKey => $paramData ) {
			$inArray = in_array( $paramKey, self::$validParams, true );

			wfDebugLog( 'wikiseo', sprintf( '%s: %s in $validParams %s', __METHOD__, $paramKey,
				$inArray ? 'true' : 'false' ) );

			if ( $inArray === true ) {
				$validatedParams[$paramKey] = $paramData;
			}
		}

		wfDebugLog( 'wikiseo', sprintf( '%s: %s %d | %s', __METHOD__, 'Params after validation',
			count( $validatedParams ), json_encode( $validatedParams ) ), 'all', $validatedParams );

		return $validatedParams;
	}
}
