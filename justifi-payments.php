<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link    https://bradmessenger.com/
 * @since   1.0.0
 * @package Justifi_Payments
 *
 * @wordpress-plugin
 * Plugin Name: Justifi Payments
 * Plugin URI:  https://bradmessenger.com/
 * Description: Web Component Shortcodes, Justifi Payment Gateway, Account Status and Transaction History
 * Version:     1.0.0
 * Author:      Brad Messenger
 * Author URI:  https://bradmessenger.com/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: BRad MEssenger
 * Domain Path: /languages
 */

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

/**
 * Settings that are needed in activation hooks.
 */
define( 'JUSTIFI_PAYMENTS_DB_VERSION', '1.0.0' );
define( 'JUSTIFI_PAYMENTS_FILE_PATH', plugin_dir_path( __FILE__ ) );
define( 'JUSTIFI_PAYMENTS_FILE', __FILE__ );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-plugin.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
$justifi = new \Justifi_Payments\Plugin();
