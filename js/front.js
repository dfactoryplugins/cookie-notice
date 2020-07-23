// CustomEvent polyfil for IE support
( function () {

	if ( typeof window.CustomEvent === "function" )
		return false;

	function CustomEvent( event, params ) {
		params = params || { bubbles: false, cancelable: false, detail: undefined };

		var evt = document.createEvent( 'CustomEvent' );

		evt.initCustomEvent( event, params.bubbles, params.cancelable, params.detail );

		return evt;
	}

	CustomEvent.prototype = window.Event.prototype;

	window.CustomEvent = CustomEvent;
} )();

// ClassList polyfil for IE/Safari support
( function () {
	var regExp = function ( name ) {
		return new RegExp( '(^| )' + name + '( |$)' );
	};

	var forEach = function ( list, fn, scope ) {
		for ( var i = 0; i < list.length; i++ ) {
			fn.call( scope, list[i] );
		}
	};

	function ClassList( element ) {
		this.element = element;
	}

	ClassList.prototype = {
		add: function () {
			forEach( arguments, function ( name ) {
				if ( !this.contains( name ) ) {
					this.element.className += this.element.className.length > 0 ? ' ' + name : name;
				}
			}, this );
		},
		remove: function () {
			forEach( arguments, function ( name ) {
				this.element.className =
					this.element.className.replace( regExp( name ), '' );
			}, this );
		},
		toggle: function ( name ) {
			return this.contains( name )
				? ( this.remove( name ), false ) : ( this.add( name ), true );
		},
		contains: function ( name ) {
			return regExp( name ).test( this.element.className );
		},
		// bonus..
		replace: function ( oldName, newName ) {
			this.remove( oldName ), this.add( newName );
		}
	};

	// IE8/9, Safari
	if ( !( 'classList' in Element.prototype ) ) {
		Object.defineProperty( Element.prototype, 'classList', {
			get: function () {
				return new ClassList( this );
			}
		} );
	}

	if ( window.DOMTokenList && DOMTokenList.prototype.replace == null )
		DOMTokenList.prototype.replace = ClassList.prototype.replace;
} )();

