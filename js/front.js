// get cookie notice value/status
function cnGetCookieNotice( bool ) {
	var value = "; " + document.cookie,
		parts = value.split( '; cookie_notice_accepted=' );

	if ( parts.length === 2 ) {
		var val = parts.pop().split( ';' ).shift();

		if ( bool )
			return val === 'true';
		else
			return val;
	}
	else
		return null;
}

// set cookie notice
function cnSetCookieNotice( cookieValue ) {
	// remove listening to scroll event
	if ( cnArgs.onScroll === 'yes' )
		window.removeEventListener( 'scroll', cnHandleScroll );

	var date = new Date(),
		laterDate = new Date(),
		cookieNoticeContainer = document.getElementById( 'cookie-notice' ),
		cookieStatus = cnGetCookieNotice( false );

	// set expiry time in seconds
	laterDate.setTime( parseInt( date.getTime() ) + parseInt( cnArgs.cookieTime ) * 1000 );

	// set cookie type
	cookieValue = cookieValue === 'accept' ? 'true' : 'false';

	// set cookie
	document.cookie = cnArgs.cookieName + '=' + cookieValue + ';expires=' + laterDate.toUTCString() + ';' + ( cnArgs.cookieDomain !== '' ? 'domain=' + cnArgs.cookieDomain + ';' : '' ) + ( cnArgs.cookiePath !== '' ? 'path=' + cnArgs.cookiePath + ';' : '' ) + ( cnArgs.secure === '1' ? 'secure;' : '' );

	// update global status
	cnCookiesAccepted = cookieValue === 'true';
	
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

	cnSetBodyClass( [ 'cookies-set', cookieValue === 'true' ? 'cookies-accepted' : 'cookies-refused' ] );

	cnHideCookieNotice();
	
	// show revoke notice if enabled
	if ( cnArgs.revoke_cookies_opt === 'automatic' ) {
		// show cookie notice after the revoke is hidden
		cookieNoticeContainer.addEventListener( 'animationend', function handler() {
			cnShowRevokeNotice();
			this.removeEventListener( 'animationend', handler );
		} ); 
		cookieNoticeContainer.addEventListener( 'webkitAnimationEnd', function handler() {
			cnShowRevokeNotice();
			this.removeEventListener( 'webkitAnimationEnd', handler );
		} );
	}

	// redirect?
	if ( cnArgs.redirection === '1' && ( ( cookieValue === 'true' && cookieStatus === null ) || ( cookieValue !== cookieStatus && cookieStatus !== null ) ) ) {
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
}

// display cookie notice
function cnShowCookieNotice() {
	var cookieNoticeContainer = document.getElementById( 'cookie-notice' );
	
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
	
	cookieNoticeContainer.classList.remove( 'cookie-notice-hidden' );
	cookieNoticeContainer.classList.add( 'cn-animated', 'cookie-notice-visible' );
	
	// console.log( 'show' );

	// detect animation
	cookieNoticeContainer.addEventListener( 'animationend', function handler() {
		cookieNoticeContainer.classList.remove( 'cn-animated' );
		this.removeEventListener( 'animationend', handler );
		// console.log( 'show end' );
	} ); 
	cookieNoticeContainer.addEventListener( 'webkitAnimationEnd', function handler() {
		cookieNoticeContainer.classList.remove( 'cn-animated' );
		this.removeEventListener( 'webkitAnimationEnd', handler );
		// console.log( 'show end' );
	} ); 
}

// hide cookie notice
function cnHideCookieNotice() {
	var cookieNoticeContainer = document.getElementById( 'cookie-notice' );

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

	cookieNoticeContainer.classList.add( 'cn-animated' );
	cookieNoticeContainer.classList.remove( 'cookie-notice-visible' );

	// detect animation
	cookieNoticeContainer.addEventListener( 'animationend', function handler() {
		cookieNoticeContainer.classList.remove( 'cn-animated' );
		cookieNoticeContainer.classList.add( 'cookie-notice-hidden' );
		this.removeEventListener( 'animationend', handler );
	} ); 
	cookieNoticeContainer.addEventListener( 'webkitAnimationEnd', function handler() {
		cookieNoticeContainer.classList.remove( 'cn-animated' );
		cookieNoticeContainer.classList.add( 'cookie-notice-hidden' );
		this.removeEventListener( 'webkitAnimationEnd', handler );
	} );
}

// display cookie notice
function cnShowRevokeNotice() {
	var cookieNoticeContainer = document.getElementById( 'cookie-notice' );
	
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
	
	cookieNoticeContainer.classList.remove( 'cookie-revoke-hidden' );
	cookieNoticeContainer.classList.add( 'cn-animated', 'cookie-revoke-visible' );

	// detect animation
	cookieNoticeContainer.addEventListener( 'animationend', function handler() {
		cookieNoticeContainer.classList.remove( 'cn-animated' );
		this.removeEventListener( 'animationend', handler );
	} ); 
	cookieNoticeContainer.addEventListener( 'webkitAnimationEnd', function handler() {
		cookieNoticeContainer.classList.remove( 'cn-animated' );
		this.removeEventListener( 'webkitAnimationEnd', handler );
	} );
}

// hide cookie notice
function cnHideRevokeNotice() {
	var cookieNoticeContainer = document.getElementById( 'cookie-notice' );
	
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

	cookieNoticeContainer.classList.add( 'cn-animated' );
	cookieNoticeContainer.classList.remove( 'cookie-revoke-visible'  );

	// detect animation
	cookieNoticeContainer.addEventListener( 'animationend', function handler() {
		cookieNoticeContainer.classList.remove( 'cn-animated' );
		cookieNoticeContainer.classList.add( 'cookie-revoke-hidden' );
		this.removeEventListener( 'animationend', handler );
	} ); 
	cookieNoticeContainer.addEventListener( 'webkitAnimationEnd', function handler() {
		cookieNoticeContainer.classList.remove( 'cn-animated' );
		cookieNoticeContainer.classList.add( 'cookie-revoke-hidden' );
		this.removeEventListener( 'webkitAnimationEnd', handler );
	} ); 
}

// change body classes
function cnSetBodyClass( classes ) {
	// remove body classes
	document.body.classList.remove( 'cookies-revoke', 'cookies-accepted', 'cookies-refused', 'cookies-set', 'cookies-not-set' );

	// add body classes
	for ( var i = 0; i < classes.length; i++ ) {
		document.body.classList.add( classes[i] );
	}
}

// handle mouse scrolling
function cnHandleScroll() {
	var scrollTop = window.pageYOffset || (document.documentElement || document.body.parentNode || document.body).scrollTop

	if ( scrollTop > parseInt( cnArgs.onScrollOffset ) ) {
		// accept cookie
		cnSetCookieNotice( 'accept' );
		
		// console.log( 'scroll end' );
	} else {
		// console.log( 'scrolling' );
	}
};

// initialize notice
function cnInitCookieNotice() {
	var cookieNoticeContainer = document.getElementById( 'cookie-notice' ),
		cookieStatus = cnGetCookieNotice( false ),
		cookieButtons = document.getElementsByClassName( 'cn-set-cookie' ),
		revokeButtons = document.getElementsByClassName( 'cn-revoke-cookie' );
		
	// add effect class
	cookieNoticeContainer.classList.add( 'cn-effect-' + cnArgs.hideEffect );

	/*
	// add refuse class
	cookieNoticeContainer.classList.add( cnArgs.refuse === 'yes' ? 'cn-refuse-active' : 'cn-refuse-inactive' );

	// add revoke class
	if ( cnArgs.revoke_cookies === '1' ) {
		cookieNoticeContainer.classList.add( 'cn-revoke-active' );

		// add revoke type class (manual or automatic)
		cookieNoticeContainer.classList.add( 'cn-revoke-' + cnArgs.revoke_cookies_opt );
	} else {
		cookieNoticeContainer.classList.add( 'cn-revoke-inactive' );
	}
	*/
	
	// check cookies status
	if ( cookieStatus === null ) {
		// handle on scroll
		if ( cnArgs.onScroll === 'yes' )
			window.addEventListener( 'scroll', cnHandleScroll );

		cnSetBodyClass( [ 'cookies-not-set' ] );
		
		// show cookie notice
		cnShowCookieNotice();
	} else {
		cnSetBodyClass( [ 'cookies-set', cookieStatus === 'true' ? 'cookies-accepted' : 'cookies-refused' ] );
		
		// show revoke notice if enabled
		if ( cnArgs.revoke_cookies_opt === 'automatic' ) {
			cnShowRevokeNotice();
		}
	}
	
	// handle cookie buttons click
	for ( var i = 0; i < cookieButtons.length; i++ ) {
		cookieButtons[i].addEventListener( 'click', function ( e ) {
			e.preventDefault();

			cnSetCookieNotice( this.dataset.cookieSet );
		} );
	}
	
	// handle revoke buttons click
	for ( var i = 0; i < revokeButtons.length; i++ ) {
		revokeButtons[i].addEventListener( 'click', function ( e ) {
			e.preventDefault();

			// hide revoke notice
			if ( cookieNoticeContainer.classList.contains( 'cookie-revoke-visible' ) ) {
				cnHideRevokeNotice();
				
				// show cookie notice after the revoke is hidden
				cookieNoticeContainer.addEventListener( 'animationend', function handler() {
					cnShowCookieNotice();
					this.removeEventListener( 'animationend', handler );
				} ); 
				cookieNoticeContainer.addEventListener( 'webkitAnimationEnd', function handler() {
					cnShowCookieNotice();
					this.removeEventListener( 'webkitAnimationEnd', handler );
				} ); 
			// show cookie notice
			} else {
				cnShowCookieNotice();
			}
		} );
	}
};

// get cookie status
cnCookiesAccepted = cnGetCookieNotice( true );

// initialize plugin
document.addEventListener( 'DOMContentLoaded', cnInitCookieNotice );