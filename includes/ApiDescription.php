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

use ApiMain;
use ExtensionDependencyError;
use ExtensionRegistry;
use InvalidArgumentException;
use MediaWiki\Request\FauxRequest;
use MediaWiki\Title\Title;
use MWException;

class ApiDescription {

	/**
	 * Description source
	 * Currently only TextExtracts is supported
	 */
	private readonly string $source;

	/**
	 * ApiDescription constructor.
	 * @param Title $title The page title to get the description from
	 * @param bool $tryCleanSentence Flag to try to remove dangling sentences
	 * @param string $source
	 * @throws ExtensionDependencyError
	 */
	public function __construct(
		private readonly Title $title,
		private readonly bool $tryCleanSentence = false,
		string $source = 'extracts',
	) {
		$this->source = strtolower( $source );

		$this->checkExtensions();
	}

	/**
	 * Request the description
	 *
	 * @return string
	 * @throws MWException
	 */
	public function getDescription(): string {
		switch ( $this->source ) {
			case 'extracts':
				return $this->getDescriptionFromExtracts();

			default:
				return '';
		}
	}

	/**
	 * Call text extracts
	 * Returns an empty string on error
	 *
	 * @return string
	 * @throws MWException
	 */
	public function getDescriptionFromExtracts(): string {
		$query = [
			'format' => 'json',
			'action' => 'query',
			'prop' => 'extracts',
			'titles' => $this->title->getPrefixedText(),
			'exchars' => 160,
			'exintro' => 1,
			'explaintext' => 1,
			'exsectionformat' => 'plain'
		];

		$request = new ApiMain( new FauxRequest( $query ) );

		$request->execute();

		$data = $request->getResult()->getResultData();

		if ( $data === null ) {
			return '';
		}

		if ( !isset( $data['batchcomplete'] ) ||
			!isset( $data['query']['pages'] ) ||
			$data['batchcomplete'] === false ||
			empty( $data['query']['pages'] )
		) {
			return '';
		}

		$text = array_shift( $data['query']['pages'] )['extract']['*'] ?? '';

		if ( $this->tryCleanSentence && substr( $text, -1 ) !== '.' ) {
			$parts = explode( '.', $text );
			array_pop( $parts );
			$text = sprintf( '%s.', implode( '.', $parts ) );
		}

		return str_replace( "\n", ' ', strip_tags( $text ) );
	}

	/**
	 * @throws ExtensionDependencyError
	 * @throws InvalidArgumentException
	 */
	private function checkExtensions(): void {
		switch ( $this->source ) {
			case 'extracts':
				if ( !ExtensionRegistry::getInstance()->isLoaded( 'TextExtracts' ) ) {
					throw new ExtensionDependencyError( [
						[
							'msg' => 'TextExtracts not loaded',
							'type' => 'missing-extensions',
							'missing' => 'TextExtracts',
						]
					] );
				}
				return;

			default:
				throw new InvalidArgumentException(
					sprintf( 'Format "%s" is not implemented. Use "extracts".', $this->source )
				);
		}
	}
}
