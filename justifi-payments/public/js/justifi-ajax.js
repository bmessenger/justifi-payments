(function( $ ) {
	'use strict';

	$(document).ready(function() {
		
		 $("#bizBtn").click(function() {

			 let nonce = $(this).data('nonce');

			 $.ajax({
				 url: justifi_ajax.ajax_url,
				 type: "POST",
				 data: {
					action: "createBiz",
				    user_id: justifi_ajax.user_id,
					nonce: nonce,
					 },
				 dataType: 'json',
				 beforeSend: function() {
					 $('.justifi-loader').addClass('show');
					 $('#bizBtn').prop("disabled", true);
				 },
				 success: function(response) {
					 $('#justifi-dashboard').html(response);
				 },
				 complete: function(data) {
					 $('.justifi-loader').removeClass('show');
					 $('#bizBtn').prop("disabled", false);
				 },
			 });
			 
		 });
		 
		 $('body').on('click', '.dismiss-notice', function() {
			
			let notice_id = $(this).data('notice-id');
			let org_id = $(this).data('org-id');
			let nonce = $(this).data('nonce');
			
			if ( notice_id && org_id && nonce ) {
				$.ajax({
				 	url: justifi_ajax.ajax_url,
				 	type: "POST",
				 	data: {
						action: "dismiss_notice",
						notice_id: notice_id,
						org_id: org_id,
						nonce: nonce,
					 	},
				 	beforeSend: function() {
					 	$('#top .account-notices .notices .loader').addClass('active');
				 	},
				 	success: function(response) {
					 	$('.account-notices').html(response);
				 	},
				 	complete: function(data) {
					 	$('#top .account-notices .notices .loader').removeClass('active');
				 	},
			 	});
			} else {
				
				alert('Organization or Notice ID not found.');
				
			} 
			 
		 })
		 
	 });

})( jQuery );