<?php
/**
 * Fired during plugin activation
 *
 * @link       https://bradmessenger.com/
 * @since      1.0.0
 *
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments/includes
 */

namespace Justifi_Payments\Hooks;

use DateTime;
use DateTimeZone;

use Justifi_Payments\Plugin;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments/includes
 * @author     Justifi_Payments, Inc. <wordpress@bradmessenger.com>
 */
class Activator {
	/**
	 * Check if database updates are needed.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		if ( Justifi_Payments_DB_VERSION !== get_site_option( 'justifi_db_version' ) ) {
			// self::create_tables();
		}

		$hook = Plugin::get_setting( 'cron_hook' );
		$date = new DateTime( 'today midnight +5 minutes', new DateTimeZone( wp_timezone_string() ) );

		$args = array( '\Justifi_Payments\Some_Class', 'method_to_call' );
		if ( ! wp_next_scheduled( $hook, $args ) ) {
			// wp_schedule_event( $date->format( 'U' ), 'daily', $hook, $args );
		}
	}

	/**
	 * Create the tables used by this plugin.
	 *
	 * @since 1.0.0
	 */
	private static function create_tables() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		global $wpdb;

		$table_name      = $wpdb->prefix . 'justifi';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			`id` VARCHAR(255) NOT NULL COMMENT 'ID',
			`stmp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) $charset_collate;";
		dbDelta( $sql );

		add_option( 'justifi_payments_db_version', Justifi_Payments_DB_VERSION );
	}
}
