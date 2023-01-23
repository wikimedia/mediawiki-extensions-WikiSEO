<?php

namespace MediaWiki\Extension\WikiSEO\Api;

use ApiMain;
use Config;
use Exception;
use FauxRequest;
use Imagick;
use ImagickDraw;
use ImagickDrawException;
use ImagickException;
use ImagickPixel;
use ImagickPixelException;
use MediaWiki\MediaWikiServices;
use MediaWiki\Rest\LocalizedHttpException;
use MediaWiki\Rest\Response;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Rest\StringStream;
use MWException;
use Title;
use Wikimedia\Message\MessageValue;
use Wikimedia\ParamValidator\ParamValidator;

class RestSocialMediaImage extends SimpleHandler {

	/**
	 * @var Config WikiSEO Config
	 */
	private Config $config;

	/**
	 * Generates the social media image based on the provided title and the title's page props
	 *
	 * @return Response
	 * @throws LocalizedHttpException
	 */
	public function run(): Response {
		if ( !extension_loaded( 'Imagick' ) ) {
			$this->makeError( 'wiki-seo-api-imagick-missing', 500 );
		}

		$params = $this->getValidatedParams();

		$title = MediaWikiServices::getInstance()->getTitleFactory()->newFromText( $params['title'] );

		if ( $title === null ) {
			$this->makeError( 'wiki-seo-api-title-empty', 400 );
		}

		$this->config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'WikiSEO' );

		if ( $this->config->get( 'WikiSeoEnableSocialImages' ) === false ) {
			$this->makeError( 'wiki-seo-api-disabled', 503 );
		}

		try {
			$out = $this->createImage( $this->createBackground( $title ), $title );
		} catch ( Exception $e ) {
			$this->makeError( 'wiki-seo-api-image-error', 500 );
		}

		$response = $this->getResponseFactory()->create();
		$response->setHeader( 'Content-Type', 'image/jpeg' );

		try {
			$stream = new StringStream( $out->getImageBlob() );
			$response->setBody( $stream );
		} catch ( Exception $e ) {
			$this->makeError( 'wiki-seo-api-image-error', 500 );
		}

