(function( $ ) {
	'use strict';
	 
	 /* Onboarding Form Validation */
	 $(document).ready(function() {
		 
		/* Account number max 17 digits */
		$('#account_number').keypress(function() {
			if ( $(this).val().length == 17 ) {
				alert('Account number must be a valid number and a max of 17 digits');
				return false;
			}
		});
		
		/* Routing number max 9 digits */
		$('#routing_number').keypress(function() {
			if ( $(this).val().length == 9 ) {
				alert('Routing number must be a valid number and exactly 9 digits');
				return false;
			}
		});
		$("#onboarding_submit").click( function(e) {      
			if ( $('#routing_number').val().length != 9 ) {
				alert("The Routing Number must be 9 digits.");
			}
		});
		
		$(document).on('click', '#close-details', function() {
			$('.justifi-table .payment-details').hide();
		});
		
		/* Dashboard Alert Handling */
		$('body').on('click', '.account-notices .alerts', function() { // Open the notices
			if ( $('.notice-counter').length ) {
				if ( $('.notices').hasClass('active') ) {
					$('.notices').slideUp('fast');
					$('.notices, .alerts').removeClass('active');
				} else {
					$('.notices').slideDown('fast');
					$('.notices, .alerts').addClass('active');
				} 
			}
		});
		
		$('body').on('click', '.close-list', function() {
			$('.notices').slideUp('fast');
			$('.notices, .alerts').removeClass('active');
		});
		
		 
	});


})( jQuery );