// cookieNotice
( function ( window, document, undefined ) {

	var cookieNotice = new function () {
		// cookie status
		this.cookiesAccepted = null;

		// notice container
		this.noticeContainer = null;

		// set cookie value
		this.setStatus = function ( cookieValue ) {
			var _this = this;

			// remove listening to scroll event
			if ( cnArgs.onScroll === '1' )
				window.removeEventListener( 'scroll', this.handleScroll );

			var date = new Date(),
				laterDate = new Date();

			// set cookie type and expiry time in seconds
			if ( cookieValue === 'accept' ) {
				cookieValue = 'true';
				laterDate.setTime( parseInt( date.getTime() ) + parseInt( cnArgs.cookieTime ) * 1000 );
			} else {
				cookieValue = 'false';
				laterDate.setTime( parseInt( date.getTime() ) + parseInt( cnArgs.cookieTimeRejected ) * 1000 );
			}

			// set cookie
			document.cookie = cnArgs.cookieName + '=' + cookieValue + ';expires=' + laterDate.toUTCString() + ';' + ( !!cnArgs.cookieDomain ? 'domain=' + cnArgs.cookieDomain + ';' : '' ) + ( !!cnArgs.cookiePath ? 'path=' + cnArgs.cookiePath + ';' : '' ) + ( cnArgs.secure === '1' ? 'secure;' : '' );

			// update global status
			this.cookiesAccepted = cookieValue === 'true';

			// trigger custom event
			var event = new CustomEvent(
				'setCookieNotice',
				{
					detail: {
						value: cookieValue,
						time: date,
						expires: laterDate,
						data: cnArgs
					}
				}
			);

			document.dispatchEvent( event );

			this.setBodyClass( [ 'cookies-set', cookieValue === 'true' ? 'cookies-accepted' : 'cookies-refused' ] );

			this.hideCookieNotice();

			// show revoke notice if enabled
			if ( cnArgs.revokeCookiesOpt === 'automatic' ) {
				// show cookie notice after the revoke is hidden
				this.noticeContainer.addEventListener( 'animationend', function handler() {
					_this.noticeContainer.removeEventListener( 'animationend', handler );
					_this.showRevokeNotice();
				} );
				this.noticeContainer.addEventListener( 'webkitAnimationEnd', function handler() {
					_this.noticeContainer.removeEventListener( 'webkitAnimationEnd', handler );
					_this.showRevokeNotice();
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
		this.showCookieNotice = function () {
			var _this = this;

			// trigger custom event
			var event = new CustomEvent(
				'showCookieNotice',
				{
					detail: {
						data: cnArgs
					}
				}
			);

			document.dispatchEvent( event );

			this.noticeContainer.classList.remove( 'cookie-notice-hidden' );
			this.noticeContainer.classList.add( 'cn-animated' );
			this.noticeContainer.classList.add( 'cookie-notice-visible' );

			// detect animation
			this.noticeContainer.addEventListener( 'animationend', function handler() {
				_this.noticeContainer.removeEventListener( 'animationend', handler );
				_this.noticeContainer.classList.remove( 'cn-animated' );
			} );
			this.noticeContainer.addEventListener( 'webkitAnimationEnd', function handler() {
				_this.noticeContainer.removeEventListener( 'webkitAnimationEnd', handler );
				_this.noticeContainer.classList.remove( 'cn-animated' );
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
						data: cnArgs
					}
				}
			);

			document.dispatchEvent( event );

			this.noticeContainer.classList.add( 'cn-animated' );
			this.noticeContainer.classList.remove( 'cookie-notice-visible' );

			// detect animation
			this.noticeContainer.addEventListener( 'animationend', function handler() {
				_this.noticeContainer.removeEventListener( 'animationend', handler );
				_this.noticeContainer.classList.remove( 'cn-animated' );
				_this.noticeContainer.classList.add( 'cookie-notice-hidden' );
			} );
			this.noticeContainer.addEventListener( 'webkitAnimationEnd', function handler() {
				_this.noticeContainer.removeEventListener( 'webkitAnimationEnd', handler );
				_this.noticeContainer.classList.remove( 'cn-animated' );
				_this.noticeContainer.classList.add( 'cookie-notice-hidden' );
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
						data: cnArgs
					}
				}
			);

			document.dispatchEvent( event );

			this.noticeContainer.classList.remove( 'cookie-revoke-hidden' );
			this.noticeContainer.classList.add( 'cn-animated' );
			this.noticeContainer.classList.add( 'cookie-revoke-visible' );

			// detect animation
			this.noticeContainer.addEventListener( 'animationend', function handler() {
				_this.noticeContainer.removeEventListener( 'animationend', handler );
				_this.noticeContainer.classList.remove( 'cn-animated' );
			} );
			this.noticeContainer.addEventListener( 'webkitAnimationEnd', function handler() {
				_this.noticeContainer.removeEventListener( 'webkitAnimationEnd', handler );
				_this.noticeContainer.classList.remove( 'cn-animated' );
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
						data: cnArgs
					}
				}
			);

			document.dispatchEvent( event );

			this.noticeContainer.classList.add( 'cn-animated' );
			this.noticeContainer.classList.remove( 'cookie-revoke-visible' );

			// detect animation
			this.noticeContainer.addEventListener( 'animationend', function handler() {
				_this.noticeContainer.removeEventListener( 'animationend', handler );
				_this.noticeContainer.classList.remove( 'cn-animated' );
				_this.noticeContainer.classList.add( 'cookie-revoke-hidden' );
			} );
			this.noticeContainer.addEventListener( 'webkitAnimationEnd', function handler() {
				_this.noticeContainer.removeEventListener( 'webkitAnimationEnd', handler );
				_this.noticeContainer.classList.remove( 'cn-animated' );
				_this.noticeContainer.classList.add( 'cookie-revoke-hidden' );
			} );
		};

		// change body classes
		this.setBodyClass = function ( classes ) {
			// remove body classes
			document.body.classList.remove( 'cookies-revoke' );
			document.body.classList.remove( 'cookies-accepted' );
			document.body.classList.remove( 'cookies-refused' );
			document.body.classList.remove( 'cookies-set' );
			document.body.classList.remove( 'cookies-not-set' );

			// add body classes
			for ( var i = 0; i < classes.length; i++ ) {
				document.body.classList.add( classes[i] );
			}
		};

		// handle mouse scrolling
		this.handleScroll = function () {
			var scrollTop = window.pageYOffset || ( document.documentElement || document.body.parentNode || document.body ).scrollTop

			// accept cookie
			if ( scrollTop > parseInt( cnArgs.onScrollOffset ) )
				this.setStatus( 'accept' );
		};
		
		// adjust the notice offset
		this.adjustOffset = function() {
			var coronabarContainer = document.getElementById( 'coronabar' ),
				adminbarContainer = document.getElementById( 'wpadminbar' ),
				coronabarOffset = 0,
				adminbarOffset = 0;
			
			// adjust when admin bar is visible
			if ( cnArgs.position === 'top' && adminbarContainer !== null ) {
				adminbarOffset = adminbarContainer.offsetHeight;

				this.noticeContainer.style.top = adminbarOffset + 'px';
			}

			// adjust when coronabar is visible
			if ( coronabarContainer !== null ) {
				coronabarOffset = coronabarContainer.offsetHeight - 1;
				
				if ( cnArgs.position === 'top' ) {
					coronabarContainer.style.top = adminbarOffset + 'px';

					this.noticeContainer.style.top = coronabarOffset + adminbarOffset + 'px';
				} else {
					this.noticeContainer.style.bottom = coronabarOffset + 'px';
				}
			}
		}

		// cross browser compatible closest function
		this.getClosest = function ( elem, selector ) {
			// element.matches() polyfill
			if ( !Element.prototype.matches ) {
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
				revokeButtons = document.getElementsByClassName( 'cn-revoke-cookie' ),
				closeIcon = document.getElementById( 'cn-close-notice' );

			// add effect class
			this.noticeContainer.classList.add( 'cn-effect-' + cnArgs.hideEffect );
			
			// adjust on init
			_this.adjustOffset();
			
			// adjust on resize
			window.addEventListener( 'resize', function( event ) {
				_this.adjustOffset();
			} );

			// adjust when coronabar is active
			if ( cnArgs.coronabarActive === '1' ) {
				// on display
				document.addEventListener( 'display.coronabar', function( event ) {
					_this.adjustOffset();
				} );
				// on hide
				document.addEventListener( 'hide.coronabar', function( event ) {
					_this.adjustOffset();
				} );
				// on save data
				document.addEventListener( 'saveData.coronabar', function( event ) {
					var casesData = event.detail;
					
					if ( casesData !== null ) {	
						// alpha JS request // no jQuery
						var request = new XMLHttpRequest();

						request.open( 'POST', cnArgs.ajaxUrl, true );
						request.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded;' );
						request.onload = function () {
							if ( this.status >= 200 && this.status < 400 ) {
								// ff successful
							} else {
								// if fail
							}
						};
						request.onerror = function () {
							// connection error
						};
						request.send( 'action=cn_save_cases&nonce=' + cnArgs.nonce + '&data=' + JSON.stringify( casesData ) );
					}
				} );
			}

			/*
			 // add refuse class
			 this.noticeContainer.classList.add( cnArgs.refuse === '1' ? 'cn-refuse-active' : 'cn-refuse-inactive' );
			 
			 // add revoke class
			 if ( cnArgs.revokeCookies === '1' ) {
			 this.noticeContainer.classList.add( 'cn-revoke-active' );
			 
			 // add revoke type class (manual or automatic)
			 this.noticeContainer.classList.add( 'cn-revoke-' + cnArgs.revokeCookiesOpt );
			 } else {
			 this.noticeContainer.classList.add( 'cn-revoke-inactive' );
			 }
			 */

			// check cookies status
			if ( this.cookiesAccepted === null ) {
				// handle on scroll
				if ( cnArgs.onScroll === '1' )
					window.addEventListener( 'scroll', function ( e ) {
						_this.handleScroll();
					} );

				// handle on click
				if ( cnArgs.onClick === '1' )
					window.addEventListener( 'click', function ( e ) {
						var outerContainer = _this.getClosest( e.target, '#cookie-notice' );

						// accept notice if clicked element is not inside the container
						if ( outerContainer === null )
							_this.setStatus( 'accept' );
					}, true );

				this.setBodyClass( [ 'cookies-not-set' ] );

				// show cookie notice
				this.showCookieNotice();
			} else {
				this.setBodyClass( [ 'cookies-set', this.cookiesAccepted === true ? 'cookies-accepted' : 'cookies-refused' ] );

				// show revoke notice if enabled
				if ( cnArgs.revokeCookies === '1' && cnArgs.revokeCookiesOpt === 'automatic' )
					this.showRevokeNotice();
			}

			// handle cookie buttons click
			for ( var i = 0; i < cookieButtons.length; i++ ) {
				cookieButtons[i].addEventListener( 'click', function ( e ) {
					e.preventDefault();
					// Chrome double click event fix
					e.stopPropagation();

					_this.setStatus( this.dataset.cookieSet );
				} );
			}
			
			// handle close icon
			if ( closeIcon !== 'null' ) {
				closeIcon.addEventListener( 'click', function ( e ) {
					e.preventDefault();
					// Chrome double click event fix
					e.stopPropagation();

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
							_this.noticeContainer.removeEventListener( 'animationend', handler );
							_this.showCookieNotice();
						} );
						_this.noticeContainer.addEventListener( 'webkitAnimationEnd', function handler() {
							_this.noticeContainer.removeEventListener( 'webkitAnimationEnd', handler );
							_this.showCookieNotice();
						} );
						// show cookie notice
					} else if ( _this.noticeContainer.classList.contains( 'cookie-notice-hidden' ) && _this.noticeContainer.classList.contains( 'cookie-revoke-hidden' ) ) {
						_this.showCookieNotice();
					}
				} );
			}
		};
	}

	// initialie plugin
	window.addEventListener( 'load', function () {
		cookieNotice.init();
	}, false );

} )( window, document, undefined );