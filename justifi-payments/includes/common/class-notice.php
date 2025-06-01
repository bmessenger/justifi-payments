<?php
/**
 * Add menu items and pages to the WordPress admin.
 *
 * @link       https://bradmessenger.com/
 * @since      1.0.0
 *
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments/common
 */

namespace Justifi_Payments\Common;

// Allow including common helpers in multiple plugins without breaking things.
if ( class_exists( 'Justifi_Payments\Common\Notice' ) ) {
	return;
}

/**
 * Add menu items and pages to the WordPress admin.
 */
class Notice {
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {}

	/**
	 * Add a notice to be displayed.
	 *
	 * @since 1.0.0
	 * @param string $key     Notice key.
	 * @param string $status  Notice status (e.g., success, error).
	 * @param string $message Notice message.
	 */
	public static function add_notice( $key, $status, $message ) {
		$notices = self::get_notices();

		$notices[] = array( $key, $message, $status );

		update_option( 'justifi_notices', wp_json_encode( $notices ) );
	}

	/**
	 * Get stored notices.
	 *
	 * @since 1.0.0
	 */
	public static function get_notices() {
		return json_decode( get_option( 'justifi_notices', '[]' ), true );
	}

	/**
	 * Print notices.
	 *
	 * @since 1.0.0
	 * @param array $notices On-demand notices to be merged with saved notices.
	 */
	public static function print_notices( $notices = array() ) {
		$saved   = self::get_notices();
		$notices = array_merge( $saved, $notices );

		echo '<h2 class="justifi-notice-anchor"></h2>';

		foreach ( $notices as $notice ) {
			?>

			<div id="setting-error-<?php echo esc_attr( $notice[0] ); ?>" class="notice notice-<?php echo isset( $notice[2] ) ? esc_attr( $notice[2] ) : 'success'; ?> settings-error is-dismissible">
				<p><strong><?php echo wp_kses_post( $notice[1] ); ?></strong></p>
			</div>

			<?php
		}

		update_option( 'justifi_notices', wp_json_encode( array() ) );
	}
}
