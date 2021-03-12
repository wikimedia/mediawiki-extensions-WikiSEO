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

use ExtensionDependencyError;
use ExtensionRegistry;
use InvalidArgumentException;
use MediaWiki\MediaWikiServices;
use Title;

class ApiDescription {

	/**
	 * @var Title The page title to get the description from
	 */
	private $title;

	/**
	 * Flag to try remove dangling sentences
	 *
	 * @var bool
	 */
	private $tryCleanSentence;

	/**
	 * Description source
	 * Currently only TextExtracts is supported
	 *
	 * @var string
	 */
	private $source;

	/**
	 * ApiDescription constructor.
	 * @param Title $title
	 * @param bool $tryCleanSentence
	 * @param string $source
	 * @throws ExtensionDependencyError
	 */
	public function __construct( Title $title, bool $tryCleanSentence = false, string $source = 'extracts' ) {
		$this->title = $title;
		$this->tryCleanSentence = $tryCleanSentence;
		$this->source = strtolower( $source );

		$this->checkExtensions();
	}

	/**
	 * Request the description
	 *
	 * @return string
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
	 */
	public function getDescriptionFromExtracts(): string {
		$query = http_build_query( [
			'format' => 'json',
			'utf8' => 1,
			'formatversion' => 2,
			'action' => 'query',
			'prop' => 'extracts',
			'titles' => $this->title->getDBkey(),
			'exchars' => 160,
			'exintro' => 1,
			'explaintext' => 1,
			'exsectionformat' => 'plain'
		] );

		$url = sprintf(
			'%s/api.php?%s',
			MediaWikiServices::getInstance()->getMainConfig()->get( 'ScriptPath' ),
			$query
		);

		$response = MediaWikiServices::getInstance()->getHttpRequestFactory()->get( $url );

		if ( $response === null ) {
			return '';
		}

		$result = json_decode( $response, true );

		if ( !isset( $result['batchcomplete'] ) ||
			$result['batchcomplete'] === false ||
			!isset( $result['query']['pages'] ) ||
			empty( $result['query']['pages'] ) ) {
			return '';
		}

		$text = $result['query']['pages'][0]['extract'] ?? '';

		if ( $this->tryCleanSentence === true && substr( $text, -1 ) !== '.' ) {
			$parts = explode( '.', $text );
			array_pop( $parts );
			$text = sprintf( '%s.', implode( '.', $parts ) );
		}

		return strip_tags( $text );
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
