<?php
/**
 * Add a shortcode to sheck the status of a business in Justifi
 *
 * @link      https://bradmessenger.com/
 * @since      1.0.0
 *
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments/shortcodes
 */

namespace Justifi_Payments\Shortcodes;

use Justifi_Payments\Justifi_Helpers;
use Justifi_Payments\Provision_Product;

/**
 * Add menu items and pages to the WordPress admin.
 */
class Account_Status {


	private $status = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->generate_shortcode();
	}

	public function generate_shortcode() {
		add_shortcode( 'Justifi_Account_Status', array( $this, 'justifi_get_status' ) );
	}

	public function justifi_get_status() {
		if ( function_exists( 'psum_current_user_can' ) && ! psum_current_user_can( 'justifi_setup' ) ) {
			return;
		}

		$org_id = Justifi_Helpers::get_org_id();
		$biz = Justifi_Helpers::get_biz_id();
		$user_id = get_current_user_id(); // Current User & Organization Owner

		/* If restriction is in place, exit */
		$options = get_option( 'justifi_config_options' );
		$enabled = get_field( 'enable_justifi', $org_id );
		if ( $enabled != 'yes' && ! empty( $options['account_permissions'] ) ) return '';

		if ( isset( $org_id ) ) {

			$status = get_post_meta( $org_id, '_justifi_account_status', true );
			//$status = $status[0];

			if ( empty( $status ) ) {

				$sub = Justifi_Helpers::get_subaccount__org_id( $org_id ) ? Justifi_Helpers::get_subaccount__org_id( $org_id ) : null;


				if ( $sub == null ) {

					$status = 'Not Created';

				} else {

					$status = Justifi_Helpers::justifi_get_sub_account_status( $sub );

				}

			}

			update_post_meta( $org_id, '_justifi_account_status', $status );
			update_user_meta( $user_id, '_justifi_account_status', $status );

		} else {
			$status = "Organization not found";
		}

		ob_start();

		$notice = '<div class="form-notices">';
		$notice .= '<div class="account-status status-' .sanitize_title( $status ). '">Payments Account: ' .$status. '</div>';
		$notice .= '</div>';

		$notice .= ob_get_clean();

		return $notice;


	}

}

new \Justifi_Payments\Shortcodes\Account_Status();
