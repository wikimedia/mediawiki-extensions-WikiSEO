# MediaWiki WikiSEO extension

**Starting from 2023-01-23, WikiSEO requires MediaWiki 1.39.0**

The WikiSEO extension allows you to replace, append or prepend the html title tag content, and allows you to add common SEO meta keywords and a meta description.  

**Extension Page: [Extension:WikiSEO](https://www.mediawiki.org/wiki/Extension:WikiSEO)**

## Installation
* [Download](https://www.mediawiki.org/wiki/Special:ExtensionDistributor/WikiSEO) and place the file(s) in a directory called WikiSEO in your extensions/ folder.
* Add the following code at the bottom of your [LocalSettings.php](https://www.mediawiki.org/wiki/Special:MyLanguage/Manual:LocalSettings.php):
```
wfLoadExtension( 'WikiSEO' );
```
* Configure as required.
* Done – Navigate to Special:Version on your wiki to verify that the extension is successfully installed.

## Configuration
The following variables are in use by this extension.

| Variable                             | Description                                                                                                                                                                                                                                                                | Usage                                                          |
|--------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|----------------------------------------------------------------|
| Site / App Keys                      |                                                                                                                                                                                                                                                                            |                                                                |
| $wgGoogleSiteVerificationKey         | Setting this variable will add a `<meta name="google-site-verification" content="CODE">` tag to every page.                                                                                                                                                                | $wgGoogleSiteVerificationKey = 'CODE';                         |
| $wgBingSiteVerificationKey           | Setting this variable will add a `<meta name="msvalidate.01" content="CODE">` tag to every page.                                                                                                                                                                           | $wgBingSiteVerificationKey= 'CODE';                            |
| $wgFacebookAppID                     | Setting this variable will add a `<meta property="fb:app_id" content="ID">` tag to every page.                                                                                                                                                                             | $wgFacebookAppID= 'App_ID';                                    |
| $wgFacebookAdmins                    | Setting this variable will add a `<meta property="fb:admins" content="ID1,ID2,...">` tag to every page.                                                                                                                                                                    | $wgFacebookAdmins= 'ID1,ID2,...';                              |
| $wgYandexSiteVerificationKey         | Setting this variable will add a `<meta name="yandex-verification" content="CODE">` tag to every page.                                                                                                                                                                     | $wgYandexSiteVerificationKey= 'CODE';                          |
| $wgAlexaSiteVerificationKey          | Setting this variable will add a `<meta name="alexaVerifyID" content="CODE">` tag to every page.                                                                                                                                                                           | $wgAlexaSiteVerificationKey= 'CODE';                           |
| $wgPinterestSiteVerificationKey      | Setting this variable will add a `<meta name="p:domain_verify" content="CODE">` tag to every page.                                                                                                                                                                         | $wgPinterestSiteVerificationKey= 'CODE';                       |
| $wgNortonSiteVerificationKey         | Setting this variable will add a `<meta name="norton-safeweb-site-verification" content="CODE">` tag to every page.                                                                                                                                                        | $wgNortonSiteVerificationKey= 'CODE';                          |
| $wgNaverSiteVerificationKey          | Setting this variable will add a `<meta name="naver-site-verification" content="CODE">` tag to every page.                                                                                                                                                                 | $wgNaverSiteVerificationKey = 'CODE';                          |
| General Options                      |                                                                                                                                                                                                                                                                            |                                                                |
| $wgMetadataGenerators                | Array containing the metadata generator classes to load. Custom generators can be added by appending the class name without the namespace to this array.                                                                                                                   | $wgMetadataGenerators = ["OpenGraph", "Twitter", "SchemaOrg"]  |
| $wgTwitterSiteHandle                 | Only used when Twitter generator is loaded. Setting this variable will add a <meta property="twitter:site" content="@SITE_HANDLE"> tag to every page.                                                                                                                      | $wgTwitterSiteHandle = '@SITE_HANDLE';                         |
| $wgWikiSeoDefaultImage               | Set a default image to use if no image is set on the site. If this variable is not set the sites logo will be used.                                                                                                                                                        | $wgWikiSeoDefaultImage = 'File:Localfile.jpg';                 |
| $wgTwitterCardType                   | Defaults to summary_large_image for the twitter card type.                                                                                                                                                                                                                 | $wgTwitterCardType = 'summary';                                |
| $wgWikiSeoDefaultLanguage            | A default language code with area to generate a <link rel="alternate" href="current Url" hreflang="xx-xx"> for.                                                                                                                                                            | $wgWikiSeoDefaultLanguage = 'de-de';                           |
| $wgWikiSeoDisableLogoFallbackImage   | Disables setting $wgLogo as the fallback image if no image was set.                                                                                                                                                                                                        |                                                                |
| $wgWikiSeoNoindexPageTitles          | An array of page titles where a 'noindex' robot tag should be added.                                                                                                                                                                                                       | $wgWikiSeoNoindexPageTitles = [ 'Custom_Title', 'Main_Page' ]; |
| $wgWikiSeoEnableAutoDescription      | Set to true to try to request a description from textextracts, if no description was given, or the description key is set to 'textextracts' or 'auto'. This requires Extension:TextExtracts to be loaded. The description is generated when saving the page after an edit. | $wgWikiSeoEnableAutoDescription = true;                        |
| $wgWikiSeoTryCleanAutoDescription    | Set to true, if WikiSEO should try to remove dangling sentences when using descriptions from textextracts. This will remove all characters after the last found dot.                                                                                                       | $wgWikiSeoTryCleanAutoDescription = true;                      |
| $wgWikiSeoOverwritePageImage         | Set to true to enable overwriting the iamge set by extension PageImages.                                                                                                                                                                                                   | $wgWikiSeoOverwritePageImage = true;                           |
| Social Media Images                  |                                                                                                                                                                                                                                                                            |                                                                |
| $wgWikiSeoEnableSocialImages         | Generate dedicated social media icons for pages                                                                                                                                                                                                                            | $wgWikiSeoEnableSocialImages = true;                           |
| $wgWikiSeoSocialImageIcon            | The icon/watermark to add to the social media image. Use a local file name                                                                                                                                                                                                 | $wgWikiSeoSocialImageIcon = LocalFile.jpg;                     |
| $wgWikiSeoSocialImageWidth           | Width of the social media image                                                                                                                                                                                                                                            | $wgWikiSeoSocialImageWidth = 1200;                             |
| $wgWikiSeoSocialImageHeight          | Height of the social media image                                                                                                                                                                                                                                           | $wgWikiSeoSocialImageHeight = 620;                             |
| $wgWikiSeoSocialImageTextColor       | Color of the text on the social image                                                                                                                                                                                                                                      | $wgWikiSeoSocialImageTextColor = #fff;                         |
| $wgWikiSeoSocialImageShowAuthor      | Show the author of the current page revision                                                                                                                                                                                                                               | $wgWikiSeoSocialImageShowAuthor = true;                        |
| $wgWikiSeoSocialImageShowLogo        | Show the Wiki logo in the top right corner                                                                                                                                                                                                                                 | $wgWikiSeoSocialImageShowLogo = true;                          |
| $wgWikiSeoSocialImageBackgroundColor | Default background color if no page image is found                                                                                                                                                                                                                         |                                                                |

## Usage
The extension can be used via the ``{{#seo}}`` parser function or in Lua modules by using `mw.ext.seo`. It accepts the following named parameters in any order.

* title
  * The title you want to appear in the html title tag
* title_mode
  * Set to append, prepend, or replace (default) to define how the title will be amended.
* title_separator
  * The separator in case titlemode was set to append or prepend; " - " (default)
* keywords
  * A comma separated list of keywords for the meta keywords tag
* description
  * A text description for the meta description tag
* robots
  * Controls the behavior of search engine crawling and indexing
  * Assumes the following order: index policy, follow policy
  * Example: robots=index,nofollow / noindex,follow / index,follow / etc.
  * Invalid: robots=follow,index
* googlebot
  * Controls the behavior of the google crawler
* hreflang_xx-xx[]
  * Adds `<link rel="alternate" href="url" hreflang="xx-xx">` elements 

**Tags related to the Open Graph protocol**  
* type
  * The type of your object, e.g., "video.movie". Depending on the type you specify, other properties may also be required.
* image
  * An image URL which should represent your object within the graph. The extension will automatically add the right image url, width and height if an image name is set as the parameter. Example ``image = Local_file_to_use.png``. Alternatively a full url to an image can be used, image_width and image_height will then have to be set manually. If no parameter is set, the extension will use ``$wgLogo`` as a fallback or the local file set through ``$wgWikiSeoDefaultImage``.
* image_width
  * The image width in px. (Automatically set if an image name is set in image)
* image_height
  * The image height in px. (Automatically set if an image name is set in image)
* image_alt
  * Alternative description for the image.
* locale
  * The locale these tags are marked up in. Of the format language_TERRITORY.
* site_name
  * If your object is part of a larger web site, the name which should be displayed for the overall site. e.g., "IMDb".

**Tags related to Open Graph type "article"**
* author
  * Writers of the article.
* keywords
  * Translates into article:tag
* section
  * A high-level section name. E.g. Technology
* published_time
  * When the article was first published. ISO 8601 Format.

**Tags related to Twitter Cards (see OpenGraph Tags)**
* twitter_site
  * If you did not set a global site name through `$wgTwitterSiteHandle`, you can set a site handle per page. If a global site handle is set this key will be ignored.
* twitter_card
  * Allows per page overriding of `$wgWikiSEOTwitterCardType`

**Tags related to the Citation generator**
* description
* keywords
* citation_type
* citation_name
* citation_headline
* citation_date_published
* citation_date_created
* citation_page_start
* citation_doi
* citation_author
* citation_publisher
* citation_license
* citation_volume

** Tags related to the Dublin Core generator**
* description
* title
* author
* locale
* site_name
* dc.identifier.wikidata

## Examples
### Adding static values
```
{{#seo:
 |title=Your page title
 |titlemode=append
 |keywords=these,are,your,keywords
 |description=Your meta description
 |image=Uploaded_file.png
 |image_alt=Wiki Logo
}}
```

### Adding dynamic values
If you need to include variables or templates you should use the parser function to ensure they are properly parsed. This allows you to use Cargo or Semantic MediaWiki, with Page Forms, for data entry, or for programmatic creation of a page title from existing variables or content...
```
{{#seo:
 |title={{#if: {{{page_title|}}} | {{{page_title}}} | Welcome to WikiSEO}}
 |titlemode={{{title_mode|}}}
 |keywords={{{keywords|}}}
 |description={{{description|}}}
 |published_time={{REVISIONYEAR}}-{{REVISIONMONTH}}-{{REVISIONDAY2}}
}}
```
```
{{#seo:
|title_mode=append
|title=Example SEO Wiki
|keywords=WikiSEO, SEO, MediaWiki
|description=An example description for this wiki
|image=Wiki_Logo.png
|image_alt=Wiki Logo
|site_name=Example SEO Wiki
|locale=en_EN
|type=website
|modified_time={{REVISIONYEAR}}-{{REVISIONMONTH}}-{{REVISIONDAY2}}
|published_time=2020-11-01
}}
```

### Using the lua module
WikiSEO exposes a lua method as `mw.ext.seo.set`

Example module:
```lua
-- Module:SEO
local seo = {}

--[[
argTable format:
{
  title_mode = 'append',
  title = 'Example Seo Wiki',
  keywords = 'WikiSEO, SEO, MediaWiki',
  -- ...
}
]]--
function seo.set( argTable )
  mw.ext.seo.set( argTable )
end

function seo.setStatic()
  mw.ext.seo.set{
    title_mode = 'append',
    title = 'Example Seo Wiki',
    keywords = 'WikiSEO, SEO, MediaWiki',  
  }
end

return seo
```

The module would now be callable as `{{#invoke:SEO|add|title=ExampleTitle|keywords=WikiSEO, SEO, MediaWiki}}` or `{{#invoke:SEO|addStatic}}`.

### Hreflang Attributes
```
{{#seo:
 |hreflang_de-de=https://example.de/page
 |hreflang_nl-nl=https://example.nl/page-nl
 |hreflang_en-us=https://website.com/
}}
```
Will generate the following `<link>` elements:
```html
<link rel="alternate" href="https://example.de/page" hreflang="de-de">
<link rel="alternate" href="https://example.nl/page-nl" hreflang="nl-nl">
<link rel="alternate" href="https://website.com/" hreflang="en-us">
```

## Title Modes
Example: Page with title `Example Page`

### Append
```
{{#seo:
 |title_mode=append
 |title=Appended Title
}}
```

HTML Title result: `Example Page - Appended Title`

### Prepend
```
{{#seo:
 |title_mode=prepend
 |title=Prepended Title
}}
```

HTML Title result: `Prepended Title - Example Page`

### Prepend (changed separator)
```
{{#seo:
 |title_mode=prepend
 |title=Prepended Title
 |title_separator=<nowiki>&nbsp;>>&nbsp;</nowiki>
}}
```

HTML Title result: `Prepended Title >> Example Page`

### Replace (default)
```
{{#seo:
 |title_mode=replace
 |title=Replaced Title
}}
```

HTML Title result: `Replaced Title`

## Hooks
WikiSEO exposes Hooks that allow to customize the metadata that gets added to the page and saved in page props.

* `WikiSEOPreAddMedatada`
  * `onWikiSEOPreAddMedatada( &$metadata )`
  * An array of key-value pairs validated through `Validator::validateParams`
  * Run right before the instantiation of the metadata generators
* `WikiSEOPreAddPageProps`
  * `onWikiSEOPreAddPageProps( &$metadata )`
  * An array of key-value pairs validated through `Validator::validateParams`
  * Run right before setting the page props
* `WikiSEOLuaPreAddPageProps`
  * `onWikiSEOLuaPreAddPageProps( &$metadata )`
  * An array of key-value pairs validated through `Validator::validateParams`
  * Run right before setting the page props
  * Only run when the lua module is called
                                                                                                                                                                                                                   | $wgWikiSeoSocialImageBackgroundColor = #14181f;                |

## Migrating to v2
### Removed tags
* DC.date.created
* DC.date.issued
* google
* name
* og:title (automatically set)
* og:url (automatically set)
* twitter:card (automatically set)
* twitter:creator
* twitter:domain
* article:modified_time / og:updated_time (automatically set)

### Removed aliases
* metakeywords / metak
  * use keywords instead
* metadescription / metad
  * use description instead
* titlemode / title mode
  * use title_mode instead

### Changed argument names
* article:author -> author
* article:section -> section
* article:tag -> keywords
* article:published_time -> published_time
* og:image / twitter:image:src -> image
* og:image:width -> image_width
* og:image:height -> image_height
* og:locale -> locale
* og:site_name -> site_name
* og:title -> title
* og:type -> type
* twitter:description -> description

## Known Issues
[Extension:PageImages](https://www.mediawiki.org/wiki/Extension:PageImages) will add an og:image tag if an image is found on the page. This overwrites any og:image tag set using this extension.  
There is currently no way to disable PageImages setting the meta tag.

## Notes
If you only want to override the display title on pages (not append words to it), you might also look at the DISPLAYTITLE tag in combination with the [Manual:$wgAllowDisplayTitle](https://www.mediawiki.org/wiki/Manual:$wgAllowDisplayTitle) and [Manual:$wgRestrictDisplayTitle](https://www.mediawiki.org/wiki/Manual:$wgRestrictDisplayTitle) settings.

### schema.org
The ``SchemaOrg`` generator will set a SearchAction property based on Special:Search.  
The properties publisher and author will be set to Organization with the name set to the content of ``$wgSitename``.  
``dateModified`` will be automatically set by fetching the latest revision timestamp. If no published_time is set, datePublished will be set to the latest revision timestamp.

### OpenGraph
``article:modified_time`` will be automatically set by fetching the latest revision timestamp. If no ``published_time`` is set, ``article:published_time`` will be set to the latest revision timestamp.  
This can be disabled on a per-page basis by setting `modified_time=-` through the parser.

## Extending this extension
Metadata generators live in the ``includes/Generator/Plugins`` directory.  
A generator has to implement the ``GeneratorInterface``.  
To load the generator simply add its name to ``$wgMetadataGenerators``.
