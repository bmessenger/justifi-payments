<?php
/**
 * A simple cron handler for all cron tasks needed by the plugin.
 *
 * @link       https://bradmessenger.com/
 * @since      1.0.0
 *
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments/includes/shopify
 */

namespace Justifi_Payments;

use Justifi_Payments\Plugin;
use Justifi_Payments\Common\Log;

/**
 * A simple cron handler for all cron tasks needed by the plugin.
 */
class Cron {
	/**
	 * Cron hook used in wp_schedule_task().
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $hook = '';

	/**
	 * Log file to use for logging.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $log = '';

	/**
	 * Class instance.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    Justifi_Payments\Cron
	 */
	protected static $instance = null;

	/**
	 * Ensure there is only a single instance of the class.
	 *
	 * @since  1.0.0
	 * @return Justifi_Payments\Cron
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize class configuration.
	 */
	public function __construct() {
		$this->hook = Plugin::get_setting( 'cron_hook' );
		$this->log  = sprintf( '%s-cron', Plugin::get_setting( 'log_prefix' ) );
	}

	/**
	 * Set up hooks and filters used by this class.
	 */
	public function init() {
		add_action( $this->hook, array( $this, 'cron_run' ), 10, 2 );
	}

	/*= CRON FUNCTIONS %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
	/**
	 * Prevent cron from running multiple jobs from the same module concurrently.
	 *
	 * @param string $class  The class containing the callback method.
	 * @param string $method The callback method to be run.
	 */
	public function cron_run( string $class, string $method ) {
		if ( ! class_exists( $class ) || ! method_exists( $class, $method ) ) {
			Log::log( $this->log, sprintf( 'Method does not exist: %s::%s()', $class, $method ) );
			return;
		}

		if ( get_transient( $this->hook . '_running' ) ) {
			return;
		}

		set_transient( $this->hook . '_running', true, 5 * MINUTE_IN_SECONDS );
		try {
			$instance = new $class();
			$instance->{$method}();
		} catch ( \Throwable $t ) {
			Log::log( $this->log, $t->getMessage() );
		}
		delete_transient( $this->hook . '_running' );
	}
}
add_action( 'plugins_loaded', array( \Justifi_Payments\Cron::get_instance(), 'init' ) );
