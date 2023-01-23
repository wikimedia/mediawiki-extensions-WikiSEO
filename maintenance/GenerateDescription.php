<?php

declare( strict_types=1 );
use MediaWiki\Extension\WikiSEO\DeferredDescriptionUpdate;
use MediaWiki\MediaWikiServices;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}
require_once "$IP/maintenance/Maintenance.php";

class GenerateDescription extends Maintenance {
	public function __construct() {
		parent::__construct();

		$this->addDescription( 'Creates SEO descriptions for wiki pages.' );
		$this->addOption( 'force', 'Force description generation, even for pages that already have one' );
		$this->addOption( 'cleanSentence', 'Cut off dangling sentences' );
		$this->addArg( 'namespaces', 'Comma separated list of namespace ids to work on' );
		$this->setBatchSize( 100 );

		$this->requireExtension( 'TextExtracts' );
		$this->requireExtension( 'WikiSEO' );
	}

	public function execute() {
		$pageQuery = WikiPage::getQueryInfo();
		$it = new BatchRowIterator(
			$this->getDB( DB_REPLICA ),
			$pageQuery['tables'],
			'page_id', $this->getBatchSize()
		);

		$validNamespaces = array_map( static function ( $ns ) {
			return (int)( $ns );
		}, explode( ',', $this->getArg( 0, '' ) ) );

		$pageProps = MediaWikiServices::getInstance()->getPageProps();
		$wikiPageFactory = MediaWikiServices::getInstance()->getWikiPageFactory();

		foreach ( $it as $batch ) {
			foreach ( $batch as $page ) {
				$wikiPage = $wikiPageFactory->newFromID( $page->page_id );

				if ( $wikiPage === null ) {
					$this->error( sprintf( "Page with id %s is null", $page->page_id ) );
					continue;
				}

				if ( $wikiPage->isRedirect() || !$wikiPage->getTitle()->inNamespaces( $validNamespaces ) ) {
					continue;
				}

				$properties = $pageProps->getProperties(
					$wikiPage->getTitle(),
					[
						'manualDescription',
						'description'
					]
				);

				$properties = array_shift( $properties );

				if ( isset( $properties['manualDescription'] ) ) {
					continue;
				}

				if ( isset( $properties['description'] ) && !$this->getOption( 'force' ) ) {
					$this->output(
						sprintf(
							"Page '%s' already has a description, skipping.\r\n",
							$wikiPage->getTitle()->getPrefixedText()
						)
					);
					continue;
				}

				$this->output(
					sprintf(
						"Generating description for '%s'\r\n",
						$wikiPage->getTitle()->getPrefixedText()
					)
				);

				( new DeferredDescriptionUpdate(
					$wikiPage->getTitle(),
					null,
					$this->getOption( 'cleanSentence', false )
				) )->doUpdate();
			}
		}
	}

}

$maintClass = GenerateDescription::class;
require_once RUN_MAINTENANCE_IF_MAIN;
