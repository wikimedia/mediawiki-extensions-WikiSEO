<?php

namespace MediaWiki\Extension\WikiSEO\Tests\Generator;

use HashConfig;
use MediaWikiIntegrationTestCase;
use MultiConfig;
use OutputPage;
use RequestContext;
use Title;
use WebRequest;

class GeneratorTest extends MediaWikiIntegrationTestCase {
	/**
	 * @param array $config
	 * @param WebRequest|null $request
	 * @param array $options
	 * @param string $title
	 *
	 * @return OutputPage
	 * @see \OutputPageTest::newInstance()
	 */
	protected function newInstance( $config = [], WebRequest $request = null, $options = [], $title = 'My test page' ) {
		$context = new RequestContext();

		$context->setConfig(
			new MultiConfig(
				[
				new HashConfig(
					$config + [
					'AppleTouchIcon'            => false,
					'DisableLangConversion'     => true,
					'EnableCanonicalServerLink' => false,
					'Favicon'                   => false,
					'Feed'                      => false,
					'LanguageCode'              => false,
					'ReferrerPolicy'            => false,
					'RightsPage'                => false,
					'RightsUrl'                 => false,
					'UniversalEditButton'       => false,
					]
				),
				$context->getConfig()
				]
			)
		);

		if ( !in_array( 'notitle', (array)$options, true ) ) {
			$context->setTitle( Title::newFromText( $title ) );
		}

		if ( $request ) {
			$context->setRequest( $request );
		}

		$out = new OutputPage( $context );
		$out->setArticleFlag( true );

		return $out;
	}
}
