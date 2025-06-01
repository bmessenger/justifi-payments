<?php
/**
 * The file that defines the core plugin class.
 *
 * A class definition that includes attributes and functions used across both
 * the public-facing side of the site and the admin area.
 *
 * @link       https://bradmessenger.com/
 *
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments/includes
 */

namespace Justifi_Payments;

/**
 * The core plugin class.
 */
class Plugin {
	/**
	 * Plugin settings
	 *
	 * @var array
	 */
	public static $settings = array(
		'plugin_name' => 'justifi-payments',
		'plugin_slug' => 'Justifi_Payments',
		'cron_hook'   => 'Justifi_Payments_cron',
		'log_prefix'  => 'justifi-payments',
	);

	/**
	 * Initialize the plugin.
	 */
	public function __construct() {
		$this->load_dependencies();
		$this->define_hooks();
	}

	/**
	 * Retrieve plugin settings.
	 *
	 * @param string $key Setting to retrieve.
	 */
	public static function get_setting( $key ) {
		if ( isset( self::$settings[ $key ] ) ) {
			return self::$settings[ $key ];
		}
		return false;
	}

	/**
	 * Generate the asset handle
	 *
	 * @param string $handle Asset handle.
	 */
	public static function asset_handle( $handle ) {
		return self::get_setting( 'plugin_slug' ) . '-' . $handle;
	}

	/**
	 * Generate the asset URL.
	 *
	 * @param string $src Asset filename.
	 * @param string $loc Asset location. Either public or admin.
	 */
	public static function asset_src( $src, $loc = 'public' ) {
		$mime = false === stripos( $src, '.js' ) ? 'css' : 'js';
		return sprintf( '%s%s/%s/%s', plugin_dir_url( __DIR__ ), $loc, $mime, $src );
	}

	/**
	 * Generate the asset version based on the asset's last modified time.
	 *
	 * @param string $src Asset filename.
	 * @param string $loc Asset location. Either public or admin.
	 */
	public static function asset_ver( $src, $loc = 'public' ) {
		$mime = false === stripos( $src, '.js' ) ? 'css' : 'js';
		return filemtime( sprintf( '%s%s/%s/%s', plugin_dir_path( __DIR__ ), $loc, $mime, $src ) );
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @access private
	 */
	private function load_dependencies() {
		
		/* Admin */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-justifi-admin.php';
		
		/* Helpers */
		//require_once plugin_dir_path( __FILE__ ) . 'class-admin.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-frontend.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-justifi-helpers.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-create-business.php';
		//require_once plugin_dir_path( __FILE__ ) . 'class-provision-product.php';
		require_once plugin_dir_path( __FILE__ ) . 'hooks/class-template-hooks.php';
		
		/* Shortcodes */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/shortcodes/class-shortcode-justifi-dashboard.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/shortcodes/class-shortcode-account-status.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/shortcodes/class-shortcode-account-notices.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/shortcodes/class-shortcode-payment-provision-form.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/shortcodes/class-shortcode-business-form.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/shortcodes/class-shortcode-business-details.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/shortcodes/class-shortcode-bank-details.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/shortcodes/class-shortcode-onboarding-form.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/shortcodes/class-shortcode-payment-chart.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/shortcodes/class-shortcode-payment-list.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/shortcodes/class-shortcode-payout-list.php';

		/* Webhooks */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/webhooks/class-justifi-refunds.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/webhooks/class-justifi-sub-accounts.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/webhooks/class-justifi-payment-disputes.php';
		
		/* Payment Gateway */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/gateway/class-justifi-payment-gateway.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/gateway/class-justifi-payment-gateway-ba.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/gateway/class-justifi-gateway-filter.php';
		
		
	}

	/**
	 * Register activation hooks for the plugin.
	 *
	 * @access private
	 */
	private function define_hooks() {
		register_activation_hook( Justifi_Payments_FILE, 'Justifi_Payments\Plugin::activate_plugin' );
		register_deactivation_hook( Justifi_Payments_FILE, 'Justifi_Payments\Plugin::deactivate_plugin' );
		register_uninstall_hook( Justifi_Payments_FILE, 'Justifi_Payments\Plugin::uninstall_plugin' );
	}

	/**
	 * The code that runs during plugin activation.
	 */
	public static function activate_plugin() {
		require_once plugin_dir_path( __FILE__ ) . 'hooks/class-activator.php';
		\Justifi_Payments\Hooks\Activator::activate();
	}

	/**
	 * The code that runs during plugin deactivation.
	 */
	public static function deactivate_plugin() {
		require_once plugin_dir_path( __FILE__ ) . 'hooks/class-deactivator.php';
		\Justifi_Payments\Hooks\Deactivator::deactivate();
	}

	/**
	 * The code that runs during plugin deletion.
	 */
	public static function uninstall_plugin() {
		require_once plugin_dir_path( __FILE__ ) . 'hooks/class-uninstaller.php';
		\Justifi_Payments\Hooks\Uninstaller::uninstall();
	}
	
	
	
}
