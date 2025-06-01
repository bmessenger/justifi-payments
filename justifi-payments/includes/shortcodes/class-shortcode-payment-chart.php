<?php
/**
 * Add the Shortcode to display the Payment Chart for an Organization
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
class Payment_Chart {
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->generate_shortcode();
	}

	public function generate_shortcode() {
		add_shortcode( 'Justifi_Payment_Chart', array( $this, 'form_generate' ) );
	}
	
	protected function get_web_token() {
		
		$biz = array();
		//$business_id = Justifi_Helpers::get_biz_id();
		$access_token = Justifi_Helpers::get_access_token();
		$org_id = Justifi_Helpers::get_org_id();
		Justifi_Helpers::get_subaccount__org_id( $org_id ) ? $subaccount_id = Justifi_Helpers::get_subaccount__org_id( $org_id ) : $subaccount_id = null;

		$url = 'https://api.justifi.ai/v1/web_component_tokens';
		
		$resources = array(
			'resources' => array(
				'write:account:' . $subaccount_id,
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
		$biz['subaccount_id'] = $subaccount_id;
		
		return( $biz );
		
	}

	/**
	 * Setup the Business Form Web Component
	 *
	 * @since 1.0.0
	 */
	public function form_generate () {
		
				
		$biz = $this->get_web_token();
		error_log( print_r( $biz['subaccount_id'], true ) );
		
		if ( $biz['subaccount_id'] ) {
			
			$output = '<justifi-gross-payment-chart account-id="' .$biz['subaccount_id']. '" auth-token="' .$biz['web_token']. '"></justifi-gross-payment-chartt>';
				
			return $output;
			
		} else {
			
			$output = '
				<div class="message no-account">
					<p>Payment chart unavailable.  Please ensure you have access to view this information.</p>
				</div>
			';
			
			return $output;
		}
		

	}
}

new \Justifi_Payments\Shortcodes\Payment_Chart();