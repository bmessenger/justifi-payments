<?php
/**
 * Various hooks used for injecting HTML into our templates
 *
 * @link       https://bradmessenger.com/
 * @since      1.0.0
 *
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments/Hooks
 */

namespace Justifi_Payments\Hooks;

/**
 * Add menu items and pages to the WordPress admin. 
 */
class Template_Hooks {
	
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		
		add_action( 'justifi_before_dashboard', arrray( $this, 'dahsboard_start'), 10, 2 );
		
	}
	
	public function dashboard_start() {
		
		error_log( print_r( 'bang', true ));
		echo '<div id="justifi-dashboard" class="justifi-things-to-do"><header><h3>Things to do</h3></header>';
		echo '<div class="dash-body">';

	}
	
}