jQuery(function($){
	// Fade out the status message
	$('.updated').delay(2500).fadeOut(1500);

	// Countries
	$('#fflcommerce_allowed_countries').on('change', function(){
		if($(this).val() == 'specific'){
			$('#fflcommerce_specific_allowed_countries').closest('tr').show();
		} else {
			$('#fflcommerce_specific_allowed_countries').closest('tr').hide();
		}
	}).change();
});
