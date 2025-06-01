<?php

/**
 * Class for housing the webhooks listening for Justifi Payment disputes
 *
 * @link       https://bradmessenger.com/
 * @since      1.0.0
 *
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments
 */

namespace Justifi_Payments\Webhooks;

class Justifi_Payment_Disputes {


	private $secret = DISPUTES_WEBHOOK_KEY;
	private $sub = array();

	/**
	  * Class constructor
	  */
	 public function __construct() {

		add_action( 'rest_api_init', array( $this, 'justifi_rest_routes') );

	}

	public function justifi_rest_routes() {

		/*
		 * Monitor for Sub Account Status Changes
		 * @refund_event - https://docs.justifi.tech/api-spec#tag/Events/operation/subAccountEvent
		 * @authentication - https://docs.justifi.tech/api-spec#tag/Webhook-Delivery
		 *
		 * The permission callback validates the signature hash from JustiFi
		 * If the permission returns true we get the request body and do the things
		 */

		register_rest_route( 'justifi/v2', '/payment-disputes', array(
			'methods'  				=> \WP_REST_Server::EDITABLE,
			'callback' 				=> array( $this, 'justifi_payment_disputes' ),
			'permission_callback'	=> array( $this, 'permission_check' ),
		) );

	}

	public function permission_check( $req ) {

		/* Get the Timestamp and hashed Signature from the JustiFi event */
		$timestamp = $req->get_header( 'Justifi-Timestamp' );
		$signature = $req->get_header( 'Justifi-Signature' );
		$jsonEvent = $req->get_body();
		$payload = "$timestamp.$jsonEvent";

		/* Hash the timestamp and payload, then compare to what we received from JustiFi */
		$hex = hash_hmac('sha256', $payload, $this->secret);

		return $signature == $hex;

	}
	
	public function justifi_payment_disputes( $req ) {
		
		$response = json_decode( $req->get_body() );
		
		//error_log( print_r( $response, true ) );
		
		if ( isset( $response ) ) {
			
			/* Query the Organization using the Business ID (previously set as post meta when creating the account) */
			$args = array(
				'post_type'      => 'organization',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
				'meta_query' => array(
					array(
						'key'		=> 'justifi_sub_account_id',
						'value' 	=> $response->account_id,
						'compare' 	=> '=',
					),
	
				),
			);
			$organization = new \WP_Query( $args );
	
			$org_id = 0;
			$auth_id = 0;
			while ( $organization->have_posts() ) { $organization->the_post();
				$org_id = get_the_ID(); // Get the Organization ID
				$auth_id = get_the_author_meta( 'ID' ); // Get the Author ID (WordPress User)
			}
	
			wp_reset_postdata();
			
			
			/*
			 *  Setup the Dispute
			 */
			$disputes = get_post_meta( $org_id, '_justifi_payment_disputes', true );
						
			if ( !is_array( $disputes ) ) {
				$disputes = array(); 
			}
			
			$dispute_id = $response->data->id;
			$disputes[ $dispute_id ][] = array(
				'event' 		=> $response->event_name,
				'account' 		=> $response->account_id,
				'payment_id' 	=> $response->data->payment_id,
				'event_status'	=> $response->data->status,
				'reason' 		=> $response->data->reason,
				'created_at'	=> $response->data->created_at,
				'status'		=> $response->event_name == 'payment.dispute.closed' ? 'closed' : 'open',
			);
			
			update_post_meta( $org_id, '_justifi_payment_disputes', $disputes );
			
			/* Handle our notices ::
		 	* Get the existing post_meta (array) and append the new account status 
		 	* along with the event id and an "unread" status of true to display the alert on the bell
		 	*/
			
			$notices = get_post_meta( $org_id, '_organization_notices', true );
		
			if ( !is_array( $notices ) ) {
				$notices = array();
				$notice_count = 0;
				$notice_index = 0;
			} else {
				$notices = array_values( $notices );// Since were always removing, editing these notices let's ensure we're always starting with a fresh index.
				$notice_count = count( $notices );
				$notice_index = $notice_count++;
			}
			
			
			$notices[ $notice_index ]['id'] = $response->account_id;
			$notices[ $notice_index ]['type'] = 'Payment Dispute';
			$notices[ $notice_index ]['event'] = $response->event_name;
			$notices[ $notice_index ]['message'] = '<span class="message" data-notice-id="' .$response->data->id. '"><span> <b>Reason:&nbsp;</b>' .$response->data->reason. '</b><span> &nbsp;<b>Status:&nbsp;</b>' .$response->data->status. '</span></span>';
			$notices[ $notice_index ]['unread'] = true;
			
			//array_push( $notices, $new_notice );
			update_post_meta( $org_id, '_organization_notices', $notices );
			

		
		}
		
	}
	
}

new \Justifi_Payments\Webhooks\Justifi_Payment_Disputes();