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
if ( class_exists( 'Justifi_Payments\Common\Log' ) ) {
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
 * @author     Justifi_Payments <wordpress@bradmessenger.com>
 */
class Log {

	/**
	 * The directory where log files are written. The path should be relative to
	 * the wp_content directory.
	 *
	 * @var string
	 */
	private static $log_dir = '';

	/**
	 * Create the logging directory if it doesn't exist.
	 */
	public function __construct() {}

	/**
	 * Write to a log file in staxx/logs.
	 *
	 * @param string $file    Log file to write to.
	 * @param string $message Log message.
	 */
	public static function log( $file, $message ) {
		// phpcs:disable WordPress.WP.AlternativeFunctions

		$file = sprintf( '%s/%s.log', self::get_log_dir(), $file );
		$log  = fopen( $file, 'a' );

		if ( false !== $log ) {
			if ( PHP_EOL === $message ) {
				fwrite( $log, PHP_EOL );
			} else {
				fwrite( $log, sprintf( '%s %s%s', gmdate( '[d-MY H:i:s e]' ), $message, PHP_EOL ) );
			}
			fclose( $log );
		}

		// phpcs:enable WordPress.WP.AlternativeFunctions
	}

	/**
	 * Write a "log break" to the specified log file;
	 *
	 * @param string $file Log file to write to.
	 */
	public static function log_start( $file ) {
		self::log( $file, PHP_EOL );
		self::log( $file, '------------------------------------------------------------------------------' );
	}

	/**
	 * Write a "log break" to the specified log file;
	 *
	 * @param string $file Log file to write to.
	 */
	public static function log_stop( $file ) {
		self::log( $file, '------------------------------------------------------------------------------' );
	}

	/*= FORMAT-SPECIFIC LOGGING %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
	/**
	 * Log XML errors.
	 *
	 * @param string $file   Log file to write to.
	 * @param array  $errors Array of LibXMLError errors.
	 */
	public static function log_xml_errors( $file, $errors ) {
		foreach ( $errors as $error ) {
			$message = 'XML Parse ';
			switch ( $error->level ) {
				case LIBXML_ERR_WARNING:
					$message .= "warning {$error->code}: ";
					break;
				case LIBXML_ERR_ERROR:
					$message .= "error {$error->code}: ";
					break;
				case LIBXML_ERR_FATAL:
					$message .= "fatal error {$error->code}: ";
					break;
			}

			$message .= sprintf( '%s on line %s', trim( $error->message ), $error->line );

			self::log( $file, $message );
		}
	}

	/*= PRIVATE %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/

	/**
	 * Get the logging directory.
	 */
	private static function get_log_dir() {
		if ( ! empty( self::$log_dir ) ) {
			return self::$log_dir;
		}

		$wp_upload_dir = wp_upload_dir();
		$log_dir       = trailingslashit( $wp_upload_dir['basedir'] ) . 'justifi-payments/logs';

		if ( ! is_dir( $log_dir ) ) {
			wp_mkdir_p( $log_dir );
		}

		self::$log_dir = $log_dir;
		return $log_dir;
	}
}
