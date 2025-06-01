<?php
/**
 * The frontend-specific functionality of the plugin.
 *
 * @link       https://bradmessenger.com/
 * @since      1.0.0
 *
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments/Frontend
 */

namespace Justifi_Payments;

/**
 * The frontend-specific functionality of the plugin.
 *
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments/Frontend
 * @author     Justifi_Payments, Inc. <wordpress@bradmessenger.com>
 */
class Frontend {
	/**
	 * Initialize class configuration.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'script_loader_tag', array( $this, 'add_module_attribute' ) , 10, 3 );
	}

	/**
	 * Register the JavaScript for the frontend area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		$stmp = filemtime( plugin_dir_path( __DIR__ ) . 'public/css/public.css' );
		wp_enqueue_style( Plugin::get_setting( 'plugin_slug' ) . '-public', plugin_dir_url( __DIR__ ) . 'public/css/public.css', array(), $stmp, 'all' );

		$stmp = filemtime( plugin_dir_path( __DIR__ ) . 'public/js/public.js' );
		wp_enqueue_script( Plugin::get_setting( 'plugin_slug' ) . '-public', plugin_dir_url( __DIR__ ) . 'public/js/public.js', array( 'jquery' ), $stmp, false );
		
		/* Web Component Requires */
		if ( !is_page('dashboard') ) {
			/* wp_enqueue_style( Plugin::get_setting( 'plugin_slug' ) . '-justify-webcomponents-css', 'https://cdn.jsdelivr.net/npm/@justifi/webcomponents@4.8.2/dist/webcomponents/webcomponents.css', array(), '4.8.2', 'all' );
			wp_enqueue_script( Plugin::get_setting( 'plugin_slug' ) . '-justify-webcomponents-esm', 'https://cdn.jsdelivr.net/npm/@justifi/webcomponents@4.8.2/dist/webcomponents/webcomponents.esm.js', array( 'jquery' ), '4.8.2', false );
			wp_enqueue_script( Plugin::get_setting( 'plugin_slug' ) . '-justify-webcomponents', 'https://cdn.jsdelivr.net/npm/@justifi/webcomponents@4.8.2/dist/webcomponents/webcomponents.js', array( 'jquery' ), '4.8.2', false ); */
			
			wp_enqueue_style( Plugin::get_setting( 'plugin_slug' ) . '-justify-webcomponents-css', 'https://cdn.jsdelivr.net/npm/@justifi/webcomponents@4.16.0/dist/webcomponents/webcomponents.css', array(), '4.16.0', 'all' );
			wp_enqueue_script( Plugin::get_setting( 'plugin_slug' ) . '-justify-webcomponents-esm', 'https://cdn.jsdelivr.net/npm/@justifi/webcomponents@4.16.0/dist/webcomponents/webcomponents.esm.js', array( 'jquery' ), '4.16.0', false );
			wp_enqueue_script( Plugin::get_setting( 'plugin_slug' ) . '-justify-webcomponents', 'https://cdn.jsdelivr.net/npm/@justifi/webcomponents@4.16.0/dist/webcomponents/webcomponents.js', array( 'jquery' ), '4.16.0', false );
		}
		
		wp_enqueue_script( Plugin::get_setting( 'plugin_slug' ) . '-justifi-ajax', plugin_dir_url( __DIR__ ) . 'public/js/justifi-ajax.js', array(), false, true );
		wp_localize_script( Plugin::get_setting( 'plugin_slug' ) . '-justifi-ajax', 'justifi_ajax', array( 
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'user_id'  => get_current_user_id()
			//'security' => wp_create_nonce( 'justifi-nonce' ) 
		));
		
	}
	
	public function add_module_attribute($tag, $handle, $src) {
		if ( 'Justifi_Payments-justify-webcomponents-esm' === $handle ) {
			$tag = '<script type="module" src="' . esc_url($src) . '"></script>';
		}
		return $tag;
		//error_log( print_r( $tag, true ) );
		//error_log( print_r( $handle, true ) );
		//error_log( print_r( $src, true ) );
	}
}
new \Justifi_Payments\Frontend();
