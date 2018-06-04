( function ( $ ) {

	// ready event
	$( document ).ready( function () {
		var notice = $( '#cookie-notice' ),
			cookie = $.fn.getCookieNotice();

		// handle set-cookie button click
		$( document ).on( 'click', '.cn-set-cookie', function ( e ) {
			e.preventDefault();

			$( this ).setCookieNotice( $( this ).data( 'cookie-set' ) );
		} );

		// handle revoke button click
		$( document ).on( 'click', '.cn-revoke-cookie', function ( e ) {
			e.preventDefault();

			if ( cnArgs.refuse === 'yes' ) {
				var revoke = $( this );

				if ( cnArgs.onScroll === 'yes' ) {
					// enable cookie acceptance by scrolling again
					$( window ).on( 'scroll', handleScroll );
				}

				if ( cnArgs.revoke_cookies === '1' ) {
					// clicked shortcode button?
					if ( revoke.hasClass( 'cn-revoke-inline' ) ) {
						var body = $( 'body' );

						// is cookie notice hidden?
						if ( ! ( body.hasClass( 'cookies-revoke' ) || body.hasClass( 'cookies-not-set' ) ) ) {
							// display automatic revoke button?
							if ( cnArgs.revoke_cookies_opt === 'automatic' ) {
								notice.showCookieNotice( 3 );
							} else {
								notice.showCookieNotice( 2 );
							}
						}
					} else {
						notice.showCookieNotice( 1 );
					}

					// update cookie value
					cookie = $.fn.getCookieNotice();

					// add body class
					$.fn.setCookieNoticeBodyClass( 'cookies-set cookies-revoke ' + ( cookie === 'true' ? 'cookies-accepted' : 'cookies-refused' ) );
				}
			}
		} );

		// cookie is not set
		if ( typeof cookie === 'undefined' ) {
			// handle on scroll
			if ( cnArgs.onScroll === 'yes' ) {
				$( window ).on( 'scroll', handleScroll );
			}

			notice.showCookieNotice( 0 );

			$.fn.setCookieNoticeBodyClass( 'cookies-not-set' );
		// active refuse button?
		} else if ( cnArgs.refuse === 'yes' ) {
			if ( cnArgs.revoke_cookies === '1' ) {
				if ( cnArgs.revoke_cookies_opt === 'automatic' ) {
					notice.hideCookieNotice( 1 );
				}

				$.fn.setCookieNoticeBodyClass( 'cookies-set ' + ( cookie === 'true' ? 'cookies-accepted' : 'cookies-refused' ) );
			}
		// remove cookie notice
		} else {
			// add body class
			$.fn.setCookieNoticeBodyClass( 'cookies-set ' + ( cookie === 'true' ? 'cookies-accepted' : 'cookies-refused' ) );
		}
	} );

	// handle mouse scrolling
	function handleScroll( event ) {
		var win = $( this );

		if ( win.scrollTop() > parseInt( cnArgs.onScrollOffset ) ) {
			// accept cookie
			win.setCookieNotice( 'accept' );

			// remove itself after cookie accept
			win.off( 'scroll', handleScroll );
		}
	};

	// set Cookie Notice
	$.fn.setCookieNotice = function ( cookie_value ) {
		if ( cnArgs.onScroll === 'yes' ) {
			$( window ).off( 'scroll', handleScroll );
		}

		var date = new Date(),
			later_date = new Date(),
			notice = $( '#cookie-notice' );

		// set expiry time in seconds
		later_date.setTime( parseInt( date.getTime() ) + parseInt( cnArgs.cookieTime ) * 1000 );

		// set cookie
		cookie_value = cookie_value === 'accept' ? 'true' : 'false';
		document.cookie = cnArgs.cookieName + '=' + cookie_value + ';expires=' + later_date.toUTCString() + ';' + ( cnArgs.cookieDomain !== undefined && cnArgs.cookieDomain !== '' ? 'domain=' + cnArgs.cookieDomain + ';' : '' ) + ( cnArgs.cookiePath !== undefined && cnArgs.cookiePath !== '' ? 'path=' + cnArgs.cookiePath + ';' : '' ) + ( cnArgs.secure === '1' ? 'secure;' : '' );

		// trigger custom event
		$.event.trigger( {
			type: 'setCookieNotice',
			value: cookie_value,
			time: date,
			expires: later_date
		} );

		// add body class
		$.fn.setCookieNoticeBodyClass( 'cookies-set ' + ( cookie_value === 'true' ? 'cookies-accepted' : 'cookies-refused' ) );

		if ( cnArgs.refuse === 'yes' && cnArgs.revoke_cookies === '1' && cnArgs.revoke_cookies_opt === 'automatic' ) {
			notice.hideCookieNotice( 2 );
		} else {
			notice.hideCookieNotice( 0 );
		}

		if ( cookie_value && cnArgs.redirection === '1' ) {
			var url = window.location.protocol + '//',
				hostname = window.location.host + '/' + window.location.pathname;

			if ( cnArgs.cache === '1' ) {
				url = url + hostname.replace( '//', '/' ) + ( window.location.search === '' ? '?' : window.location.search + '&' ) + 'cn-reloaded=1' + window.location.hash;

				window.location.href = url;
			} else {
				url = url + hostname.replace( '//', '/' ) + window.location.search + window.location.hash;

				window.location.reload( true );
			}

			return;
		}
	};

	// add class(es) to body
	$.fn.setCookieNoticeBodyClass = function( classes ) {
		$( 'body' ).removeClass( 'cookies-revoke cookies-accepted cookies-refused cookies-set cookies-not-set' ).addClass( classes );
	}

	// get cookie value
	$.fn.getCookieNotice = function () {
		var value = "; " + document.cookie,
			parts = value.split( '; cookie_notice_accepted=' );

		if ( parts.length === 2 )
			return parts.pop().split( ';' ).shift();
		else
			return;
	}

	// display cookie notice
	$.fn.showCookieNotice = function( type ) {
		// trigger custom event
		$.event.trigger( {
			type: 'showCookieNotice',
			value: type,
			data: cnArgs
		} );

		switch ( type ) {
			case 0:
				if ( cnArgs.hideEffect === 'fade' ) {
					// show cookie notice
					this.fadeIn( 300 );
				} else if ( cnArgs.hideEffect === 'slide' ) {
					// show cookie notice
					this.slideDown( 300 );
				} else {
					// show cookie notice
					this.show();
				}
				break;

			case 1:
				var notice = this;

				if ( cnArgs.hideEffect === 'fade' ) {
					// hide revoke button
					this.find( '.cookie-notice-revoke-container' ).fadeOut( 300, function () {
						// show cookie notice
						notice.css( { 'visibility': 'visible', 'display': 'none' } ).fadeIn( 300 );
					} );
				} else if ( cnArgs.hideEffect === 'slide' ) {
					// hide revoke button
					this.find( '.cookie-notice-revoke-container' ).slideUp( 300, function () {
						// show cookie notice
						notice.css( { 'visibility': 'visible', 'display': 'none' } ).slideDown( 300 );
					} );
				} else {
					// hide revoke button
					this.find( '.cookie-notice-revoke-container' ).hide();

					// show cookie notice
					notice.css( { 'visibility': 'visible', 'display': 'block' } );
				}
				break;

			case 2:
				if ( cnArgs.hideEffect === 'fade' ) {
					// show cookie notice
					this.css( { 'visibility': 'visible', 'display': 'none' } ).fadeIn( 300 );
				} else if ( cnArgs.hideEffect === 'slide' ) {
					// show cookie notice
					this.css( { 'visibility': 'visible', 'display': 'none' } ).slideDown( 300 );
				} else {
					// show cookie notice
					this.css( { 'visibility': 'visible', 'display': 'block' } );
				}
				break;

			case 3:
				var notice = this;

				if ( cnArgs.hideEffect === 'fade' ) {
					// hide revoke button
					this.find( '.cookie-notice-revoke-container' ).fadeOut( 300, function () {
						// show cookie notice
						notice.css( { 'visibility': 'visible', 'display': 'none' } ).fadeIn( 300 );
					} );
				} else if ( cnArgs.hideEffect === 'slide' ) {
					// hide revoke button
					this.find( '.cookie-notice-revoke-container' ).slideUp( 300, function () {
						// show cookie notice
						notice.css( { 'visibility': 'visible', 'display': 'none' } ).slideDown( 300 );
					} );
				} else {
					// hide revoke button
					this.find( '.cookie-notice-revoke-container' ).hide();

					// show cookie notice
					notice.css( { 'visibility': 'visible', 'display': 'block' } );
				}
				break;
		}
	}

	// hide cookie notice
	$.fn.hideCookieNotice = function ( type ) {
		// trigger custom event
		$.event.trigger( {
			type: 'hideCookieNotice',
			value: type,
			data: cnArgs
		} );

		switch ( type ) {
			case 0:
				if ( cnArgs.hideEffect === 'fade' ) {
					this.fadeOut( 300 );
				} else if ( cnArgs.hideEffect === 'slide' ) {
					this.slideUp( 300 );
				} else {
					this.hide();
				}
				break;

			case 1:
				this.css( { 'visibility': 'hidden', 'display': 'block' } );

				if ( cnArgs.hideEffect === 'fade' ) {
					this.find( '.cookie-notice-revoke-container' ).fadeIn( 300 ).css( { 'visibility': 'visible', 'display': 'block' } );
				} else if ( cnArgs.hideEffect === 'slide' ) {
					this.find( '.cookie-notice-revoke-container' ).slideDown( 300 ).css( { 'visibility': 'visible', 'display': 'block' } );
				} else {
					this.find( '.cookie-notice-revoke-container' ).css( { 'visibility': 'visible', 'display': 'block' } );
				}
				break;

			case 2:
				var notice = this;

				if ( cnArgs.hideEffect === 'fade' ) {
					this.fadeOut( 300, function () {
						// hide cookie notice
						notice.css( { 'visibility': 'hidden', 'display': 'block' } );

						// show revoke button
						notice.find( '.cookie-notice-revoke-container' ).fadeIn( 300 ).css( { 'visibility': 'visible', 'display': 'block' } );
					} );
				} else if ( cnArgs.hideEffect === 'slide' ) {
					this.slideUp( 300, function () {
						// hide cookie notice
						notice.css( { 'visibility': 'hidden', 'display': 'block' } );

						// show revoke button
						notice.find( '.cookie-notice-revoke-container' ).slideDown( 300 ).css( { 'visibility': 'visible', 'display': 'block' } );
					} );
				} else {
					// hide cookie notice
					this.css( { 'visibility': 'hidden', 'display': 'block' } );

					// show revoke button
					notice.find( '.cookie-notice-revoke-container' ).css( { 'visibility': 'visible', 'display': 'block' } );
				}
				break;
		}
	}

} )( jQuery );