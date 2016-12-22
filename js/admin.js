( function ( $ ) {

    $( document ).ready( function () {

	// initialize color picker
	$( '.cn_color' ).wpColorPicker();

	// refuse option
	$( '#cn_refuse_opt' ).change( function () {
	    if ( $( this ).is( ':checked' ) ) {
		$( '#cn_refuse_opt_container' ).slideDown( 'fast' );
	    } else {
		$( '#cn_refuse_opt_container' ).slideUp( 'fast' );
	    }
	} );

	// read more option
	$( '#cn_see_more' ).change( function () {
	    if ( $( this ).is( ':checked' ) ) {
		$( '#cn_see_more_opt' ).slideDown( 'fast' );
	    } else {
		$( '#cn_see_more_opt' ).slideUp( 'fast' );
	    }
	} );

	// read more option
	$( '#cn_on_scroll' ).change( function () {
	    if ( $( this ).is( ':checked' ) ) {
		$( '#cn_on_scroll_offset' ).slideDown( 'fast' );
	    } else {
		$( '#cn_on_scroll_offset' ).slideUp( 'fast' );
	    }
	} );

	// read more link
	$( '#cn_see_more_link-custom, #cn_see_more_link-page' ).change( function () {
	    if ( $( '#cn_see_more_link-custom:checked' ).val() === 'custom' ) {
		$( '#cn_see_more_opt_page' ).slideUp( 'fast', function () {
		    $( '#cn_see_more_opt_link' ).slideDown( 'fast' );
		} );
	    } else if ( $( '#cn_see_more_link-page:checked' ).val() === 'page' ) {
		$( '#cn_see_more_opt_link' ).slideUp( 'fast', function () {
		    $( '#cn_see_more_opt_page' ).slideDown( 'fast' );
		} );
	    }
	} );

	$( document ).on( 'click', 'input#reset_cookie_notice_options', function () {
	    return confirm( cnArgs.resetToDefaults );
	} );

    } );

} )( jQuery );