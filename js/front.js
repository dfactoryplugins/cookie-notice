( function( window, document, undefined ) {

	var cookieNotice = new function () {
		// cookie status
		this.cookiesAccepted = null;
		// notice container
		this.noticeContainer = null;

		// set cookie value
		this.setStatus = function ( cookieValue ) {
			var _this = this;
			
			// remove listening to scroll event
			if ( cnArgs.onScroll === 'yes' )
				window.removeEventListener( 'scroll', this.handleScroll );

			var date = new Date(),
				laterDate = new Date();

			// set expiry time in seconds
			laterDate.setTime( parseInt( date.getTime() ) + parseInt( cnArgs.cookieTime ) * 1000 );

			// set cookie type
			cookieValue = cookieValue === 'accept' ? 'true' : 'false';

			// set cookie
			document.cookie = cnArgs.cookieName + '=' + cookieValue + ';expires=' + laterDate.toUTCString() + ';' + ( cnArgs.cookieDomain !== '' ? 'domain=' + cnArgs.cookieDomain + ';' : '' ) + ( cnArgs.cookiePath !== '' ? 'path=' + cnArgs.cookiePath + ';' : '' ) + ( cnArgs.secure === '1' ? 'secure;' : '' );

			// update global status
			this.cookiesAccepted = cookieValue === 'true' ? true : ( cookieValue === 'false' ? false : null );

			// trigger custom event
			var event = new CustomEvent(
				'setCookieNotice',
				{
					detail: {
						value: cookieValue,
						time: date,
						expires: laterDate,
						data: cnArgs
					},
					bubbles: true,
					cancelable: true
				}
			);

			document.dispatchEvent( event );

			this.setBodyClass( [ 'cookies-set', cookieValue === 'true' ? 'cookies-accepted' : 'cookies-refused' ] );

			this.hideCookieNotice();

			// show revoke notice if enabled
			if ( cnArgs.revoke_cookies_opt === 'automatic' ) {
				// show cookie notice after the revoke is hidden
				this.noticeContainer.addEventListener( 'animationend', function handler() {
					_this.showRevokeNotice();
					_this.noticeContainer.removeEventListener( 'animationend', handler );
				} );
				this.noticeContainer.addEventListener( 'webkitAnimationEnd', function handler() {
					_this.showRevokeNotice();
					_this.noticeContainer.removeEventListener( 'webkitAnimationEnd', handler );
				} );
			}

			// redirect?
			if ( cnArgs.redirection === '1' && ( ( cookieValue === 'true' && this.cookiesAccepted === null ) || ( cookieValue !== this.cookiesAccepted && this.cookiesAccepted !== null ) ) ) {
				var url = window.location.protocol + '//',
					hostname = window.location.host + '/' + window.location.pathname;

				// enabled cache?
				if ( cnArgs.cache === '1' ) {
					url = url + hostname.replace( '//', '/' ) + ( window.location.search === '' ? '?' : window.location.search + '&' ) + 'cn-reloaded=1' + window.location.hash;

					window.location.href = url;
				} else {
					url = url + hostname.replace( '//', '/' ) + window.location.search + window.location.hash;

					window.location.reload( true );
				}

				return;
			} else {
				// show revoke notice if enabled
				if ( cnArgs.revoke_cookies_opt === 'automatic' ) {
					// cnShowRevokeNotice();
				}
			}
		};

		// get cookie value
		this.getStatus = function ( bool ) {
			var value = "; " + document.cookie,
				parts = value.split( '; cookie_notice_accepted=' );

			if ( parts.length === 2 ) {
				var val = parts.pop().split( ';' ).shift();

				if ( bool )
					return val === 'true';
				else
					return val;
			} else
				return null;
		};

		// display cookie notice
		this.showCookieNotice = function() {
			var _this = this;
			
			// trigger custom event
			var event = new CustomEvent(
				'showCookieNotice',
				{
					detail: {
						data: cnArgs,
					},
					bubbles: true,
					cancelable: true
				}
			);

			document.dispatchEvent( event );

			this.noticeContainer.classList.remove( 'cookie-notice-hidden' );
			this.noticeContainer.classList.add( 'cn-animated', 'cookie-notice-visible' );

			// console.log( 'show' );

			// detect animation
			this.noticeContainer.addEventListener( 'animationend', function handler() {
				_this.noticeContainer.classList.remove( 'cn-animated' );
				_this.noticeContainer.removeEventListener( 'animationend', handler );
				// console.log( 'show end' );
			} ); 
			this.noticeContainer.addEventListener( 'webkitAnimationEnd', function handler() {
				_this.noticeContainer.classList.remove( 'cn-animated' );
				_this.noticeContainer.removeEventListener( 'webkitAnimationEnd', handler );
				// console.log( 'show end' );
			} ); 
		};

		// hide cookie notice
		this.hideCookieNotice = function () {
			var _this = this;
			
			// trigger custom event
			var event = new CustomEvent(
				'hideCookieNotice',
				{
					detail: {
						data: cnArgs,
					},
					bubbles: true,
					cancelable: true
				}
			);

			document.dispatchEvent( event );

			this.noticeContainer.classList.add( 'cn-animated' );
			this.noticeContainer.classList.remove( 'cookie-notice-visible' );

			// detect animation
			this.noticeContainer.addEventListener( 'animationend', function handler() {
				_this.noticeContainer.classList.remove( 'cn-animated' );
				_this.noticeContainer.classList.add( 'cookie-notice-hidden' );
				_this.noticeContainer.removeEventListener( 'animationend', handler );
			} ); 
			this.noticeContainer.addEventListener( 'webkitAnimationEnd', function handler() {
				_this.noticeContainer.classList.remove( 'cn-animated' );
				_this.noticeContainer.classList.add( 'cookie-notice-hidden' );
				_this.noticeContainer.removeEventListener( 'webkitAnimationEnd', handler );
			} );
		};

		// display revoke notice
		this.showRevokeNotice = function () {
			var _this = this;
			
			// trigger custom event
			var event = new CustomEvent(
				'showRevokeNotice',
				{
					detail: {
						data: cnArgs,
					},
					bubbles: true,
					cancelable: true
				}
			);

			document.dispatchEvent( event );

			this.noticeContainer.classList.remove( 'cookie-revoke-hidden' );
			this.noticeContainer.classList.add( 'cn-animated', 'cookie-revoke-visible' );

			// detect animation
			this.noticeContainer.addEventListener( 'animationend', function handler() {
				_this.noticeContainer.classList.remove( 'cn-animated' );
				_this.noticeContainer.removeEventListener( 'animationend', handler );
			} ); 
			this.noticeContainer.addEventListener( 'webkitAnimationEnd', function handler() {
				_this.noticeContainer.classList.remove( 'cn-animated' );
				_this.noticeContainer.removeEventListener( 'webkitAnimationEnd', handler );
			} );
		};

		// hide revoke notice
		this.hideRevokeNotice = function () {
			var _this = this;
			
			// trigger custom event
			var event = new CustomEvent(
				'hideRevokeNotice',
				{
					detail: {
						data: cnArgs,
					},
					bubbles: true,
					cancelable: true
				}
			);

			document.dispatchEvent( event );

			this.noticeContainer.classList.add( 'cn-animated' );
			this.noticeContainer.classList.remove( 'cookie-revoke-visible'  );

			// detect animation
			this.noticeContainer.addEventListener( 'animationend', function handler() {
				_this.noticeContainer.classList.remove( 'cn-animated' );
				_this.noticeContainer.classList.add( 'cookie-revoke-hidden' );
				_this.noticeContainer.removeEventListener( 'animationend', handler );
			} ); 
			this.noticeContainer.addEventListener( 'webkitAnimationEnd', function handler() {
				_this.noticeContainer.classList.remove( 'cn-animated' );
				_this.noticeContainer.classList.add( 'cookie-revoke-hidden' );
				_this.noticeContainer.removeEventListener( 'webkitAnimationEnd', handler );
			} ); 
		};

		// change body classes
		this.setBodyClass = function ( classes ) {
			// remove body classes
			document.body.classList.remove( 'cookies-revoke', 'cookies-accepted', 'cookies-refused', 'cookies-set', 'cookies-not-set' );

			// add body classes
			for ( var i = 0; i < classes.length; i++ ) {
				document.body.classList.add( classes[i] );
			}
		};

		// handle mouse scrolling
		this.handleScroll = function () {
			var scrollTop = window.pageYOffset || (document.documentElement || document.body.parentNode || document.body).scrollTop

			if ( scrollTop > parseInt( cnArgs.onScrollOffset ) ) {
				// accept cookie
				this.setStatus( 'accept' );

				// console.log( 'scroll end' );
			} else {
				// console.log( 'scrolling' );
			}
		};
		
		// cross browser compatible closest function
		this.getClosest = function ( elem, selector ) {

			// element.matches() polyfill
			if ( ! Element.prototype.matches ) {
				Element.prototype.matches =
					Element.prototype.matchesSelector ||
					Element.prototype.mozMatchesSelector ||
					Element.prototype.msMatchesSelector ||
					Element.prototype.oMatchesSelector ||
					Element.prototype.webkitMatchesSelector ||
					function ( s ) {
						var matches = ( this.document || this.ownerDocument ).querySelectorAll( s ),
							i = matches.length;
						while ( --i >= 0 && matches.item( i ) !== this ) {
						}
						return i > -1;
					};
			}

			// get the closest matching element
			for ( ; elem && elem !== document; elem = elem.parentNode ) {
				if ( elem.matches( selector ) )
					return elem;
			}
			return null;

		};

		// initialize
		this.init = function () {
			var _this = this;

			this.cookiesAccepted = this.getStatus( true );
			this.noticeContainer = document.getElementById( 'cookie-notice' );

			var cookieButtons = document.getElementsByClassName( 'cn-set-cookie' ),
				revokeButtons = document.getElementsByClassName( 'cn-revoke-cookie' );

			// add effect class
			this.noticeContainer.classList.add( 'cn-effect-' + cnArgs.hideEffect );

			/*
			// add refuse class
			this.noticeContainer.classList.add( cnArgs.refuse === 'yes' ? 'cn-refuse-active' : 'cn-refuse-inactive' );

			// add revoke class
			if ( cnArgs.revoke_cookies === '1' ) {
				this.noticeContainer.classList.add( 'cn-revoke-active' );

				// add revoke type class (manual or automatic)
				this.noticeContainer.classList.add( 'cn-revoke-' + cnArgs.revoke_cookies_opt );
			} else {
				this.noticeContainer.classList.add( 'cn-revoke-inactive' );
			}
			*/

			// check cookies status
			if ( this.cookiesAccepted === null ) {
				// handle on scroll
				if ( cnArgs.onScroll === 'yes' )
					window.addEventListener( 'scroll', function ( e ) {
						_this.handleScroll();
					} );
				
				// handle on click
				if ( cnArgs.onClick === 'yes' )
					window.addEventListener( 'click', function ( e ) {
						// e.preventDefault();
							
						var outerContainer = _this.getClosest( e.target, '#cookie-notice' );
						
						// accept notice if clicked element is not inside the container
						if ( outerContainer === null ) {
							_this.setStatus( 'accept' );
						}

					}, true );

				this.setBodyClass( [ 'cookies-not-set' ] );

				// show cookie notice
				this.showCookieNotice();
			} else {
				this.setBodyClass( [ 'cookies-set', this.cookiesAccepted === 'true' ? 'cookies-accepted' : 'cookies-refused' ] );

				// show revoke notice if enabled
				if ( cnArgs.revoke_cookies_opt === 'automatic' ) {
					this.showRevokeNotice();
				}
			}

			// handle cookie buttons click
			for ( var i = 0; i < cookieButtons.length; i++ ) {
				cookieButtons[i].addEventListener( 'click', function ( e ) {
					e.preventDefault();

					_this.setStatus( this.dataset.cookieSet );
				} );
			}

			// handle revoke buttons click
			for ( var i = 0; i < revokeButtons.length; i++ ) {
				revokeButtons[i].addEventListener( 'click', function ( e ) {
					e.preventDefault();

					// hide revoke notice
					if ( _this.noticeContainer.classList.contains( 'cookie-revoke-visible' ) ) {
						_this.hideRevokeNotice();

						// show cookie notice after the revoke is hidden
						_this.noticeContainer.addEventListener( 'animationend', function handler() {
							_this.showCookieNotice();
							_this.noticeContainer.removeEventListener( 'animationend', handler );
						} ); 
						_this.noticeContainer.addEventListener( 'webkitAnimationEnd', function handler() {
							_this.showCookieNotice();
							_this.noticeContainer.removeEventListener( 'webkitAnimationEnd', handler );
						} ); 
					// show cookie notice
					} else {
						_this.showCookieNotice();
					}
				} );
			}
		};
	}

	// initialie plugin
	window.onload = function() {
		cookieNotice.init();
	};

} )( window, document, undefined );