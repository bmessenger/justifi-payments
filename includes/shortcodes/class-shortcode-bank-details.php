<?php
/**
 * Add the Shortcode for the displaying Bank Account Info from Justifi
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
class Bank_Details {
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->generate_shortcode();
	}

	public function generate_shortcode() {
		add_shortcode( 'Justifi_Bank_Details', array( $this, 'get_bank_details' ) );
	}
	
	/**
	 * Send the API Request for the Bank Details
	 *
	 * @since 1.0.0
	 */
	public function get_bank_details () {
			
			$biz = Justifi_Helpers::get_biz_id();
			$bank = Justifi_Helpers::get_ba_id();
			
			if ( !isset( $biz ) ) :
				
				$response = 'Business ID not found.';
				error_log( print_r( $response, true ) );
				
			elseif ( !isset( $bank ) ) :
				
				$response = 'Bank Account ID not found';
				error_log( print_r( $response, true ) );
				
			else :
				
				/* Build our Endpoint */
				$url = 'https://api.justifi.ai/v1/entities/bank_accounts/' . $bank;
				
				
				/* 
				 * Almost all API requests to Justifi require an Access Token
				 * We've set up a helper class that can generate a token for us 
				 * @class-justifi-helpers.php 
				 */
				$access_token = Justifi_Helpers::get_access_token();
					
				/* 
				 * Make our API call (POST request), passing the required headers and payload
				 * Set the response to a variable at the same time
				 */
				$response = wp_remote_get( $url, array(
					'headers' => array(
						'Content-Type' => 'application/json',
						'Authorization' => 'Bearer ' . $access_token,
					),
				) );
				
				/* Error handling */
				if ( is_wp_error( $response ) ) {
					return false;
					error_log( print_r( $response, true ) );
				}
				
				/* The response is returned in JSON so we need to do some cleanup */
				$response_body = wp_remote_retrieve_body( $response );
				$decoded = json_decode($response_body, true);
				$data = $decoded['data'];
			
				ob_start();
				
				?>
				
				<div class="bank-details mt-4" part="detail-section">
					<h5 part="detail-section-title">Bank Account Details</h5>
					<hr>
					<div class="d-table gap-2 w-100">
						<div class="row gy-3">
							<div class="col-12 col-md-6">
								<div class="d-table-row gap-2"><span part="detail-section-item-title" class="fw-bold d-table-cell px-2">Account Owner</span><span part="detail-section-item-data" class="flex-1 d-table-cell px-2 text-wrap"><?php echo $data['account_owner_name']; ?></span></div>
								<div class="d-table-row gap-2"><span part="detail-section-item-title" class="fw-bold d-table-cell px-2">Account Number Last 4</span><span part="detail-section-item-data" class="flex-1 d-table-cell px-2 text-wrap"><?php echo $data['account_number_last4']; ?></span></div>
								<div class="d-table-row gap-2"><span part="detail-section-item-title" class="fw-bold d-table-cell px-2">Routing Number</span><span part="detail-section-item-data" class="flex-1 d-table-cell px-2 text-wrap"><?php echo $data['routing_number']; ?></span></div>
							</div>
							<div class="col-12 col-md-6">
								<div class="d-table-row gap-2"><span part="detail-section-item-title" class="fw-bold d-table-cell px-2">Bank Name</span><span part="detail-section-item-data" class="flex-1 d-table-cell px-2 text-wrap"><?php echo $data['bank_name']; ?></span></div>
								<div class="d-table-row gap-2"><span part="detail-section-item-title" class="fw-bold d-table-cell px-2">Account Type</span><span part="detail-section-item-data" class="flex-1 d-table-cell px-2 text-wrap"><?php echo $data['account_type']; ?></span></div>
								<div class="d-table-row gap-2"><span part="detail-section-item-title" class="fw-bold d-table-cell px-2">Account Nickname</span><span part="detail-section-item-data" class="flex-1 d-table-cell px-2 text-wrap"><?php echo $data['nickname']; ?></span></div>
							</div>
						</div>
					</div>
				</div>
				
				<?php
				
				return ob_get_clean();	
				
			endif;
		 
	}

}

new \Justifi_Payments\Shortcodes\Bank_Details();