<?php
/**
 * Register all actions and filters for the plugin
 *
 * @link       https://bradmessenger.com/
 * @since      1.0.0
 *
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments/includes
 */

namespace Justifi_Payments\Common;

// Allow including common helpers in multiple plugins without breaking things.
if ( class_exists( 'Justifi_Payments\Common\Admin_Menu' ) ) {
	return;
}

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments/includes/helpers
 * @author     Brad Messenger <wordpress@bradmessenger.com>
 */
class Admin_Menu {
	/**
	 * Add a separator to the WordPress admin. Use wp_rand() to ensure that we
	 * don't overwrite any existing menu items.
	 *
	 * @param integer $position Menu position.
	 */
	public static function menu_separator( $position ) {
		global $menu;

		// Everything in the menu should have indexes less than 1000.
		$rand = wp_rand( 1000 );

		// Zero-pad the menu position to keep the sparator as close to the requested
		// position as possible, without overwriting anything that's already there.
		$menu[ $position . '.00' . $rand ] = array( '', 'read', 'separator' . $rand, '', 'wp-menu-separator' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride

		ksort( $menu );
	}
}
