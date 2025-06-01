<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://bradmessenger.com/
 * @since      1.0.0
 *
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments/Admin
 */

//namespace Justifi_Payments;

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments/Admin
 * @author     Justifi_Payments, Inc. <wordpress@bradmessenger.com>
 */
class Justifi_Admin {
	/**
	 * Initialize class configuration. Calling most of our hooks here.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_pages' ), 20 );
		add_action( 'admin_init', array( $this, 'justifi_register_settings' ) );

		add_action( 'add_meta_boxes', array( $this, 'justifi_business_id' ) );
		add_action( 'add_meta_boxes', array( $this, 'justifi_bank_account_id' ) );
		add_action( 'add_meta_boxes', array( $this, 'justifi_sub_account_id' ) );
		add_filter('acf/prepare_field/key=field_66e498ef531f3', array( $this, 'acf_hide_justifi') ); /* Only show Justifi toggle to Admins */
	}


	public function acf_hide_justifi( $field ) {

		// hide the field if the current user is not able to save options within the admin
		$options = get_option( 'justifi_config_options' );

		if ( current_user_can( 'manage_options' ) && ! empty( $options['account_permissions'] ) ) {
			return $field;
		}

		wp_enqueue_style( 'justifi-permissions-css', plugin_dir_url( __DIR__ ) . 'admin/css/justifi-permissions.css', array(), '1.1', 'all' );
		return false;

	}

	public function wc_order_item_add_action_buttons_callback( $order ) {
		$label = esc_html__( 'Custom', 'woocommerce' );
		$slug  = 'custom';
		?>
		<button type="button" class="button <?php echo $slug; ?>-items"><?php echo $label; ?></button>
		<?php
	}

	/**
	 * Set up hooks and filters used by this class.
	 */
	public function init() {}

	/**
	 * Register the scripts and styles for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		$stmp = filemtime( plugin_dir_path( __DIR__ ) . 'admin/css/justifi-admin.css' );
		wp_enqueue_style( 'justifi-admin-css', plugin_dir_url( __DIR__ ) . 'admin/css/justifi-admin.css', array(), $stmp, 'all' );
		wp_enqueue_script( 'justifi-admin-js', plugin_dir_url( __DIR__ ) . 'admin/js/justifi-admin.js', array(), false, true );
	}



	/*= PAGES %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%*/
	/**
	 * Add pages to the admin menu.
	 */
	public function add_menu_pages() {
		add_submenu_page(
			'justifi_payments',
			'Justifi',
			'Justifi',
			'edit_posts',
			'justifi-config',
			array( $this, 'justifi_content' )
		);
	}

	/**
	 * Add query args to additional setting pages
	 */

	 public function justify_sub_page( $slug ) {

		 $current = 'justifi-config';
		 $target_arg = $slug;

		 // Construct the URL to the other settings page
		 $target_url = add_query_arg(
			 array(
				 'page' => $target_arg,
			 ),
			 admin_url('admin.php')
		 );

		 return( $target_url );
	 }

	/**
	 * Display the index page.
	 */
	public function justifi_content() {

		?>
		<div class="justifi-admin">
			<h1>Justifi Integration</h1>

			<div class="justifi-admin-form">
				<form method="post" action="options.php">
					<?php
						settings_fields( 'justifi_config_options' );
						do_settings_sections( 'justifi_config' );
						submit_button();
					?>
				</form>
			</div>
		</div>



		<?php
	}

	/*= SETTINGS PAGE %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	 * Add some input fields to hold our account get_meta_keys
	 * Client ID is visible
	 * Client Secret is hidden in a Password field
	 * https://developer.wordpress.org/plugins/settings/custom-settings-page/
	 */

	public function justifi_register_settings() {
		register_setting( 'justifi_config_options', 'justifi_config_options', 'string' );
		add_settings_section( 'justifi_api_keys', 'Justifi API Keys', array( $this, 'justifi_section_text'), 'justifi_config');
		add_settings_field( 'justifi_setting_client_key', 'Client Key', array( $this, 'justifi_setting_client_key'), 'justifi_config', 'justifi_api_keys' );
		add_settings_field( 'justifi_setting_client_secret', 'Client Secret', array( $this, 'justifi_setting_client_secret'), 'justifi_config', 'justifi_api_keys' );
		add_settings_field( 'justifi_setting_account_permissions', 'Restrict Access', array( $this, 'justifi_setting_account_permissions'), 'justifi_config', 'justifi_api_keys' );
	}
	public function justifi_section_text() {
		echo '<p>Set your API keys for the Justifi integration.<br>Keys beginning with "test_" are in Test Mode.</p>';
	}
	public function justifi_setting_client_key() {
		$options = get_option( 'justifi_config_options' );
		echo '<input name="justifi_config_options[client_key]" id="justifi_client_key" type="text" value="' . esc_attr( $options['client_key'] ) . '" class="justifi-input" /><br>';
	}
	public function justifi_setting_client_secret() {
		$options = get_option( 'justifi_config_options' );
		echo '<input name="justifi_config_options[client_secret]" id="justifi_client_secret" type="password" value="' . esc_attr( $options['client_secret'] ) . '" class="justifi-input" />';

	}
	public function justifi_setting_account_permissions() {
		$options = get_option( 'justifi_config_options' );
		echo '<input name="justifi_config_options[account_permissions]" id="justifi_account_permissions" type="checkbox" value="1" class="justifi-checkbox" ' .checked('1', $options['account_permissions'] ?? '0', false ). '/>  Check to allow admins to manage Justifi access.  If left unchecked all accounts will have access to create a Justifi account.';
	}


	/*= META BOXES %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	* Here we add a few custom meta boxes to hold ID's generated by Justifi.
	* These are visible inside of the Organization Admin
	* These are also used as conditionals to check what step in the setup process we are in
	*/

	/* Justifi Business ID - Generated upon successful creation of a Business within Justifi
	 * @class-create-business.php
	 */
	public function justifi_business_id() {

		add_meta_box( 'justifi_business_id', __( 'Justifi Business ID'), function( $post ) {
			$value = get_post_meta( $post->ID, 'justifi_business_id', true );
			?>
			<input class="jf-post-meta jf-biz-id" type="text" name="justifi-business-id" placeholder="Not yet created" value="<?php echo esc_attr( $value ); ?>" readonly />
			<?php
		},'organization', 'side', 'low');

	}

	/* Justifi Bank Account ID - Generated once an Organization has submitted their bank account details and accepted the Terms & Conditions
	 * @class-shortcode-onboarding-form.php
	 */
	public function justifi_bank_account_id() {

		add_meta_box( 'justifi_bank_account_id', __( 'Justifi Bank Account ID'), function( $post ) {
			$value = get_post_meta( $post->ID, 'justifi_bank_account_id', true );
			?>
			<input class="jf-post-meta jf-ba-id" type="text" name="justifi-ba-id" placeholder="Not yet created" value="<?php echo esc_attr( $value ); ?>" readonly />
			<?php
		}, 'organization', 'side', 'low');

	}
	/* Justifi Sub Account ID - Generated once an Organization has successfully fulfilled all of the required fields on the Business Web Form and Onboarding Form
	 * @class-provision-product.php
	 */
	public function justifi_sub_account_id() {

		add_meta_box( 'justifi_sub_account_id', __( 'Justifi Sub Account ID'), function( $post ) {
			$value = get_post_meta( $post->ID, 'justifi_sub_account_id', true );
			?>
			<input class="jf-post-meta jf-sa-id" type="text" name="justifi-sa-id" placeholder="Not yet created" value="<?php echo esc_attr( $value ); ?>" readonly />
			<?php
		}, 'organization', 'side', 'low');

	}

}
new \Justifi_Admin();
