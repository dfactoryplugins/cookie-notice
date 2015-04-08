(function($) {
	
	$(document).ready(function() {
		
		// initialize color picker
		$('.cn-color').wpColorPicker();
		
		// read more option
		$('#cn-see-more-yes, #cn-see-more-no').change(function() {
			if($('#cn-see-more-yes:checked').val() === 'yes') {
				$('#cn_see_more_opt').fadeIn(300);
			} else if($('#cn-see-more-no:checked').val() === 'no') {
				$('#cn_see_more_opt').fadeOut(300);
			}
		});
		
		// read more link
		$('#cn-see-more-link-custom, #cn-see-more-link-page').change(function() {
			if($('#cn-see-more-link-custom:checked').val() === 'custom') {
				$('#cn_see_more_opt_page').fadeOut(300, function() {
					$('#cn_see_more_opt_link').fadeIn(300);
				});
			} else if($('#cn-see-more-link-page:checked').val() === 'page') {
				$('#cn_see_more_opt_link').fadeOut(300, function() {
					$('#cn_see_more_opt_page').fadeIn(300);
				});
			}
		});
		
	});
	
})(jQuery);