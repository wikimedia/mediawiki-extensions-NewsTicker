( function ( $, mw ) {
	var NewsTicker = {

		init: function () {
			window.setInterval( NewsTicker.next, 3000 );
		},

		next: function () {
			var news = $( '#news-ticker .news' );
			var current = $( '#news-ticker .current' );
			var index = current.index();
			var length = news.length - 1;
			var next = index === length ? 0 : index + 1;
			current.removeClass( 'current' ).slideToggle();
			news.eq( next ).addClass( 'current' ).slideToggle();
		}
	};

	$( NewsTicker.init );
}( jQuery, mediaWiki ) );