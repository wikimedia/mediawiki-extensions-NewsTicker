<?php

use MediaWiki\MediaWikiServices;

class NewsTicker {

	/**
	 * Add the NewsTicker resource module
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 */
	public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
		$out->addModuleStyles( 'ext.NewsTicker.styles' );
		$out->addModules( 'ext.NewsTicker.scripts' );
	}

	/**
	 * Register NEWSTICKER as a variable
	 *
	 * @param array &$customVariableIds
	 * @return bool
	 */
	public static function onMagicWordwgVariableIDs( &$customVariableIds ) {
		$customVariableIds[] = 'NEWSTICKER';
		return true;
	}

	/**
	 * Render the news ticker itself
	 *
	 * @param Parser $parser
	 * @param array &$cache
	 * @param string $magicWordId
	 * @param string &$return
	 * @return bool
	 */
	public static function onParserGetVariableValueSwitch(
		Parser $parser,
		&$cache,
		$magicWordId,
		&$return
	) {
		if ( $magicWordId !== 'NEWSTICKER' ) {
			return true;
		}
		// Get all the relevant data
		$newsData = [];
		$newsTitle = Title::newFromText( "News.json", NS_MEDIAWIKI );
		if ( method_exists( MediaWikiServices::class, 'getWikiPageFactory' ) ) {
			// MW 1.36+
			$newsPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $newsTitle );
		} else {
			$newsPage = new WikiPage( $newsTitle );
		}
		$newsContent = $newsPage->getContent();
		if ( $newsContent ) {
			$newsData = $newsContent->getJsonData();
		}

		// Merge the default values with the data
		$defaults = [
			'news' => [],
			'class' => '',
			'style' => '',
		];
		$newsData = array_merge( $defaults, $newsData );

		// Get the news for this page
		$pages = $newsData['pages'] ?? 0;
		$thisTitle = $parser->getTitle();
		if ( method_exists( $parser, 'getUserIdentity' ) ) {
			// MW 1.36+
			$parserUser = MediaWiki\MediaWikiServices::getInstance()
				->getUserFactory()->newFromUserIdentity( $parser->getUserIdentity() );
		} else {
			$parserUser = $parser->getUser();
		}
		$thisText = $thisTitle->getFullText();
		for ( $i = 0; $i <= $pages; $i++ ) {

			$title = $newsData["title$i"] ?? null;
			if ( $title && $title === $thisText ) {

				$news = $newsData["news$i"] ?? $newsData['news'];
				$class = $newsData["class$i"] ?? $newsData['class'];
				$style = $newsData["style$i"] ?? $newsData['style'];

				$return .= Html::openElement( 'div', [
					'class' => "news-ticker-wrapper",
				] );

				$return .= Html::openElement( 'div', [
					'class' => "news-ticker $class",
					'style' => $style
				] );

				// Prepare the parser
				$parserOptions = new ParserOptions( $parserUser );
				$parser = $parser->getFreshParser();

				// Select random news
				$current = rand( 0, count( $news ) - 1 );
				foreach ( $news as $i => $value ) {
					$class = $current === $i ? 'news initial current' : 'news';
					$parserOutput = $parser->parse( $value, $thisTitle, $parserOptions );
					$value = $parserOutput->getText();
					$return .= Html::rawElement( 'div', [ 'class' => $class ], $value );
				}

				$return .= Html::closeElement( 'div' );
				$return .= Html::closeElement( 'div' );
			}
		}
		$cache[$magicWordId] = $return;
		return true;
	}
}