		return $response;
	}

	/** @inheritDoc */
	public function getParamSettings(): array {
		return [
			'title' => [
				self::PARAM_SOURCE => 'path',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
			'background' => [
				self::PARAM_SOURCE => 'query',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
			],
			'backgroundColor' => [
				self::PARAM_SOURCE => 'query',
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
			],
		];
	}

	/**
	 * Creates the image background
	 * Either a local file, or a flat color provided by $wgWikiSeoSocialImageBackgroundColor
	 *
	 * @param Title $title
	 * @return Imagick|ImagickPixel|null
	 * @throws ImagickPixelException
	 */
	private function createBackground( Title $title ) {
		$params = $this->getValidatedParams();
		$background = null;

		if ( isset( $params['background'] ) ) {
			$props = [
				'background' => $params['background'],
			];
		} else {
			$props = MediaWikiServices::getInstance()->getPageProps()->getProperties( $title, 'page_image_free' );
		}

		if ( !empty( $props ) ) {
			$background = MediaWikiServices::getInstance()->getTitleFactory()->makeTitle(
				NS_FILE,
				array_pop( $props )
			);
			$file = MediaWikiServices::getInstance()->getRepoGroup()->getLocalRepo()->findFile( $background );

			if ( $file !== false ) {
				$background = new Imagick();

				try {
					$background->readImage( $file->getLocalRefPath() );
				} catch ( ImagickException $e ) {
					$background = null;
				}
			}
		}

		if ( $background === null ) {
			$background = new ImagickPixel( $this->config->get( 'WikiSeoSocialImageBackgroundColor' ) );

			if ( isset( $params['backgroundColor'] ) ) {
				$background = new ImagickPixel( $params['backgroundColor'] );
			}
		}

		return $background;
	}

	/**
	 * Combines all parts into one image
	 *
	 * @param Imagick|ImagickPixel $background
	 * @param Title $title
	 * @return Imagick
	 * @throws ImagickDrawException
	 * @throws ImagickException|ImagickPixelException
	 */
	private function createImage( $background, Title $title ): Imagick {
		$textColor = new ImagickPixel( $this->config->get( 'WikiSeoSocialImageTextColor' ) );

		$imagick = $this->initImage( $background );

		$this->addBackgroundImage( $imagick, $background );
		$this->darkenBackground( $imagick );

		$this->addText(
			$imagick,
			$textColor,
			$title
		);

		if ( $this->config->get( 'WikiSeoSocialImageShowLogo' ) ) {
			$this->drawIcon( $imagick, $textColor );
		}

		$imagick->setImageFormat( 'jpg' );

		return $imagick;
	}

	/**
	 * @param string $message
	 * @param int $status
	 * @return never-return
	 * @throws LocalizedHttpException
	 */
	private function makeError( string $message, int $status ): void {
		throw new LocalizedHttpException( new MessageValue( $message ), $status, [
			'error' => 'parameter-validation-failed',
			'failureCode' => $status,
			'failureData' => $status < 500 ? 'Bad Request' : 'Server Error',
		] );
	}

	/**
	 * Initialize the image object with the provided width, height, and background image/color
	 *
	 * @param mixed $background
	 * @return Imagick
	 * @throws ImagickException
	 */
	private function initImage( $background ): Imagick {
		$imagick = new Imagick();

		if ( get_class( $background ) !== ImagickPixel::class ) {
			$background = 'none';
		}

		$imagick->newImage(
			$this->config->get( 'WikiSeoSocialImageWidth' ),
			$this->config->get( 'WikiSeoSocialImageHeight' ),
			$background
		);

		return $imagick;
	}

	/**
	 * If we are working with an image, resize the background image to the dimensions of the image
	 *
	 * @param Imagick $imagick
	 * @param Imagick|ImagickPixel $background
	 * @return void
	 * @throws ImagickException
	 */
	private function addBackgroundImage( Imagick $imagick, $background ): void {
		if ( get_class( $background ) !== Imagick::class ) {
			return;
		}

		$background->resizeImage(
			$this->config->get( 'WikiSeoSocialImageWidth' ),
			0,
			Imagick::FILTER_CATROM,
			1
		);

		if ( $background->getImageHeight() < $this->config->get( 'WikiSeoSocialImageHeight' ) ) {
			$background->resizeImage(
				0,
				$this->config->get( 'WikiSeoSocialImageHeight' ),
				Imagick::FILTER_CATROM,
				1
			);
		}

		$imagick->compositeImage( $background, Imagick::COMPOSITE_OVER, 0, 0 );
	}

	/**
	 * Add a dark-to-light gradient at the bottom, ensures readability
	 *
	 * @param Imagick $imagick
	 * @return void
	 * @throws ImagickException
	 */
	private function darkenBackground( Imagick $imagick ): void {
		$overlay = new Imagick();
		$overlay->newPseudoImage(
			$this->config->get( 'WikiSeoSocialImageWidth' ),
			$this->config->get( 'WikiSeoSocialImageHeight' ) / 2,
			'gradient:rgba(0, 0, 0, 0)-rgba(0, 0, 0, 1)'
		);

		$imagick->compositeImage(
			$overlay,
			Imagick::COMPOSITE_OVER,
			0,
			$this->config->get( 'WikiSeoSocialImageHeight' ) / 2
		);
	}

	/**
	 * Write the title, namespace, last modified date, and contributors to the image
	 *
	 * @param Imagick $imagick
	 * @param ImagickPixel $textColor
	 * @param Title $title
	 * @return void
	 * @throws ImagickDrawException
	 * @throws ImagickException
	 */
	private function addText( Imagick $imagick, ImagickPixel $textColor, Title $title ): void {
		$width = $this->config->get( 'WikiSeoSocialImageWidth' );
		$height = $this->config->get( 'WikiSeoSocialImageHeight' );

		$titleSize = 56;
		$subtitleSize = 32;
		$namespaceSize = 32;
		$leftMargin = 62;
		$bottomMargin = 62;
		$ySpacing = 16;

		$materialIcons = new ImagickDraw();
		$materialIcons->setFillColor( $textColor );
		$materialIcons->setFont( 'extensions/WikiSEO/assets/fonts/MaterialIcons/MaterialIconsOutlined-Regular.otf' );
		$materialIcons->setFontSize( 32 );
		$materialIcons->setFillColor( new ImagickPixel( '#dddddd' ) );

		$roboto = new ImagickDraw();
		$roboto->setFillColor( $textColor );
		$roboto->setFillColor( new ImagickPixel( '#dddddd' ) );

		$rev = MediaWikiServices::getInstance()->getRevisionLookup()->getKnownCurrentRevision( $title );

		// Last Modified
		if ( is_object( $rev ) ) {
			$imagick->annotateImage(
				$materialIcons,
				$leftMargin,
				$height - ( $bottomMargin - 6 ),
				0,
				$this->utf8( 0xe923 )
			);

			$leftMargin += $imagick->queryFontMetrics( $materialIcons, $this->utf8( 0xe923 ) )['textWidth'] + 8;

			$roboto->setFont( 'extensions/WikiSEO/assets/fonts/Roboto/Roboto-Medium.ttf' );
			$roboto->setFontSize( $subtitleSize );

			$timestamp = MediaWikiServices::getInstance()->getContentLanguage()->date( $rev->getTimestamp() );

			$imagick->annotateImage(
				$roboto,
				$leftMargin,
				$height - $bottomMargin,
				0,
				$timestamp
			);
		}

		// Contributors
		$contributors = $this->getContributors( $title );

		if ( !empty( $contributors ) ) {
			$leftMargin += $imagick->queryFontMetrics( $roboto, $timestamp ?? '' )['textWidth'] + 40;
			$imagick->annotateImage(
				$materialIcons,
				$leftMargin,
				$height - ( $bottomMargin - 6 ),
				0,
				$this->utf8( 0xe7fd )
			);

			$leftMargin += $imagick->queryFontMetrics( $materialIcons, $this->utf8( 0xe7fd ) )['textWidth'] + 8;

			$contribsShow = array_splice( $contributors, 0, 2 );

			$text = implode( ', ', $contribsShow );

			if ( $imagick->queryFontMetrics( $roboto, $text )['textWidth'] > $width - 240 - $leftMargin ) {
				$text = $contribsShow[0];
				$contributors[] = $contribsShow[1];
			}

			if ( count( $contributors ) > 0 ) {
				$text .= ' +' . count( $contributors );
			}

			$roboto->setFontSize( $subtitleSize );
			$imagick->annotateImage(
				$roboto,
				$leftMargin,
				$height - $bottomMargin,
				0,
				$text
			);
		}

		// Page Title
		$leftMargin = 62;
		$roboto->setFont( 'extensions/WikiSEO/assets/fonts/Roboto/Roboto-Medium.ttf' );
		$roboto->setFontSize( $titleSize );
		$roboto->setFillColor( $textColor );

		[ $lines, $lineHeight ] = $this->wordWrapAnnotation(
			$imagick,
			$roboto,
			$title->getText(),
			$width - 240
		);

		$lines = array_reverse( $lines );

		foreach ( $lines as $i => $iValue ) {
			$imagick->annotateImage(
				$roboto,
				$leftMargin, $height - $bottomMargin - $subtitleSize - $ySpacing - ( $i * $lineHeight ), 0, $iValue );
		}

		// Namespace
		$roboto->setFont( 'extensions/WikiSEO/assets/fonts/Roboto/Roboto-Light.ttf' );
		$roboto->setFontSize( $namespaceSize );

		$yOffset = ( ( count( $lines ) - 1 ) * $titleSize ) + $ySpacing;

		$imagick->annotateImage(
			$roboto,
			$leftMargin,
			$height - $bottomMargin - $subtitleSize - $titleSize - $ySpacing - $yOffset + 4,
			0,
			$title->getNsText()
		);
	}

	/**
	 * Wraps overly long lines
	 * https://stackoverflow.com/a/28288589
	 *
	 * @param Imagick $image
	 * @param ImagickDraw $draw
	 * @param string $text
	 * @param int $maxWidth
	 * @return array
	 * @throws ImagickException
	 */
	private function wordWrapAnnotation( Imagick $image, ImagickDraw $draw, string $text, int $maxWidth ): array {
		$words = explode( " ", $text );
		$lines = [];
		$i = 0;
		$lineHeight = 0;
		while ( $i < count( $words ) ) {
			$currentLine = $words[$i];
			if ( $i + 1 >= count( $words ) ) {
				$lines[] = $currentLine;
				break;
			}

			// Check to see if we can add another word to this line
			$metrics = $image->queryFontMetrics( $draw, $currentLine . ' ' . $words[$i + 1] );
			while ( $metrics['textWidth'] <= $maxWidth ) {
				// If so, do it and keep doing it!
				$currentLine .= ' ' . $words[++$i];
				if ( $i + 1 >= count( $words ) ) {
					break;
				}
				$metrics = $image->queryFontMetrics( $draw, $currentLine . ' ' . $words[$i + 1] );
			}
			// We can't add the next word to this line, so loop to the next line
			$lines[] = $currentLine;
			$i++;
			// Finally, update line height
			if ( $metrics['textHeight'] > $lineHeight ) {
				$lineHeight = $metrics['textHeight'];
			}
		}
		return [ $lines, $lineHeight ];
	}

	/**
	 * Adds a logo to the image
	 *
	 * @param Imagick $imagick
	 * @param ImagickPixel $textColor
	 * @return void
	 * @throws ImagickDrawException
	 * @throws ImagickException
	 */
	private function drawIcon( Imagick $imagick, ImagickPixel $textColor ): void {
		$icon = $this->config->get( 'WikiSeoSocialImageIcon' );

		if ( $icon === null ) {
			return;
		}

		$icon = MediaWikiServices::getInstance()->getTitleFactory()->makeTitle( NS_FILE, $icon );
		$icon = MediaWikiServices::getInstance()->getRepoGroup()->getLocalRepo()->findFile( $icon );

		if ( !$icon->exists() ) {
			return;
		}

		$circle = new Imagick();
		$iconSize = 80;
		$circle->newImage( $iconSize, $iconSize, 'none' );
		$circle->setImageFormat( 'png' );
		$circle->setImageMatte( true );

		$draw = new ImagickDraw();
		$draw->setfillcolor( $textColor );
		$draw->circle( $iconSize / 2, $iconSize / 2, $iconSize / 2, $iconSize - 2 );
		$circle->drawimage( $draw );

		$iconIm = new Imagick();
		$iconIm->readImage( $icon->getLocalRefPath() );
		$iconIm->setImageMatte( true );
		if ( $iconIm->getImageHeight() > $iconIm->getImageWidth() ) {
			$iconIm->resizeImage( 0, $iconSize, Imagick::FILTER_LANCZOS, 1 );
		} else {
			$iconIm->resizeImage( $iconSize, 0, Imagick::FILTER_LANCZOS, 1 );
		}

		$iconIm->compositeimage( $circle, Imagick::COMPOSITE_DSTIN, -1, -1 );

		$x = $this->config->get( 'WikiSeoSocialImageWidth' ) - $iconIm->getImageWidth() - 80;
		$y = $this->config->get( 'WikiSeoSocialImageHeight' ) - $iconIm->getImageHeight() - 48;

		$imagick->compositeImage( $iconIm, Imagick::COMPOSITE_OVER, $x, $y );
	}

	/**
	 * Try to retrieve the contributors for a given title
	 *
	 * @param string $title
	 * @return array
	 */
	private function getContributors( string $title ): array {
		try {
			$req = new ApiMain( new FauxRequest( [
				'action' => 'query',
				'prop' => 'contributors',
				'titles' => $title,
				'pcexcludegroup' => 'bot',
				'pclimit' => '10',
				'format' => 'json'
			] ) );
		} catch ( MWException $e ) {
			return [];
		}

		$req->execute();

		$data = $req->getResult()->getResultData();

		if ( ( $data['batchcomplete'] ?? false ) === false ||
			!isset( $data['query']['pages'] ) ||
			isset( $data['query']['pages'][-1] ) ) {
			return [];
		}

		$contributors = array_shift( $data['query']['pages'] )['contributors'];

		if ( $contributors === null ) {
			return [];
		}

		return array_map( static function ( array $contributor ) {
			return $contributor['name'];
		}, array_filter( $contributors, static function ( $contributor ) { return is_array( $contributor );
		} ) );
	}

	/**
	 * Convert a code point to UTF8 - Needed to correctly handle the material icon font
	 *
	 * @param mixed $c
	 * @return string
	 * @throws Exception
	 */
	private function utf8( $c ) {
		if ( $c <= 0x7F ) {
			return chr( $c );
		}
		if ( $c <= 0x7FF ) {
			return chr( ( $c >> 6 ) + 192 ) . chr( ( $c & 63 ) + 128 );
		}
		if ( $c <= 0xFFFF ) {
			return chr( ( $c >> 12 ) + 224 ) .
				chr( ( ( $c >> 6 ) & 63 ) + 128 ) .
				chr( ( $c & 63 ) + 128 );
		}
		if ( $c <= 0x1FFFFF ) {
			return chr( ( $c >> 18 ) + 240 ) .
				chr( ( ( $c >> 12 ) & 63 ) + 128 ) .
				chr( ( ( $c >> 6 ) & 63 ) + 128 ) .
				chr( ( $c & 63 ) + 128 );
		} else {
			throw new Exception( 'Could not represent in UTF-8: ' . $c );
		}
	}
}
