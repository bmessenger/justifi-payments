<?php
/**
 * Justifi Payment Gateway Filter (unsets other Gateways)
 *
 * @link       https://bradmessenger.com/
 * @since      1.0.0
 *
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments/gateway
 */

namespace Justifi_Payments\Gateway;
use Justifi_Payments\Justifi_Helpers;

class Gateway_Filter {
	
	public function __construct() {
		
		add_filter('woocommerce_available_payment_gateways', array( $this, 'filter_gateways') );
	
	}
	
	public function filter_gateways( $available_gateways ) {
		
		// Is the global WooCommerce object available
		if ( \WC()->cart ) :
			
			// Get the items in the cart
			$items = WC()->cart->get_cart(); 
			
			$ticket_id = '';
			
			// Grab the "Product" id from the item (all the same, just need one)
			foreach ( $items as $item ) {
				$ticket_id = $item['product_id']; 
			}
			
			$event = tribe_get_event( $ticket_id );  // get the event asociated with the ticket (product id)
			$author = $event->post_author; // get the author of the event (Organizer)
			$sub_account = Justifi_Helpers::get_subaccount__author_id( $author ); // get the Subaccount ID assigned by Justifi
			$status = Justifi_Helpers::justifi_get_sub_account_status( $sub_account ); // get status of the subaccount
			
			// Disable Paypal gateways for this session if Justifi is enabled
			// "created" "submitted" "information_needed" "rejected" "enabled" "disabled" "archived"
			if ( $status && $status == 'enabled' ) {
				unset( $available_gateways['ppcp-gateway'] );
				unset( $available_gateways['ppcp-credit-card-gateway'] );
			} else {
				unset( $available_gateways['justifi_payment_gateway'] );
			}
			
			//error_log( print_r( $status, true ) );
				
		endif;
	
		return $available_gateways;
	}
	
	
	
}

new \Justifi_Payments\Gateway\Gateway_Filter();