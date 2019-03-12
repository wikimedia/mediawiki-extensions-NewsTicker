( function ( $, mw ) {

	var NewsTicker = {

		init: function () {
			window.setInterval( NewsTicker.next, 3000 );
		},

		next: function () {
			$( '.news-ticker' ).each( function () {
				var news = $( '.news', this );
				var current = $( '.current', this );
				var index = current.index();
				var length = news.length - 1;
				var next = index === length ? 0 : index + 1;
				current.removeClass( 'current' ).slideToggle();
				news.eq( next ).addClass( 'current' ).slideToggle();
			});
		}
	};

	$( NewsTicker.init );

}( jQuery, mediaWiki ) );