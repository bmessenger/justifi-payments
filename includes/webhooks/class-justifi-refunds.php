<?php

/**
 * Class for housing the webhooks listening for Justifi Refunds
 *
 * @link       https://bradmessenger.com/
 * @since      1.0.0
 *
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments
 */

namespace Justifi_Payments\Webhooks;

class Justifi_Refunds {
	
	
	private $secret = REFUND_WEBHOOK_KEY;
	private $refund = array();
	
	/**
	  * Class constructor
	  */
	 public function __construct() {
		
		add_action( 'rest_api_init', array( $this, 'justifi_rest_routes') ); 
			 
	}
	
	public function justifi_rest_routes() {
		
		/* 
		 * Monitor for Refunds 
		 * @refund_event - https://docs.justifi.tech/api-spec#tag/Events/operation/refundEvent
		 * @authentication - https://docs.justifi.tech/api-spec#tag/Webhook-Delivery
		 *
		 * The method from JustiFi is always 'POST'
		 * The permission callback validates the signature hash from JustiFi
		 * If the permission returns true we get the request body and do the things
		 */
		 
		register_rest_route( 'justifi/v2', '/refund', array(
			'methods'  				=> \WP_REST_Server::EDITABLE,
			'callback' 				=> array( $this, 'justifi_refund' ),
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
	
	public function justifi_refund( $req ) {
		
		$response = json_decode( $req->get_body() );
		$refund['id'] = $response->data->id;
		$refund['payment_id'] = $response->data->payment_id;
		$refund['amount'] = $response->data->amount;
		$refund['reason'] = $response->data->reason;
		$refund['status'] = $response->data->status;
		$refund['trigger'] = $response->event_name;
		$refund['idemp'] = $response->data->metadata->idemp;
		
		$this->process_refund( $refund );
	}
	
	public function process_refund( $refund ) {
		
		$args = array(
			'meta_key'		=> '_justifi_payment_id',
			'meta_value'	=> $refund['payment_id'],
		);
		$orders = wc_get_orders($args);
		
		foreach( $orders as $order ) {
			
			$amount = round( $refund['amount'] / 100, 2);
			$reason = $refund['reason'];
			$order_id = $order->get_id();
			$order_items = $order->get_items();

		}

		/* Here we're checking the post meta to ensure this is a unique refund request */
		$idemp = get_post_meta($order_id, '_justifi_refund_idemp', true);
				 
		/* If the key doesn't match an existing key in the post_meta array, we're good to go */
		if ( !in_array( $refund['idemp'], $idemp  ) ) {

			$jf_refund = wc_create_refund( array(
				'amount'         => $amount,
				'reason'         => $reason,
				'order_id'       => $order_id,
				'line_items'     => $order_items,
				'refund_payment' => false,
				'restock_items'  => false,
			) );
			
		} /* else if ( $refund['status'] == 'succeeded' ) {
			
			/* If the idempotency keys match and the refund succeeds, add an order note *
			$order = wc_get_order( $order_id ); 
			$message = 'Refunded: $' .round( $refund['amount'] / 100, 2). ' via Justifi. ';
			$message .= 'Refund ID: ' .$refund['id'];
			$message .= ' Reason: ';
			$order->add_order_note( $message );
			
		} */
		
		return $order;
		
	}
	
}

new \Justifi_Payments\Webhooks\Justifi_Refunds();