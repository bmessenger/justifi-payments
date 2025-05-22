<?php
/**
 * Add the Shortcode for the Payment Provision (BETA) Web Component
 *
 * @link       https://bradmessenger.com/
 * @since      1.0.0
 *
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments/shortcodes
 */

namespace Justifi_Payments\Shortcodes;

use Justifi_Payments\Justifi_Helpers;

/**
 * Add menu items and pages to the WordPress admin.
 */
class Payment_Provision_Form {
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->generate_shortcode();
	}

	public function generate_shortcode() {
		add_shortcode( 'Justifi_Payment_Provision_Form', array( $this, 'form_generate' ) );
	}

	protected function get_web_token() {

		$biz = array();
		$business_id = Justifi_Helpers::get_biz_id();
		$access_token = Justifi_Helpers::get_access_token();
		$url = 'https://api.justifi.ai/v1/web_component_tokens';

		$resources = array(
			'resources' => array(
				'write:business:' .$business_id,
			),
		);
		$args = array(
			'headers' => array(
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer ' . $access_token,
			),
			'body' => wp_json_encode($resources),
		);

		$response = wp_remote_post( $url, $args );
		$response_body = wp_remote_retrieve_body( $response );
		$decoded = json_decode($response_body, true);
		$biz['web_token'] = $decoded['access_token'];
		$biz['biz_id']	= $business_id;

		return( $biz );
	}

	/**
	 * Setup the Payment Provision Form Web Component
	 *
	 * @since 1.0.0
	 */
	public function form_generate () {

		$output = '';

		$biz = $this->get_web_token();

		$submitted = Justifi_Helpers::justifi_business_form_submitted( $biz['biz_id'] );
		
		$org_id = Justifi_Helpers::get_org_id();
		
		/* If restriction is in place, exit */
		$options = get_option( 'justifi_config_options' );
		$enabled = get_field( 'enable_justifi', $org_id );
		if ( $enabled != 'yes' && ! empty( $options['account_permissions'] ) ) return '';
			
		if ( isset( $biz['biz_id'] ) && $submitted == true ) {

			$output = '
				<div class="message no-account" style="display: block; text-align: left;">
					<p>It looks like you\'ve already submitted your business form.  <a href="/dashboard/justifi-business-details/" id="bizDetails">You may review your business information here</a>.</p>
					<p>If you need to make any changes to the information you\'ve submitted please reach out to <a href="mailto:support@gmail.com">support@gmail.com</a>.</p>
				</div>
			';

			return $output;

		} elseif ( isset( $biz['biz_id'] ) && $submitted == false ) {

			ob_start();
	
			?>
			<justifi-payment-provisioning business-id="<?php echo esc_attr( $biz['biz_id'] ); ?>" auth-token="<?php echo esc_attr( $biz['web_token'] ); ?>"></justifi-payment-provisioning>
			<script>
				(function ($) {
					var paymentProvisioning = document.querySelector("justifi-payment-provisioning");
	
					paymentProvisioning.addEventListener("submitted", (data) => {
						if (data.detail && data.detail.data && data.detail.data.response && data.detail.data.response.data && data.detail.data.response.data.sub_account_id) {
							console.log("FOUND SUB ACCOUNT");
							window.location.href = "<?php echo esc_url( site_url( '/dashboard/' ) ); ?>";
						}
	
						console.log("server response received", data);
					});
	
					paymentProvisioning.addEventListener("error-event", (data) => {
						let error_code = data.detail.errorCode;
						let error_message = data.detail.message;
						let error_severity = data.detail.severity;
	
						console.log("network error", data.detail);
						alert("There was an error processing your request.  Error Code: " + error_code + ".  Error Message: " + error_message + ".");
					});
				})(jQuery);
				</script>
				
				<?php
	
				$output = ob_get_clean();
	
				return $output;

			} else {

				$output = '
					<div class="message no-account">
						<p>It looks like you still need to create your account with Justifi, our new payment processor.  To do so head back to your <a href="/dashboard/">Dashboard</a> page and look for the "Create My Business" button to get started.  If you can\'t find your button please reach out to ou support team.</p>
					</div>
				';
	
				return $output;
	
			}
			
	}
}

new \Justifi_Payments\Shortcodes\Payment_Provision_Form();
