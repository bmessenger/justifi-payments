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

namespace Justifi_Payments;
use Justifi_Payments\Justifi_Helpers;

class Provision_Product {
	
	public function provision( $biz_id, $org_id ) {
		
		if ( $biz_id && $org_id ) {
			
			$access_token = Justifi_Helpers::get_access_token();
			$new_account_name = sanitize_text_field( get_the_title( $org_id ) ). ' - ' .$org_id;
			
			$url = 'https://api.justifi.ai/v1/entities/provisioning';
			
			$body = array(
				'new_account_name'	=> $new_account_name,
				'business_id'		=> $biz_id,
				'product_category'	=> 'payment'
			);
			
			$args = array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'Authorization' => 'Bearer ' . $access_token,
				),
				'body' => wp_json_encode( $body ),
			);
			
			$response = wp_remote_post( $url, $args );
			$response_body = wp_remote_retrieve_body( $response );
			$decoded = json_decode($response_body, true);
			
			$sub_account_id = $decoded['data']['sub_account_id'];

			if ( isset( $sub_account_id ) ) {
				update_post_meta( $org_id, 'justifi_sub_account_id', $sub_account_id );
				$message = 'Provision Succesfull.  Sub Account created.';
				return $message;	
			} else {
				return $decoded;
			}	
			
		} else {
			$message = '
				<div class="message success sub-account">
					<p>Unable to Provision Product</p>
				</div>';
			error_log( print_r( 'Business ID or Organization ID not found in class-provision-product.php' ) );
			return $message;
		}

	} 		
}

new \Justifi_Payments\Provision_Product();