<?php
/**
 * Shortcode for adding the main Justifi Dashboard (AKA "Things to Do")
 *
 * @link       https://bradmessenger.com/
 * @since      1.0.0
 *
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments/shortcodes
 */

namespace Justifi_Payments\Shortcodes;

use Justifi_Payments\Justifi_Helpers;
use Justifi_Payments\Hooks\Template_Hooks;

/**
 * Add menu items and pages to the WordPress admin.
 */
class Justifi_Dashboard {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->generate_shortcode();

	}

	public function generate_shortcode() {
		add_shortcode( 'Justifi_Dashboard', array( $this, 'justifi_dashboard_gen' ) );
	}


	public function justifi_dashboard_gen() {
		if ( function_exists( 'psum_current_user_can' ) && ! psum_current_user_can( 'justifi_setup' ) ) {
			return;
		}

		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;
		$registered - $current_user->user_registered;

		// error_log( print_r( $registered, true ) );

		$org_id = Justifi_Helpers::get_org_id();

		/* If restriction is in place, exit */
		$options = get_option( 'justifi_config_options' );
		$enabled = get_field( 'enable_justifi', $org_id );
		if ( $enabled != 'yes' && ! empty( $options['account_permissions'] ) ) return '';

		$biz_id = Justifi_Helpers::get_biz_id();
		$bank_id = Justifi_Helpers::get_ba_id();


		if ( $biz_id ) $business = Justifi_Helpers::justifi_get_business( $biz_id );
		Justifi_Helpers::get_subaccount__org_id( $org_id ) ? $sub_account_id = Justifi_Helpers::get_subaccount__org_id( $org_id ) :  $sub_account_id = null;

		//if ( $biz_id && $sub_account_id == null ) {
			if ( !( empty( $business['data']['associated_accounts'][0]['id'] ) ) ) {
				$sub_account_id = $business['data']['associated_accounts'][0]['id'];
				// error_log( print_r( $sub_account_id, true ) );
				update_post_meta( $org_id, 'justifi_sub_account_id', $sub_account_id );
			}
		//}

		if ( !( empty( $business['data']['bank_accounts'][0]['id'] ) ) ) {
			$bank_account_id = $business['data']['bank_accounts'][0]['id'];
			update_post_meta( $org_id, 'justifi_bank_account_id', $bank_account_id );

		}


		//$status = Justifi_Helpers::justifi_get_sub_account_status( $sub_account_id );
		$submitted = Justifi_Helpers::justifi_business_form_submitted( $biz_id );

		//$status = 'pending';

		if ( $submitted && isset( $bank_id ) && !isset( $sub_account_id ) ) {

			$pending = $this->justifi_pending( $biz_id, $sub_account_id ); // Check pending status
			return $pending;

		} else {

			$dashboard = '';

			ob_start();
			require Justifi_Payments_FILE_PATH . 'includes/templates/dashboard/dashboard-before.php';
			$dashboard .= ob_get_clean();

			$dashboard .= $this->justifi_connect( $biz_id );
			//$dashboard .= $this->justifi_onboarding( $biz_id );
			//$dashboard .= $this->justifi_business_form( $biz_id );
			$dashboard .= $this->justifi_payment_provision_form( $biz_id );

			ob_start();
			require Justifi_Payments_FILE_PATH . 'includes/templates/dashboard/dashboard-after.php';
			$dashboard .= ob_get_clean();

			return $dashboard;

		}



	}

	public function justifi_connect( $biz_id ) {

		ob_start();
		require Justifi_Payments_FILE_PATH . 'includes/templates/dashboard/dashboard-connect.php';
		return ob_get_clean();

	}

	public function justifi_onboarding( $biz_id ) {

		$ba_id = Justifi_Helpers::get_ba_id();

		ob_start();
		require Justifi_Payments_FILE_PATH . 'includes/templates/dashboard/dashboard-onboarding.php';
		return ob_get_clean();

	}

	public function justifi_business_form( $biz_id ) {

		$submitted = Justifi_Helpers::justifi_business_form_submitted( $biz_id );

		ob_start();
		require Justifi_Payments_FILE_PATH . 'includes/templates/dashboard/dashboard-business.php';
		return ob_get_clean();

	}

	public function justifi_payment_provision_form( $biz_id ) {

		$submitted = Justifi_Helpers::justifi_business_form_submitted( $biz_id );

		ob_start();
		require Justifi_Payments_FILE_PATH . 'includes/templates/dashboard/dashboard-payment-provision.php';
		return ob_get_clean();

	}

	public function justifi_pending( $biz_id, $sub_account_id ) {

		ob_start();
		require Justifi_Payments_FILE_PATH . 'includes/templates/dashboard/dashboard-pending.php';
		return ob_get_clean();

	}

}

new \Justifi_Payments\Shortcodes\Justifi_Dashboard();