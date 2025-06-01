<?php

/**
 * Class for adding a basic helpers for use throughout the plugin
 *
 * @link       https://bradmessenger.com/
 * @since      1.0.0
 *
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments
 */

namespace Justifi_Payments;

class Justifi_Helpers {
	
	public function __construct() {
		/* 
		 * Setup any AJAX hooks
		 */
		add_action( 'wp_ajax_nopriv_dismiss_notice', array( $this, 'dismiss_notice_ajax' ));
		add_action( 'wp_ajax_dismiss_notice', array( $this, 'dismiss_notice_ajax' ));
	}
	
	/* Our hook Callback to start everything off */
	function dismiss_notice_ajax() {
		
		if ( current_user_can( 'edit_posts' ) && isset( $_POST['org_id'] ) && isset( $_POST['notice_id'] ) && wp_verify_nonce( $_POST['nonce'], 'dismiss-notice' ) ) {
			
			$org_id = $_POST['org_id'];
			$notice_id = $_POST['notice_id']; // Unique "Justifi event id" assigned when the sub account was updated
			$counter = 0;
			
			$notice_meta = get_post_meta( $org_id, '_organization_notices', true ); // Grab the current post meta array
			
			if ( is_array( $notice_meta ) ) {
				
				/* Find the matching Post Meta to remove --------
				 * @ $notice_meta - array( array( 'id' => 'evt_xxx', 'message' => '...', 'unread' => bool ) )
				 * @ Loop through each nested array to find the "Justifi event id" that corresponds with the dismiss button
				 */
				 
				foreach ( $notice_meta as $notice => $meta_set ) {
					if ( isset( $meta_set['id'] ) && $meta_set['id'] === $notice_id ) {
						//unset( $notice_meta[ $notice ] ); // Remove the matching array item
						$notice_meta[ $notice ]['unread'] = false; 
					}
				}
				
			}
			$notice_meta = array_values( $notice_meta ); //Reindex our array
			update_post_meta( $org_id, '_organization_notices', $notice_meta ); //Update the post_meta with the new values
		
			
			$notice_list = '';
			$notice_list = '<ul class="notice-list">';
			
			if ( is_array( $notice_meta ) ) {
				foreach( $notice_meta as $notice ) {
					if ( $notice['unread'] == true ) { 
						$counter++;
						$notice_list .= '<li class="notice ' . sanitize_title( $notice['type'] ) .'"><h5>' . $notice['type'] . '</h5>' .$notice['message']. '<span class="dismiss-notice" data-notice-id="' .$notice['id']. '" data-org-id="' .$org_id. '" data-nonce="' .wp_create_nonce( 'dismiss-notice' ). '"><i class="fa fa-times" aria-hidden="true"></i></span></li>';
					}
				}
				$notice_list .= '</ul>';
			}
			
			if ( $counter > 0 ) {
				$counter = '<span class="notice-counter active">' .$counter. '</span>';
			} else {
				$counter = '';
			}
			
			ob_start();
				
			$html .= '<div class="alerts"><i class="fas fa-bell"></i> '.$counter. '</div>';
			if ( $counter ) {
				$html .= '<div class="notices">';
				$html .= '<div class="loader"><img src="' .get_stylesheet_directory_uri(). '/icons/ajax-loading.gif" alt="Ajax Loading GIF" width="60px" height="60px"></div>';
				$html .= $notice_list;
				$html .= '<button class="close-list btn btn-primary">Close</button>';
				$html .= '</div>';
			}
			$html .= ob_get_clean();
			
			echo $html;
				
		}  else {
			 die ( 'I see what\'s happening here.');
		 }
		 
		 wp_die();
		 
		
	}
	
