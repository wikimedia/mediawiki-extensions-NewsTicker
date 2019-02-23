<?php

class NewsTicker {

	/**
	 * Add the NewsTicker resource module
	 */
	static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
		$out->addModuleStyles( 'ext.NewsTicker.styles' );
		$out->addModuleScripts( 'ext.NewsTicker.scripts' );
		return true;
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

				$news = $newsData["news$i"] ?? $newsData["news"];
				$width = $newsData["width$i"] ?? $newsData["width"];
				$heigth = $newsData["height$i"] ?? $newsData["height"];
	
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
	/**
	 * Render the news ticker itself
	 */
	static function onSkinAfterContent2( &$data, $skin )  {
		$newspages = Title::newFromText( 'newspages', NS_MEDIAWIKI );
		if ( $newspages->exists() ) {
			$newspages = wfMessage( 'newspages' )->plain();
			$newspages = explode( "\n", $newspages );
			$newspages = array_values( array_filter( $newspages ) ); // Normalize the array
			$title = $skin->getTitle();
			$text = $title->getText();

			if ( in_array( $text, $newspages ) ) {
				global $wgNewsTickerWidth, $wgNewsTickerHeight;
				$data = Html::openElement( 'div', [
					'id' => 'news-ticker',
					'style' => "width: $wgNewsTickerWidth; height: $wgNewsTickerHeight;"
				]);
				$news = Title::newFromText( 'news', NS_MEDIAWIKI );

				if ( $news->exists() ) {
					$news = wfMessage( 'news' )->plain();
					$news = explode( "\n", $news );
					$news = array_values( array_filter( $news ) ); // Normalize the array

					// Prepare necessary objects to init the parser and parse the news
					$parserOptions = new ParserOptions;
					$parser = new Parser;
					$title = $skin->getTitle();
					
					$current = rand( 0, count( $news ) - 1 ); // Select a random news
					foreach ( $news as $i => $n ) {
						$class = $current === $i ? 'news initial current' : 'news';
						$parserOutput = $parser->parse( $n, $title, $parserOptions );
						$n = $parserOutput->getText();
						$data .= Html::rawElement( 'div', [ 'class' => $class ], $n );
					}
				}
				$data .= Html::closeElement( 'div' );
			}
		}
		return true;
	}
}
