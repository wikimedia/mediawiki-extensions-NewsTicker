<?php

class NewsTicker {

	/**
	 * Add the NewsTicker resource module
	 */
	static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
		$out->addModuleStyles( 'ext.NewsTicker.styles' );
		$out->addModuleScripts( 'ext.NewsTicker.scripts' );
	}

	/**
	 * Render the news ticker itself
	 */
	static function onSkinAfterContent( &$data, $skin )  {
		// Get all the data
		$newsData = [];
		$newsTitle = Title::newFromText( "News.json", NS_MEDIAWIKI );
		$newsPage = new WikiPage( $newsTitle );
		$newsContent = $newsPage->getContent();
		if ( $newsContent ) {
			$newsData = $newsContent->getJsonData();
		}

		// Merge the default values with the data
		$defaults = [
			'news' => [],
			'width' => '',
			'height' => '',
		];
		$newsData = array_merge( $defaults, $newsData );

		// Get the news for this page
		$pages = $newsData['pages'] ?? 0;
		$thisTitle = $skin->getTitle();
		$thisText = $thisTitle->getFullText();
		for ( $i = 0; $i <= $pages; $i++ ) {

			$title = $newsData["title$i"] ?? null;
			if ( $title and $title === $thisText ) {

				$news = $newsData["news$i"] ?? $newsData['news'];
				$width = $newsData["width$i"] ?? $newsData['width'];
				$heigth = $newsData["height$i"] ?? $newsData['height'];

				$data = Html::openElement( 'div', [
					'id' => 'news-ticker',
					'style' => "width: $width; height: $heigth;"
				]);

				// Prepare the parser
				$parserOptions = new ParserOptions;
				$parser = new Parser;

				// Select random news
				$current = rand( 0, count( $news ) - 1 );
				foreach ( $news as $i => $value ) {
					$class = $current === $i ? 'news initial current' : 'news';
					$parserOutput = $parser->parse( $value, $thisTitle, $parserOptions );
					$value = $parserOutput->getText();
					$data .= Html::rawElement( 'div', [ 'class' => $class ], $value );
				}

				$data .= Html::closeElement( 'div' );
			}
		}
	}
}