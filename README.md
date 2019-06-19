# MediaWiki WikiSEO extension

This is a simple MediaWiki extension to give you control over the HTML title 
and meta tags via a tag or parser function.  

## Steps to take

### Manual installation

You can get the extension via Git (specifying WikiSEO as the destination directory):

    git clone https://github.com/octfx/wiki-seo.git WikiSEO

Or [download it as zip archive](https://github.com/octfx/WikiSEO/archive/master.zip).

In either case, the "WikiSEO" extension should end up in the "extensions" directory 
of your MediaWiki installation. If you got the zip archive, you will need to put it 
into a directory called WikiSEO.

Add the following line to the end of your LocalSettings file:

    wfLoadExtension( 'WikiSEO' );

## Usage

Use this extension as described [on the extensions documentation page](https://www.mediawiki.org/wiki/Extension:WikiSEO).
