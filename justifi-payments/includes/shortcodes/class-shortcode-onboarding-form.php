<?php
/**
 * Add the Shortcode for the Justifi Onboarding Form (Not a Web Component - Custom)
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
class Onboarding_Form {
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->generate_shortcode();
	}

	public function generate_shortcode() {
		add_shortcode( 'Justifi_Onboarding_Form', array( $this, 'build_onboarding_form' ) );
	}
	
	public function build_onboarding_form() {
		
		$bank = Justifi_Helpers::get_ba_id();
			
		if ( $bank ) {
			
			$output = '
			<div class="form-notices">
				<div class="message no-account">
					<p>It looks like you\'ve already submitted your banking account info.  <a href="/dashboard/justifi-business-details/" id="bizDetails">You may review your business information here</a>.</p>
					<p>If you need to make any changes to the information you\'ve submitted please reach out to <a href="mailto:support@gmail.com">support@gmail.com</a>.</p>
				</div>
			</div>
			';
			
			return $output;
			
		} elseif ( isset( $_POST['onboarding_submit'] ) ) {
			
			$payload = $this->gather_payload();
			$ob_response = $this->onboard( $payload );
			$tc_response = $this->accept_the_terms( $payload );
			
			if ( isset( $ob_response['id'] ) && isset( $tc_response['id'] ) ) {
				
				$org_id = Justifi_Helpers::get_org_id();				
				$upm = update_post_meta( $org_id, 'justifi_bank_account_id', $ob_response['id'] );
				
				header('Location: ' .home_url('dashboard'));
				
				
			} else {
				
				$output = '
					<div class="messager error">
						<p>There was an error submitting your Account information.</p>
						<p>Error Message: ' .$ob_response['error']['message']. '</p>
						<p>Please check your Account details and <a href="/dashboard/justifi-new-account-onboarding/">try again</a>.</p>
					</div>
				';
				return $output;
				
			}
			
		
		} else {
			
			$output = '<form method="post" action="" id="justifi_onboarding" class="justifi-onboard">';
			$output .= '
				<div class="form-input">
					<label for="account_owner">Full Name of Account Owner<span class="req">*</span></label>
					<input type="text" id="account_owner" name="account_owner" required />
				</div>
				<div class="form-input">
					<label for="account_type">Account Type<span class="req">*</span></label><br>
					<fieldset id="account_type" required>
						<input type="radio" id="jfi-checking" name="account_type" value="checking">
						<label for="jfi-checking">Checking</label><br>
						<input type="radio" id="jfi-savings" name="account_type" value="savings">
						<label for="jfi-savings">Savings</label><br>
					</fieldset>
				</div>
				<div class="form-input">
					<label for="account_number">Account Number<span class="req">*</span> Test: 000123456789</label>
					<input id="account_number" type="number" name="account_number" maxlength="17" required />
				</div>
				<div class="form-input">
					<label for="routing_number">Routing Number<span class="req">*</span> Test: 110000000</label>
					<input id="routing_number" type="number" name="routing_number" maxlength="9" required />
				</div>
				<div class="form-input">
					<label for="bank_name">Bank Name*</label>
					<input id="bank_name" type="text" name="bank_name" required/>
				</div>
				<div class="form-input">
					<label for="nickname">Nickname (for the account)</label>
					<input id="nickname" type="text" name="nickname" required/>
				</div>
				<div class="form-input tc">
					<input type="checkbox" value="accept" name="terms" id="terms" required><label for="terms">I agree to the terms and conditions set in the JustiFi Technologies, Inc. <a href="https://justifi.tech/terms-of-service/" target="_blank">Terms of Service</a>.
				</div>
				<input type="submit" name="onboarding_submit" id="onboarding_submit" class="submit" value="Submit" />
				
			</form>
			';
			
			return $output;
			
		}
		
	}
	
	public function gather_payload() {
		
		$business_id = Justifi_Helpers::get_biz_id();
		isset( $_POST['terms'] ) ? $terms = true : $terms = false;
		
		$biz_details = array(
			'account_owner_name' 	=> sanitize_text_field( $_POST['account_owner'] ),
			'account_type' 			=> $_POST['account_type'],
			'account_number' 		=> absint( $_POST['account_number'] ),
			'routing_number' 		=> absint( $_POST['routing_number'] ),
			'business_id' 			=> $business_id,
			'bank_name' 			=> sanitize_text_field(  $_POST['bank_name'] ),
			'nickname' 				=> sanitize_text_field(  $_POST['nickname'] ),
			'metadata'				=> array(
				'accepted' 				=> $terms,
				'ua' 	 				=> $_SERVER['HTTP_USER_AGENT'],
				'ip' 					=> $_SERVER['REMOTE_ADDR'],
			),
		);
		//error_log( print_r( $biz_details, true ) );
		return( $biz_details );
		
	}
	
	public function onboard( $payload ) {
		
		$url = 'https://api.justifi.ai/v1/entities/bank_accounts';
		$access_token = Justifi_Helpers::get_access_token();
		
		$response = wp_remote_post( $url, array(
			'headers' => array(
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer ' . $access_token,
			),
			'body' => wp_json_encode( $payload ),
		) );
		
		if ( is_wp_error( $response ) ) {
			return false;
		}
		
		/**************************
		 * Adds any required fields needed to Provision the Payments product
		 * Will likely go away in production 
		 **************************/
		//$update_required = Justifi_Helpers::update_with_required( $business_id );
		/********** END ***********/
		
		$response_body = wp_remote_retrieve_body( $response );
		$decoded = json_decode($response_body, true);
		
		return $decoded;

	}
	
	public function accept_the_terms( $payload ) {
		
		$url = 'https://api.justifi.ai/v1/entities/terms_and_conditions';
		$access_token = Justifi_Helpers::get_access_token();
		
		$args = array(
			'business_id'	=> $payload['business_id'],
			'accepted'		=> $payload['metadata']['accepted'],
			'ip'			=> $payload['metadata']['ip'],
			'user_agent'	=> $payload['metadata']['ua'],
		);
		
		$response = wp_remote_post( $url, array(
			'headers' => array(
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer ' . $access_token,
			),
			'body' => wp_json_encode( $args ),
		) );
		
		if ( is_wp_error( $response ) ) {
			return false;
		}
		
		$response_body = wp_remote_retrieve_body( $response );
		$decoded = json_decode($response_body, true);
		
		return $decoded;
		
	}
}


new \Justifi_Payments\Shortcodes\Onboarding_Form();