<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://bradmessenger.com/
 * @since      1.0.0
 *
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments/includes
 */

namespace Justifi_Payments\Hooks;

use Justifi_Payments\Plugin;

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments/includes
 * @author     Justifi_Payments, Inc. <wordpress@bradmessenger.com>
 */
class Deactivator {
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {
		$hook      = Plugin::get_setting( 'cron_hook' );
		$schedules = array(
			array( '\Justifi_Payments\Class_Name', 'method_to_call' ),
		);

		foreach ( $schedules as $args ) {
			$timestamp = wp_next_scheduled( $hook, $args );
			wp_unschedule_event( $timestamp, $hook, $args );
		}
	}
}
