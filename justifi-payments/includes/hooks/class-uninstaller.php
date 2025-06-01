<?php
/**
 * Fired during plugin deletion.
 *
 * @link       https://bradmessenger.com/
 * @since      1.0.0
 *
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments/includes
 */

namespace Justifi_Payments\Hooks;

/**
 * Fired during plugin deletion.
 *
 * This class defines all code necessary to run during the plugin's deletion.
 *
 * @since      1.0.0
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments/includes
 * @author     Justifi_Payments, Inc. <wordpress@bradmessenger.com>
 */
class Uninstaller {
	/**
	 * Delete tables created by this plugin.
	 *
	 * @since 1.0.0
	 */
	public static function uninstall() {
		global $wpdb;

		$tables = array();

		foreach ( $tables as $table ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %s', $table ) );
		}

		delete_option( 'justifi_db_version' );
	}
}