	/* 
	 * Generate Access tokens 
	 *
	 * All of the API calls to Justifi require an access token
	 * 
	 */
	public static function get_access_token() {
		
		//$client_id = 'test_0aadfb2acdb179d279f701144766b535';
		//$client_secret = 'test_4afd748a193aef974cb4e572f4537bd9df9916461fc5f174f335c38b6885847a';
		
		$client_id = get_option('justifi_config_options')['client_key'];
		$client_secret = get_option('justifi_config_options')['client_secret'];
		$token_url = 'https://api.justifi.ai/oauth/token';
		
		$at_args = array(
			'grant_type' => 'client_credentials',
			'client_id' => $client_id,
			'client_secret' => $client_secret,
		);
		
		$response = wp_remote_post( $token_url, array(
			'body' => $at_args,
		) );
		
		$response_body = wp_remote_retrieve_body( $response );
		$decoded = json_decode($response_body, true);
		$access_token = $decoded['access_token'];
		
		return( $access_token );
		
	}
	
	/* Get the Organization ID assigned by WordPress */
	public static function get_org_id() {
		
		$user_id = get_current_user_id();
		$org_id = \Staxx\Helpers::get_organization_profile_id( $user_id );
		
		return $org_id;
			
	}
	
	/* Get the Sub Account ID assigned by Justifi - using the author id */
	public static function get_subaccount__author_id( $author ) {
		
		$user_id = $author;
		$org_id = \Staxx\Helpers::get_organization_profile_id( $user_id );
		
		return self::get_subaccount__org_id( $org_id );
		
	}
	
	/* Get the Sub Account ID assigned by Justifi using the organization id */
	public static function get_subaccount__org_id( $org_id ) {

		//$org_id = \Staxx\Helpers::get_organization_profile_id( $user_id );
		metadata_exists( 'post', $org_id, 'justifi_sub_account_id') ? $sa_id = get_post_meta( $org_id, 'justifi_sub_account_id', true) : $sa_id = null;
	
		return $sa_id;
		
	}
	
	/* Get the Business ID assigned by Justifi */
	public static function get_biz_id() {
		
		$user_id = get_current_user_id();
		$org_id = \Staxx\Helpers::get_organization_profile_id( $user_id );
		//error_log( print_r( $user_id, true ) );
		metadata_exists( 'post', $org_id, 'justifi_business_id') ? $biz_id = get_post_meta( $org_id, 'justifi_business_id', true) : $biz_id = null;
	
		return $biz_id;
		
	}
	
	/* Get the Bank Account ID assigned by Justifi */
	public static function get_ba_id() {
		
		$user_id = get_current_user_id();
		$org_id = \Staxx\Helpers::get_organization_profile_id( $user_id );
		metadata_exists( 'post', $org_id, 'justifi_bank_account_id') ? $ba_id = get_post_meta( $org_id, 'justifi_bank_account_id', true) : $ba_id = null;
	
		return $ba_id;
		
	}
	
	/* public static function update_with_required( $biz_id ) {
		
		$url = 'https://api.justifi.ai/v1/entities/business/' .$biz_id;
		$token = Justifi_Helpers::get_access_token();
			
		$payload = array(
			"business_structure"	=> "public_partnership",
			"representative"		=> array(
				"address"			=> array(
					"country"		=> "USA",
				),
				"ssn_last4" 			=> "1234",
				"identification_number" => "123456789",
			),
		);
		$args = array(
			'headers' => array(
				'Content-Type' 	=> 'application/json',
				'Authorization' => 'Bearer ' .$token ,
			),
			'method' 	=> 'PATCH',
			'body' 		=> wp_json_encode( $payload ),
		); 
		
		//error_log( print_r( $args, true ) );
		$response = wp_remote_post( $url, $args );
		$response_body = wp_remote_retrieve_body( $response );
		$details = json_decode($response_body, true);
		
		error_log( print_r( 'We had to manually populate a few fields in order to succesfully provision the payment product: ', true ) );
		error_log( print_r( $response, true ) );
		
		return $response;
		
	} */
	
