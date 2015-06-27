( function ( $ ) {

	$( document ).ready( function () {

		// handle set-cookie button click
		$( document ).on( 'click', '.cn-set-cookie', function ( e ) {
			e.preventDefault();
			$( this ).setCookieNotice( $( this ).data( 'cookie-set' ) );
		});
		
		
		// handle on scroll
		if( cnArgs.onScroll == 'yes' ) {
			var cnHandleScroll = function () {				
				var win = $( this );
				if ( win.scrollTop() > parseInt( cnArgs.onScrollOffset ) ) {
					// If user scrolls at least 100 pixels
					win.setCookieNotice( 'accept' );
					win.off( 'scroll', cnHandleScroll ); //remove itself after cookie accept
				}
			};
			
			$(window).on('scroll', cnHandleScroll);
		}

		// display cookie notice
		if ( document.cookie.indexOf( 'cookie_notice_accepted' ) === -1 ) {
			if ( cnArgs.hideEffect === 'fade' ) {
				$( '#cookie-notice' ).fadeIn( 300 );
			} else if ( cnArgs.hideEffect === 'slide' ) {
				$( '#cookie-notice' ).slideDown( 300 );
			} else {
				$( '#cookie-notice' ).show();
			}
			
		} else {
			$( '#cookie-notice' ).remove();
		}

	});
	
	// Set Cookie Notice
	$.fn.setCookieNotice = function ( cookie_value ) {
		
		var cnTime = new Date(),
			cnLater = new Date();
		
		// set expiry time in seconds
		cnLater.setTime( parseInt( cnTime.getTime() ) + parseInt( cnArgs.cookieTime ) * 1000 );
		
		// set cookie
		cookie_value = cookie_value === 'accept' ? true : false;
		document.cookie = cnArgs.cookieName + '=' + cookie_value + ';expires=' + cnLater.toGMTString() + ';' + ( cnArgs.cookieDomain !== undefined && cnArgs.cookieDomain !== '' ? 'domain=' + cnArgs.cookieDomain + ';' : '' ) + ( cnArgs.cookiePath !== undefined && cnArgs.cookiePath !== '' ? 'path=' + cnArgs.cookiePath + ';' : '' );
		
		// trigger custom event
		$.event.trigger({
			type: "setCookieNotice",
			value: cookie_value,
			time: cnTime,
			expires: cnLater
		});
		
		// hide message container
		if ( cnArgs.hideEffect === 'fade' ) {
			$( '#cookie-notice' ).fadeOut( 300, function () {
				$( this ).remove();
			} );
		} else if ( cnArgs.hideEffect === 'slide' ) {
			$( '#cookie-notice' ).slideUp( 300, function () {
				$( this ).remove();
				} );
		} else {
			$( '#cookie-notice' ).remove();
		}
	};

} )( jQuery );
