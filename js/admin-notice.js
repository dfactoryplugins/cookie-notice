( function ( $ ) {
	$( document ).ready( function () {
		// Save dismiss state
		$( '.cn-notice.is-dismissible' ).on( 'click', '.notice-dismiss, .cn-notice-dismiss', function ( e ) {
			if ( $( e.currentTarget ).hasClass( 'cn-approve' ) ) {
				var notice_action = 'approve';
			} else if ( $( e.currentTarget ).hasClass( 'cn-delay' ) ) {
				var notice_action = 'delay';
			} else {
				var notice_action = 'dismiss';
			}

			$.ajax( {
				url: cnArgsNotice.ajaxUrl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'cn_dismiss_notice',
					notice_action: notice_action,
					nonce: cnArgsNotice.nonce
				}
			} );

			$( e.delegateTarget ).slideUp( 'fast' );
		} );
	   
		/* Steps
		$( '.cn-notice .step-choice .cn-approve' ).on( 'click', function ( e ) {
			e.preventDefault();

			$( '.cn-notice .step-choice' ).slideUp( 'fast', 'linear', {
				start: function () {
					$( this ).css( {
						display: "flex"
					} )
				}
			} );

			if ( $( e.target ).hasClass( 'cn-reply-yes' ) ) {
				$( '.cn-notice .step-yes' ).slideDown( {
				start: function () {
					$( this ).css( {
						display: "flex"
					} )
				}
			} );
			} else {
				$( '.cn-notice .step-no' ).slideDown( {
				start: function () {
					$( this ).css( {
						display: "flex"
					} )
				}
			} );
			};
		} );
		*/
	} );
} )( jQuery );