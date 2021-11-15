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

declare( strict_types=1 );

namespace MediaWiki\Extension\WikiSEO;

use MediaWiki\MediaWikiServices;
use Scribunto_LuaLibraryBase;

class SeoLua extends Scribunto_LuaLibraryBase {
	/**
	 * Registers the callable lua methods
	 *
	 * @return array
	 */
	public function register(): array {
		$lib = [
			'set' => [ $this, 'set' ],
		];

		return $this->getEngine()->registerInterface(
			sprintf(
				'%s%s%s',
				__DIR__,
				DIRECTORY_SEPARATOR,
				'mw.ext.seo.lua'
			),
			$lib,
			[]
		);
	}

	/**
	 * Validates function arguments through Validator::validateParams
	 * All validated params are written to the page props, which in turn are picked up by WikiSEO
	 * through the onBeforePageDisplay Hook
	 *
	 * @see Validator::validateParams()
	 * @see Hooks::onBeforePageDisplay()
	 */
	public function set(): void {
		$args = func_get_args();

		if ( !isset( $args[0] ) ) {
			return;
		}

		$args = $args[0];

		$validated = Validator::validateParams( $args );

		MediaWikiServices::getInstance()->getHookContainer()->run(
			'WikiSEOLuaPreAddPageProps',
			[
				&$validated,
			]
		);

		$out = $this->getParser()->getOutput();
		foreach ( $validated as $metaKey => $value ) {
			// MW 1.38+
			if ( method_exists( $out, 'setPageProperty' ) ) {
				$out->setPageProperty( $metaKey, $value );
			} else {
				$out->setProperty( $metaKey, $value );
			}
		}
	}
}
