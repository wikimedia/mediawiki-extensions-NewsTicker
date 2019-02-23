<?php

class SpecialNewsTicker extends FormSpecialPage {

	/**
	 * Configuration options
	 */
	static $options;

	/**
	 * Default values for the news tickers
	 */
	static $defaults = [
		'news' => [],
		'width' => '',
		'height' => '',
		'pages' => 1
	];

	public function __construct( $name = '', $restriction = '', $listed = true ) {
		parent::__construct( 'NewsTicker', 'newsticker' );
	}

	public function execute( $parameter ) {
		$user = $this->getUser();
		$this->checkExecutePermissions( $user );
		parent::execute( $parameter );
	}

	public function onSubmit( array $data ) {
		$data['news'] = self::validateNews( $data['news'] );
		$data['pages'] = self::validatePages( $data['pages'] );

		$pages = $data['pages'];
		for ( $i = 1; $i <= $pages; $i++ ) {
			$data["news$i"] = array_key_exists( "news$i", $data ) ? self::validateNews( $data["news$i"] ) : [];
		}

		// Save the values
		$data = array_filter( $data );
		$json = json_encode( $data );
		$title = Title::newFromText( "News.json", NS_MEDIAWIKI );
		$page = new WikiPage( $title );
		$content = ContentHandler::makeContent( $json, $title );
		$summary = "";
		$status = $page->doEditContent( $content, $summary );

		// Reload the page or the form won't update correctly
		header( "Refresh:0" );
		return false;
	}

	/**
	 * Return the HTMLForm descriptor
	 */
	protected function getFormFields() {
		$options = $this->getOptions();
		$fields = [
			/**
			 * General
			 */
			'news' => [
				'section' => 'general',
				'class' => 'HTMLTextareaField',
				'rows' => count( $options['news'] ),
				'label' => wfMessage( 'newsticker-news' ),
				'help' => wfMessage( 'newsticker-default-news-help' )->text(),
				'default' => implode( "\n", $this->getOption( 'news' ) ),
				//'placeholder' => implode( "\n", $this->getOption( 'news' ) ),
			],
			'width' => [
				'section' => 'general',
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'newsticker-width' ),
				'help' => wfMessage( 'newsticker-default-width-help' )->text(),
				'default' => $this->getOption( 'width' ),
				//'placeholder' => $this->getOption( 'width' ),
			],
			'height' => [
				'section' => 'general',
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'newsticker-height' ),
				'help' => wfMessage( 'newsticker-default-height-help' )->text(),
				'default' => $this->getOption( 'height' ),
				//'placeholder' => $this->getOption( 'height' ),
			],
			'pages' => [
				'section' => 'general',
				'class' => 'HTMLIntField',
				'label' => wfMessage( 'newsticker-pages' ),
				'help' => wfMessage( 'newsticker-pages-help' )->text(),
				'default' => $this->getOption( 'pages' ),
				//'placeholder' => $this->getOption( 'pages' ),
			]
		];

		$pages = $this->getOption( 'pages' );
		for ( $i = 1; $i <= $pages; $i++ ) {
			$fields[ "title$i" ] = [
				'section' => "page$i",
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'newsticker-title' ),
				'help' => wfMessage( 'newsticker-title-help' )->text(),
				'default' => $this->getOption( "title$i" ),
			];
			$fields[ "news$i" ] = [
				'section' => "page$i",
				'class' => 'HTMLTextareaField',
				'rows' => count( $this->getOption( 'news$i', $this->getOption( "news" ) ) ),
				'label' => wfMessage( 'newsticker-news' ),
				'help' => wfMessage( 'newsticker-news-help' )->text(),
				'default' => implode( "\n", $this->getOption( "news$i", [] ) ),
				'placeholder' => implode( "\n", $this->getOption( "news" ) ),
			];
			$fields[ "width$i" ] = [
				'section' => "page$i",
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'newsticker-width' ),
				'help' => wfMessage( 'newsticker-width-help' )->text(),
				'default' => $this->getOption( "width$i" ),
				'placeholder' => $this->getOption( 'width' ),
			];
			$fields[ "height$i" ] = [
				'section' => "page$i",
				'class' => 'HTMLTextField',
				'label' => wfMessage( 'newsticker-height' ),
				'help' => wfMessage( 'newsticker-height-help' )->text(),
				'default' => $this->getOption( "height$i" ),
				'placeholder' => $this->getOption( 'height' ),
			];
		}
		return $fields;
	}

	/**
	 * Validate links
	 */
	public function validateLinks( string $pagenames ) {
		$links = [];
		$pagenames = explode( "\n", $pagenames );
		foreach ( $pagenames as $pagename ) {
			$title = Title::newFromText( $pagename );
			if ( $title ) {
				$links[] = $pagename;
			}
		}
		return $links;
	}

	/**
	 * Validate news
	 */
	public function validateNews( string $string ) {
		$news = explode( "\n", $string );
		$news = array_filter( $news );
		return $news;
	}

	/**
	 * Validate pages
	 */
	public function validatePages( int $int ) {
		return $int;
	}

	/**
	 * Get the configuration options
	 *
	 * Only get them once, store them in a static variable and reuse them on subsequent calls
	 */
	static function getOptions() {
		if ( self::$options ) {
			return self::$options;
		}
		$title = Title::newFromText( "News.json", NS_MEDIAWIKI );
		$page = new WikiPage( $title );
		$content = $page->getContent();
		if ( $content ) {
			$data = $content->getJsonData();
			$options = array_merge( self::$defaults, $data );
		} else {
			$options = self::$defaults;
		}
		return $options;
	}

	/**
	 * Get a configuration option
	 */
	static function getOption( $key, $default = '' ) {
		$options = self::getOptions();
		if ( array_key_exists( $key, $options ) ) {
			return $options[ $key ];
		}
		return $default;
	}

	/**
	 * Return the display format for the HTMLForm
	 */
	protected function getDisplayFormat() {
		return 'ooui';
	}
}