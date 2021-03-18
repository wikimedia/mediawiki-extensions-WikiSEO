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

namespace MediaWiki\Extension\WikiSEO\Hooks;

use CommentStoreComment;
use ExtensionDependencyError;
use MediaWiki\Extension\WikiSEO\ApiDescription;
use MediaWiki\Extension\WikiSEO\WikiSEO;
use MediaWiki\Hook\BeforePageDisplayHook;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RenderedRevision;
use MediaWiki\Storage\Hook\MultiContentSaveHook;
use MediaWiki\User\UserIdentity;
use OutputPage;
use ParserOutput;
use Skin;
use Status;
use Title;

/**
 * Hooks to run relating the page
 */
class PageHooks implements BeforePageDisplayHook, MultiContentSaveHook {

	/**
	 * Extracts the generated SEO HTML comments form the page and adds them as meta tags
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		$seo = new WikiSEO();
		$seo->setMetadataFromPageProps( $out );
		$seo->addMetadataToPage( $out );
	}

	/**
	 * @param RenderedRevision $renderedRevision
	 * @param UserIdentity $user
	 * @param CommentStoreComment $summary
	 * @param int $flags
	 * @param Status $status
	 * @return void
	 * @throws ExtensionDependencyError
	 */
	public function onMultiContentSave( $renderedRevision, $user, $summary, $flags, $status ): void {
		$output = $renderedRevision->getRevisionParserOutput();

		if ( $output === null ) {
			return;
		}

		$autoEnabled = MediaWikiServices::getInstance()->getMainConfig()->get( 'WikiSeoEnableAutoDescription' );
		if ( (bool)$autoEnabled === false || $output->getExtensionData( 'manualDescription' ) === true ) {
			return;
		}

		$this->saveAutoDescription( $output );
	}

	/**
	 * @param ParserOutput $output
	 * @throws ExtensionDependencyError
	 */
	private function saveAutoDescription( ParserOutput $output ): void {
		$description = $this->loadDescriptionFromApi( $output->getTitleText() );

		if ( $description === null || $description === '' ) {
			// This will only run for new pages
			$description = trim( substr( strip_tags( $output->getText() ), 0, 160 ) );
		}

		$output->setProperty( 'description', $description );
	}

	/**
	 * @param string $title
	 * @return string|null
	 * @throws ExtensionDependencyError
	 */
	private function loadDescriptionFromApi( string $title ): ?string {
		$descriptor = new ApiDescription(
			Title::newFromText( $title ),
			MediaWikiServices::getInstance()->getMainConfig()->get( 'WikiSeoTryCleanAutoDescription' ) === true
		);

		try {
			return $descriptor->getDescription();
		} catch ( ExtensionDependencyError $e ) {
			wfLogWarning( $e->getMessage() );
		}

		return null;
	}
}
