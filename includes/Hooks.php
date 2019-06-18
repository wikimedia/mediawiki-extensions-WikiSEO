<?php


namespace Octfx\WikiSEO;


use FormatJson;
use OutputPage;
use PPFrame;
use Skin;
use Parser;
use DatabaseUpdater;
use SkinTemplate;

class Hooks
{
    /**
     * Customisations to OutputPage right before page display.
     *
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
     */
    public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
        global $wgExampleEnableWelcome;
        if ( $wgExampleEnableWelcome ) {
            // Load our module on all pages
            $out->addModules( 'ext.Example.welcome' );
        }
    }


    /**
     * Register parser hooks.
     *
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserFirstCallInit
     * @see https://www.mediawiki.org/wiki/Manual:Parser_functions
     * @param Parser $parser
     */
    public static function onParserFirstCallInit( Parser $parser ) {
        // Add the following to a wiki page to see how it works:
        // <dump>test</dump>
        // <dump foo="bar" baz="quux">test content</dump>
        $parser->setHook( 'dump', [ self::class, 'parserTagDump' ] );
        // Add the following to a wiki page to see how it works:
        // {{#echo: hello }}
        $parser->setFunctionHook( 'echo', [ self::class, 'parserFunctionEcho' ] );
        // Add the following to a wiki page to see how it works:
        // {{#showme: hello | hi | there }}
        $parser->setFunctionHook( 'showme', [ self::class, 'parserFunctionShowme' ] );
    }

    public static function onOutputPageBeforeHTML( OutputPage &$out, &$text ) {

    }
}