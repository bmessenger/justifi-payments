(function( $ ) {
	'use strict';
	
	$(document).on("input", "#justifi_cc", function () {
		
		if ( $('#justifi_cc').val().length == 16 && checkLuhn( $('#justifi_cc').val() ) ) {
			$('#status').html('<i class="fas fa-check"></i>');
		} else {
			//alert('Please make sure you enter valid CC Number');
			$('#status').html('<i class="fas fa-times"></i>');
		}
	});
	
	$(document).on("keypress", "#justifi_cc", function () {
		if ( $(this).val().length == 16 ) {
			alert('Must be a valid card number with 16 digits');
			return false;
		}
	});
	
	/* Check to ensure the Credit card has 16 digits on Submit*/	
	$('#place_order').click( function(e) {     
		if ( $(this).val().length != 16 ) {
			alert('Please make sure you enter valid CC Number');
			return false;
		}
	});
	
	// Simple Luhn Algorith to check for valid CC numbers before the form is submitted
	function checkLuhn(value) {
	  var value = value.replace(/\D/g, '');
	  var sum = 0;
	  var shouldDouble = false;

	  for (var i = value.length - 1; i >= 0; i--) {
		var digit = parseInt(value.charAt(i));
		
		if (shouldDouble) {
		  if ((digit *= 2) > 9) digit -= 9;
		}
	
		sum += digit;
		shouldDouble = !shouldDouble;
	  }
	  return (sum % 10) == 0;
	}



})( jQuery );
