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
	$( '#cn_see_more_link-custom, #cn_see_more_link-page, #cn_see_more_link-legacy' ).change( function () {
        $( '.cn_see_more_opt' ).hide( 500, function () {
		    more_link = $( '#cn_see_more_opt_custom_link input:checked' ).val();
            $( '#cn_see_more_opt_'+more_link ).show( 500 );
		});
	});

	$( document ).on( 'click', 'input#reset_cookie_notice_options', function () {
	    return confirm( cnArgs.resetToDefaults );
	} );

    } );

} )( jQuery );