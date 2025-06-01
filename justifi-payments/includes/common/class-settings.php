<?php
/**
 * Add menu items and pages to the WordPress admin.
 *
 * @link       https://bradmessenger.com/
 * @since      1.0.0
 *
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments/admin
 */

namespace Justifi_Payments\Common;

// Allow including common helpers in multiple plugins without breaking things.
if ( class_exists( 'Justifi_Payments\Common\Settings' ) ) {
	return;
}

/**
 * Add menu items and pages to the WordPress admin.
 */
class Settings {
	/**
	 * Loader instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var    Loader
	 */
	protected static $instance = null;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since  1.0.0
	 * @return Loader
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {}

	/*= CUSTOM %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
	/**
	 * Get the buying multipler for the given data.
	 *
	 * @param array $data Diamond criteria.
	 */
	public function get_multiplier_buy( $data = array() ) {
		return get_field( 'multiplier_buy', 'option' );
	}

	/**
	 * An alias for get_multiplier_buy() so that I stop mixing them up.
	 *
	 * @param array $data Diamond criteria.
	 */
	public function get_multiplier_offer( $data = array() ) {
		return $this->get_multiplier_buy( $data );
	}

	/**
	 * Get the selling multipler for the given data.
	 *
	 * @param array $data Diamond criteria.
	 */
	public function get_multiplier_sell( $data = array() ) {
		return get_field( 'multiplier_sell', 'option' );
	}

	/**
	 * An alias for get_multiplier_sell() so that I stop mixing them up.
	 *
	 * @param array $data Diamond criteria.
	 */
	public function get_multiplier_retail( $data = array() ) {
		return $this->get_multiplier_sell( $data );
	}

	/**
	 * Get the URL for the pricing tool.
	 */
	public function get_pricing_tool_url() {
		return get_field( 'pricing_tool', 'option' );
	}
}