	/* Get the sub account details jusing the business ID */
	public static function justifi_get_sub_account( $biz_id ) {
		
		if ( !isset( $biz_id) ) {
			return 0;
		} else {
			$details = array();
			$url = "https://api.justifi.ai/v1/sub_accounts/" .$biz_id;
			$args = array(
				'headers' => array(
					'Content-Type'	=> 'application/json',
					'Authorization' => 'Bearer ' . Justifi_Helpers::get_access_token(),
				),
				'method' 	=> 'GET',
			);
			
			$response = wp_remote_get( $url, $args );
			$response_body = wp_remote_retrieve_body( $response );
			$details = json_decode($response_body, true);
			//error_log( print_r( $details, true ) );
			return $details;
		
		}
		
	}
	
	/* Get only the sub account status */
	public static function justifi_get_sub_account_status( $sub_id ) {
		
		if ( !isset( $sub_id) ) {
			return 0;
		} else {

			$url = "https://api.justifi.ai/v1/sub_accounts/" .$sub_id;
			$args = array(
				'headers' => array(
					'Content-Type'	=> 'application/json',
					'Authorization' => 'Bearer ' . Justifi_Helpers::get_access_token(),
				),
				'method' 	=> 'GET',
			);
			
			// "created" "submitted" "information_needed" "rejected" "enabled" "disabled" "archived"
			$response = wp_remote_get( $url, $args );
			$response_body = wp_remote_retrieve_body( $response );
			$details = json_decode($response_body, true);
			$status = $details['data']['status'];
			return $status;
		
		}
		
	}
	
	/* Get the business details */
	public static function justifi_get_business( $biz_id ) {
		
		if ( !isset( $biz_id) ) {
			return false;
		} else {
			$details = array();
			$url = "https://api.justifi.ai/v1/entities/business/" .$biz_id;
			$args = array(
				'headers' => array(
					'Content-Type'	=> 'application/json',
					'Authorization' => 'Bearer ' . Justifi_Helpers::get_access_token(),
				),
				'method' 	=> 'GET',
			);
			
			$response = wp_remote_get( $url, $args );
			$response_body = wp_remote_retrieve_body( $response );
			$business = json_decode($response_body, true);
			return $business;
		
		}
		
	}
	
	
	public static function justifi_business_form_submitted( $biz_id ) {
	
		if ( !isset( $biz_id) ) {
			return false;
		} else {
			$details = array();
			$url = "https://api.justifi.ai/v1/entities/business/" .$biz_id;
			$args = array(
				'headers' => array(
					'Content-Type'	=> 'application/json',
					'Authorization' => 'Bearer ' . Justifi_Helpers::get_access_token(),
				),
				'method' 	=> 'GET',
			);
			
			$response = wp_remote_get( $url, $args );
			$response_body = wp_remote_retrieve_body( $response );
			$business = json_decode($response_body, true);
			$business = $business['data'];
			
			if ( ( !empty( $business['doing_business_as'] ) || !empty( $business['legal_address']['id'] ) || !empty( $business['legal_address']['id'] ) || !empty( $business['legal_address']['line1'] ) || !empty( $business['legal_address']['city'] ) || !empty( $business['representative']['name'] ) ) &&  $business['terms_conditions_accepted'] == true ) {
				return true;
			} else {
				return false;
			}
		
		}
		
	}
	
	public static function get_checkout_id( $subaccount, $token, $total ) {
		
		$url = 'https://api.justifi.ai/v1/checkouts/';
		
		$payload = array(
			"amount"	=> $total,
			"currency"	=> "usd",
			"description" => "WooCommerce Order",
		);
		
		$args = array(
			'headers' => array(
				'Content-Type'	=> 'application/json',
				'Authorization' => 'Bearer ' . $token,
				'Sub-Account'	=> $subaccount,
			),
			'method'=> 'POST',
			'body' 	=> wp_json_encode( $payload ),
		);

		
		$response = wp_remote_post( $url, $args );
		$response_body = wp_remote_retrieve_body( $response );
		$checkout = json_decode($response_body, true);
	
		return $checkout;
		
	}
	
	
}

new \Justifi_Payments\Justifi_Helpers();