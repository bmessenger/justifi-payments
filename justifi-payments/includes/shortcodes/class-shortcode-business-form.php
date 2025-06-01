<?php
/**
 * Add the Shortcode for the Business Form Web Component in Justifi
 *
 * @link       https://bradmessenger.com/
 * @since      1.0.0
 *
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments/shortcodes
 */

namespace Justifi_Payments\Shortcodes;
use Justifi_Payments\Justifi_Helpers;

/**
 * Add menu items and pages to the WordPress admin. 
 */
class Business_Form {
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->generate_shortcode();
	}

	public function generate_shortcode() {
		add_shortcode( 'Justifi_Business_Form', array( $this, 'form_generate' ) );
	}
	
	protected function get_web_token() {
		
		$biz = array();
		$business_id = Justifi_Helpers::get_biz_id();
		$access_token = Justifi_Helpers::get_access_token();
		$url = 'https://api.justifi.ai/v1/web_component_tokens';
		
		$resources = array(
			'resources' => array(
				'write:business:' .$business_id,
			),
		);
		$args = array(
			'headers' => array(
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer ' . $access_token,
			),
			'body' => wp_json_encode($resources),
		); 

		$response = wp_remote_post( $url, $args );
		$response_body = wp_remote_retrieve_body( $response );
		$decoded = json_decode($response_body, true);
		$biz['web_token'] = $decoded['access_token'];
		$biz['biz_id']	= $business_id;
		
		return( $biz );
		
	}

	/**
	 * Setup the Business Form Web Component
	 *
	 * @since 1.0.0
	 */
	public function form_generate () {
			
			$output = '';

			$biz = $this->get_web_token();
			
			$submitted = Justifi_Helpers::justifi_business_form_submitted( $biz['biz_id'] );
			
			if ( isset( $biz['biz_id'] ) && $submitted == true ) {
				
				$output = '
				<div class="form-notices">
					<div class="message no-account">
						<p>It looks like you\'ve already submitted your business form.  <a href="/dashboard/justifi-business-details/" id="bizDetails">You may review your business information here</a>.</p>
						<p>If you need to make any changes to the information you\'ve submitted please reach out to <a href="mailto:support@gmail.com">support@gmail.com</a>.</p>
					</div>
				</div>
				';
				
				return $output;
			
			} elseif ( isset( $biz['biz_id'] ) && $submitted == false ) {
	
		 		$output .= '<justifi-business-form business-id="' .$biz['biz_id']. '" auth-token="' .$biz['web_token']. '"></justifi-business-form>';
				$output .= '<div class="justifi-biz-form-success"><p></p></div>';
		 		$output .= '<script>
		   			(function() {
						   
						var message = \'<div class="form-notices"><section class="step message step-completed">Your form has been submitted!  Head back to your <a href="/dashboard/">account dashboard</a>.</section></div>\';
			 			var businessForm = document.querySelector("justifi-business-form");
			 			businessForm.addEventListener("submitted", (data) => {
			   				/* this event is raised when the server response is received */
							console.log("server response received", data);
							let output = message;
							jQuery("justifi-business-form").addClass("submitted");
							jQuery(".justifi-biz-form-success p").html(output);
							window.location.href = "' .home_url(). '/dashboard/";
			   				
			 			});
		 			
		   			})();
		 			</script>';
									
				return $output;
				
			} else {
				
				$output = '
					<div class="message no-account">
						<p>It looks like you still need to create your account with Justifi, our new payment processor.  To do so head back to your <a href="/dashboard/">Dashboard</a> page and look for the "Create My Business" button to get started.  If you can\'t find your button please reach out to ou support team.</p>
					</div>
				';
				
				return $output;
			}

		 
	}

}


new \Justifi_Payments\Shortcodes\Business_Form();