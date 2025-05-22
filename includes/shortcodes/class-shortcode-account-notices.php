<?php
/**
 * Add a shortcode to show notices of account changes
 *
 * @link       https://bradmessenger.com/
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
class Account_Notices {


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
		add_shortcode( 'Justifi_Account_Notices', array( $this, 'justifi_get_notices' ) );
	}

	public function justifi_get_notices() {
		if ( function_exists( 'psum_current_user_can' ) && ! psum_current_user_can( 'justifi_setup' ) ) {
			return;
		}

		$org_id = Justifi_Helpers::get_org_id();

		/* If restriction is in place, exit */
		$options = get_option( 'justifi_config_options' );
		$enabled = get_field( 'enable_justifi', $org_id );
		if ( $enabled != 'yes' && ! empty( $options['account_permissions'] ) ) return '';

		$counter = 0;
		$notice_meta = get_post_meta( $org_id, '_organization_notices', true );
		$notice_list = '<ul class="notice-list">';

		if ( is_array( $notice_meta ) ) {
			foreach( $notice_meta as $notice ) {
				if ( $notice['unread'] == true ) {
					$counter++;
					$notice_list .= '<li class="notice ' .sanitize_title( $notice['type'] ) .'"><h5>' . $notice['type'] . '</h5>' .$notice['message']. '<span class="dismiss-notice" data-notice-id="' .$notice['id']. '" data-org-id="' .$org_id. '" data-nonce="' .wp_create_nonce( 'dismiss-notice' ). '"><i class="fa fa-times" aria-hidden="true"></i></span></li>';
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

		$html = '<div class="account-notices">';
		$html .= '<div class="alerts"><i class="fas fa-bell"></i> '.$counter. '</div>';
		if ( $counter ) {
			$html .= '<div class="notices">';
			$html .= '<div class="loader"><img src="' .get_stylesheet_directory_uri(). '/icons/ajax-loading.gif" alt="Ajax Loading GIF" width="60px" height="60px"></div>';
			$html .= $notice_list;
			$html .= '<button class="close-list btn btn-primary">Close</button>';
			$html .= '</div>';
		}
		$html .= '</div>';

		$html .= ob_get_clean();

		return $html;

	}

}

new \Justifi_Payments\Shortcodes\Account_Notices();
