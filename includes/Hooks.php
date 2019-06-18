<?php

namespace Octfx\WikiSEO;

use OutputPage;
use Parser;
use Skin;
use MWException;

class Hooks {
	/**
	 * Customisations to OutputPage right before page display.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 */
	public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {

	}


	/**
	 * Register parser hooks.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserFirstCallInit
	 * @see https://www.mediawiki.org/wiki/Manual:Parser_functions
	 *
	 * @param Parser $parser
	 *
	 * @throws MWException
	 */
	public static function onParserFirstCallInit( Parser $parser ) {
		// parserTag
		$parser->setHook( 'seo', 'Octfx\WikiSEO\WikiSEO::fromTag' );

		// parserFunction
		$parser->setFunctionHook( 'seo', 'Octfx\WikiSEO\WikiSEO::fromParserFunction', Parser::SFH_OBJECT_ARGS );
	}

	public static function onOutputPageBeforeHTML( OutputPage $out, &$text ) {

	}
}