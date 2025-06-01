<?php
/**
 * The payment Justifi Payment Gateway for WooCommerce
 *
 * @link       https://bradmessenger.com/
 * @since      1.0.0
 *
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments/gateway
 */

namespace Justifi_Payments\Gateway;

use Justifi_Payments\Justifi_Helpers;
use WC_Payment_Gateway;
use WC_Order;
use WC_Session_Handler;




// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}

/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter( 'woocommerce_payment_gateways', 'Justifi_Payments\Gateway\justifi_add_gateway_class');
function justifi_add_gateway_class( $gateways ) {
	$gateways[] = '\Justifi_Payments\Gateway\WC_Justifi_Gateway'; // your class name is here
	//error_log( print_r( $gateways, true) );
	return $gateways;
}


/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'Justifi_Payments\Gateway\justifi_init_gateway_class');
function justifi_init_gateway_class() {
	
	
	class WC_Justifi_Gateway extends WC_Payment_Gateway { 
		
	
		 /**
		  * Class constructor
		  */
		 public function __construct() {
	
			 $this->id = 'justifi_payment_gateway';
			 $this->method_title = 'Justifi';
			 $this->method_description = 'Enable to accept credit card payments using the Justifi Payment Processor.<br>In order to obtain a Client Key and Client Secret you need to have an account set up with Justifi.';
			 /* $this->icon = plugin_dir_url( dirname( __FILE__ ) ) . '../public/media/justifi-icon_72x72.png'; */
			 $this->has_fields = true;
			 
			 $this->init_form_fields();
			 $this->init_settings();
			 
			 $this->title = $this->get_option( 'title' );
			 $this->description = $this->get_option( 'description' );
			 $this->enabled = $this->get_option( 'enabled' );
			 $this->testmode = 'yes' === $this->get_option( 'testmode' );
			 $this->supports = array('products', 'refunds');
			 //$this->private_key = $this->testmode ? $this->get_option( 'test_private_key' ) : $this->get_option( 'private_key' );
			 //$this->publishable_key = $this->testmode ? $this->get_option( 'test_publishable_key' ) : $this->get_option( 'publishable_key' );
			 
			 // This action hook saves the settings
			 add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			 
			 // Enqueue Required Scripts 
			 add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
			 
			 // We need custom JavaScript to obtain a token
			 //add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
			 
			 // You can also register a webhook here
			 // add_action( 'woocommerce_api_{webhook name}', array( $this, 'webhook' ) );

		 }

		/**
		  * Plugin options, we deal with it in Step 3 too
		  */
		 public function init_form_fields(){
		 	
			 /* Grab our API keys - These should already be set in the Justifi Plugin Settings */
			 get_option('justifi_config_options')['client_key'] ? $client_id = get_option('justifi_config_options')['client_key'] : $client_id = '';
			 get_option('justifi_config_options')['client_secret'] ? $client_secret = get_option('justifi_config_options')['client_secret'] : $client_secret = '';
			 
			 $this->form_fields = array(
				'enabled' => array(
					'title'         => 'Enable/Disable',
					'type'          => 'checkbox',
					'label'         => 'Enable Justifi Gateway',
					'default'       => 'no',
				),
			 	'title' => array(
			 		'title' => __( 'Justifi', 'woocommerce' ),
			 		'type' => 'text',
			 		'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
			 		'default' => __( 'Credit Card', 'woocommerce' ),
			 		'desc_tip' => true,
			 	),
			 	'description' => array(
			 		'title' => __( 'Customer Message', 'woocommerce' ),
			 		'type' => 'textarea',
			 		'default' => ''
			 	),
				 'client_key' => array(
					 'title'       => 'Client Key',
					 'type'        => 'text',
					 'default'	   => $client_id,
					 'description' => 'To obtain your Client Key and Client Secret you must have an account set up within Justifi.'
				 ),
				 'client_secret' => array(
					 'title'       => 'Client Secret',
					 'type'        => 'password',
					 'default'	   => $client_secret,
				 ),
				 'testmode' => array(
					  'title'       => 'Test mode',
					  'label'       => 'Enable Test Mode',
					  'type'        => 'checkbox',
					  'description' => 'Place the payment gateway in test mode using test API keys.',
					  'default'     => 'yes',
					  'desc_tip'    => true,
				  ),
			 );
	
		 }

		/**
		 * You will need it if you want your custom credit card form, Step 4 is about it
		 */
		public function payment_fields() {
			
			/*
			 * A bit of climbing down the ladder here to get the Sub Account ID required to process a payment.
			 * Access the global woocommerce query and get the items in the cart.
			 * Get the product ID of the items (we only need one and it shouldn't matter if there are multiple items) -
			 * - the product_id will be tied to the same owner / author.
			 * Use the product_id to fetch the event.
			 * Get the organizer of the event.
			 * Get the Sub Account ID from the organizer.
			 */
			global $woocommerce;
			$items = $woocommerce->cart->get_cart(); 
			foreach( $items as $item ) {
				$ticket_id = $item['product_id']; // Grab the "Product" id from the item, even if there are multiple it shouldn't matter
			}
			$event = tribe_get_event( $ticket_id );
			$author = $event->post_author;
			$sub_account = Justifi_Helpers::get_subaccount__author_id( $author );
			$status = Justifi_Helpers::justifi_get_sub_account_status( $sub_account );

			if ( $this->description ) {
				if ( $this->testmode ) {
					$this->description .= ' TEST MODE ENABLED. In test mode, you can use the card numbers listed in <a href="https://docs.justifi.tech/api-spec#section/Testing" target="_blank">documentation</a>.';
					$this->description  = trim( $this->description );
				}
				echo wpautop( wp_kses_post( $this->description ) );
			}
									
			if ( $status && $status == 'enabled' ) {
				
				echo '<fieldset id="wc-' . esc_attr( $this->id ) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">';
				
				do_action( 'woocommerce_credit_card_form_start', $this->id );
				
				echo '<div class="form-row form-row-wide">
						<label>Card Number <span class="required">*</span></label> 
						<div class="cc-validate">
							<input id="justifi_cc" type="text" autocomplete="off" name="justifi_cc" placeholder="xxxx xxxx xxxx xxxx" max="16" min="15">
							<span id="status"></span>
						</div>
					</div>
					<div class="flex_row 3-col">
						<div class="flex_column av_one_fourth first">
							<label>Expiry Month <span class="required">*</span></label>
							<input id="justifi_expmonth" name="justifi_expmonth" type="text" autocomplete="off" placeholder="MM"><br>
						</div>
						<div class="flex_column av_one_fourth">
							<label>Expiry Year <span class="required">*</span></label>
							<input id="justifi_expyear" type="text" name="justifi_expyear" autocomplete="off" placeholder="YYYY">
						</div>
						<div class="flex_column av_one_fourth last">
							<label>Card Security Code (CVV) <span class="required">*</span></label>
							<input id="justifi_cvv" type="password" name="justifi_cvv" autocomplete="off" placeholder="***" max="3">
						</div>
					</div>
					<div class="clear"></div>';
				
				do_action( 'woocommerce_credit_card_form_end', $this->id );
				
				echo '<div class="clear"></div></fieldset>';
			} else {
				
				echo '<div class"error notice no-subaccount">
						<p>This event organizer is not ready to accept payments.</p>
					</div>';
					
			}
		}

		/*
		 * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
		 */
		 public function payment_scripts() {
			 
		 
			 wp_enqueue_script( 'justifi-cc-validate', plugin_dir_url( dirname( __DIR__ ) ) . 'public/js/validate.js', array( 'jquery' ), '1.0', false );
		 
		 	// do not work with card detailes without SSL unless your website is in a test mode
			  if( ! $this->testmode && ! is_ssl() ) {
				  return;
			  }
		 
		 }

		/*
		  * Fields validation, more in Step 5
		 */
		public function validate_fields() {
			
			$ret = true;
			
			if( empty( $_POST[ 'justifi_cc' ]) ) {
				
				wc_add_notice(  '<strong>Card Number</strong> is required', 'error' );
				$ret = false;
				
			} else {
				
				$credit_card_number = strval( $_POST['justifi_cc'] );
				$sum = 0;
				$flag = 0;
			
				for ($i = strlen($credit_card_number) - 1; $i >= 0; $i--) {
				  $add = $flag++ & 1 ? $credit_card_number[$i] * 2 : $credit_card_number[$i];
				  $sum += $add > 9 ? $add - 9 : $add;
				}
			
				if ( $sum % 10 != 0 ) {
					wc_add_notice(  '<strong>Credit Card</strong> Invalid Number', 'error' );
					$ret = false;
				}
					
			}
			
			if( empty( $_POST['justifi_expmonth'] ) ) {
				wc_add_notice(  '<strong>Card Expiry Month</strong> is required', 'error' );
				$ret = false;
			}
			
			if( empty( $_POST['justifi_expyear'] ) ) {
				wc_add_notice(  '<strong>Card Expiry Year</strong> is required', 'error' );
				$ret = false;
			}
			
			if( empty( $_POST[ 'justifi_cvv' ]) ) {
				wc_add_notice(  '<strong>Card Security Code (CVV)</strong> is required', 'error' );
				$ret = false;
			}
			
			return $ret;
			
		}

		/*
		 * We're processing the payments here, everything about it is in Step 5
		 */
		public function process_payment( $order_id ) {
			
			/* This is the same block of code used to get the sub account id 
			 * used in the payment fields method - TO DO: keep it dry 
			 */
			
			global $woocommerce;
			$order = new WC_Order( $order_id );
			$dollars = $order->get_total();
			$total = bcmul($dollars, 100);
			
			$fees = $woocommerce->cart->get_fees(); 
			$total_fees = bcmul( $fees['convenience-fees']->total, 100 );
			
			$items = $woocommerce->cart->get_cart(); 
			foreach( $items as $item ) {
				$ticket_id = $item['product_id'];
			}
						
		    $ticket_meta = get_post_meta( $ticket_id  );	
			$event_id = absint( $ticket_meta[ '_tribe_wooticket_for_event' ][0] );	
			
			$event_title = get_the_title( $event_id );	
			$ticket_title = get_the_title( $ticket_id );	
			
			$event = tribe_get_event( $event_id );
			$author = $event->post_author;
			
			$sub_account = Justifi_Helpers::get_subaccount__author_id( $author );
				
			
			$url = 'https://api.justifi.ai/v1/payments';
			$ik = strtotime("now");
			$token = Justifi_Helpers::get_access_token();
			
			$payment_details = array(
				"amount" 					=> $total,
				"currency"					=> "usd",
				"capture_strategy"			=> "automatic",
				"application_fee_amount"	=> $total_fees,
				"payment_method"	=> array(
					"card"				=> array(
						"name"					=> $_POST['billing_first_name'],
						"number"				=> $_POST['justifi_cc'],
						"verification"			=> $_POST['justifi_cvv'],
						"month"					=> $_POST['justifi_expmonth'],
						"year"					=> $_POST['justifi_expyear'],
						"address_postal_code" 	=> $_POST['billing_postcode'],
					),
				),
				"metadata"			=> array(
					"order_id"			=> $order_id,
					"event_title"		=> $event_title,
					"event_id"			=> $event_id,
					"ticket_title"		=> $ticket_title,
					"ticket_id"			=> $ticket_id,
				),
			);
			
			//error_log( print_r( $sub_account, true ) );
			//error_log( print_r( $payment_details, true ) );
			
			$response = wp_remote_post( $url, array(
				'headers' => array(
					'Content-Type' 	=> 'application/json',
					'Idempotency-Key'	=> $ik,
					'Authorization' => 'Bearer ' . $token,
					'Sub-Account'	=> $sub_account,
				),
				'timeout'	=> 60,
				'body' 		=> wp_json_encode( $payment_details ),
			) );
			
			if ( is_wp_error( $response ) ) {
				return false;
			}
			
			$response_body = wp_remote_retrieve_body( $response );
			$decoded = json_decode($response_body, true); 
						
			if ( array_key_exists( 'error', $decoded ) ) {
				
				wc_add_notice(  $decoded['error']['message'], 'error' );
				
				$note = 'Order Failed: ' .$decoded['error']['code']. '. ' .$decoded['error']['message'];
				$order->update_status( 'failed', __( $note, 'woocommerce' ) );
				
				return array(
					'result'   => 'failure',
					'redirect' => '',
				);
				
			} else {
			
				$order->update_meta_data( '_justifi_payment_id', $decoded['id'] );	
				$order->payment_complete();
				$woocommerce->cart->empty_cart();
				
				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order ),
				);
			
			}

		 }
		 
		public function process_refund( $order_id, $amount = null, $reason = '' ) {
			
			/* Get the Order */
			$order = wc_get_order( $order_id );
			
			/* Make the POST request to JustiFi */
			$refund = $this->refund_order( $order, $amount, $reason );
						
			/* If the Refund is successful update the order, status and add the refund id to the order meta */
			if ( $refund['data']['status'] == 'succeeded' ) {
				
				$order = wc_get_order( $order_id );
				
				$message = 'Refunded: $' .round( $refund['data']['amount'] / 100, 2). ' via Justifi - ';
				$message .= 'Refund ID: ' .$refund['id']. ' - ';
				$refund['data']['description'] ? $message .= 'Reason: ' .$refund['data']['description'] : $message .= 'Reason: ' .$refund['data']['reason'];
				
				$order->add_order_note( $message );
				
				/* 
				 * @ToDo - check back to see if $order->update_status( 'refunded' ) works yet.  
				 * Known bug / roadblock in WP
				 */
				global $wpdb;
				$table_name = $wpdb->prefix .'posts';
				//error_log( print_r( $table_name, true ));
				
				//$status_update = $wpdb->update( $table_name, array( 'post_status' => 'wc-refunded' ), array( 'ID' => $order_id ) );
				//$order->update_status( 'refunded' );
				//error_log( print_r( $status_update, true ));
				
				$order->update_meta_data( '_justifi_refund_id', $refund['id'] );
				
				return true;
				
			} else {
				
				return new \WP_Error( 'refund_failed', __( 'Refund failed. Please try again or reach out to support.', 'woocommerce' ) );
			
			}
		}
		
		public function refund_order( $order, $amount, $reason ) {
			
			$payment_id = get_post_meta( $order->get_id(), '_justifi_payment_id');
			$url = 'https://api.justifi.ai/v1/payments/' .$payment_id[0]. '/refunds';
			$ik = 'refund-request-' .strtotime("now");
			$token = Justifi_Helpers::get_access_token();
				
			/* 
			 * We want to prevent duplicate refunds (One with Justifi and one with Woo),
			 * so we add an idempotency key to ensure each refund is unique.  This also allows
			 * us to reference orders with multiple refunds.
			 * Instead of replacing the post_meta with the new key we add to the array.
			 */
			
			/* Get the current post meta, ensure it's an array */
			$current_idemp = get_post_meta( $order->get_id(), '_justifi_refund_idemp', true);
			
			if ( empty( $current_idemp ) ) {
				$current_idemp = array(); /* Set a new array if empty */
			}
			if ( ! is_array( $current_idemp ) ) {
				return; /* Bail if it's not an array */
			}
			
			/* Generate our random idempotencey key */
			$idemp = wp_generate_password( 32, false, false );
			
			/* Append the key to the post_meta array */
			$current_idemp[] = $idemp;
			
			/* Update the post meta with the new array */
			update_post_meta( $order->get_id(), '_justifi_refund_idemp', $current_idemp );
			
			/* Set up the args for our API request */
			$body = array(
				'amount'		=> bcmul($amount, 100),
				'reason'		=> 'customer_request', // must be one of 3 ( "duplicate" "fraudulent" "customer_request" )
				'description'	=> $reason,
				"metadata"			=> array(
					"order_id"		=> $order->get_id(),
					"idemp"			=> $idemp,
				),
			);
			
			
			/* Make the API Request */
			$response = wp_remote_post( $url, array(
				'headers' => array(
					'Content-Type' 	=> 'application/json',
					'Idempotency-Key'	=> $ik,
					'Authorization' => 'Bearer ' . $token,
				),
				'timeout'	=> 60,
				'body' 		=> wp_json_encode( $body ),
			) );
						
			/* Get and parse the response */
			$response_body = wp_remote_retrieve_body( $response );
			$decoded = json_decode( $response_body, true ); 

			//error_log( print_r( $decoded, true ));
			
			if ( is_wp_error( $decoded ) ) {
				return false;
			}
			
			return $decoded;
			
		}

		/*
		 * In case you need a webhook, like PayPal IPN etc
		 */
		public function webhook() {


					
		 }
	 }
	 
	 $gw = new \Justifi_Payments\Gateway\WC_Justifi_Gateway();
	 
}