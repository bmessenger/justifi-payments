<?php

/**
 * Class for housing the webhooks listening for Justifi Sub Account changes
 *
 * @link       https://bradmessenger.com/
 * @since      1.0.0
 *
 * @package    Justifi_Payments
 * @subpackage Justifi_Payments
 */

namespace Justifi_Payments\Webhooks;

class Justifi_Sub_Accounts {


	private $secret = SUBACCOUNT_WEBHOOK_KEY;
	private $sub = array();

	/**
	  * Class constructor
	  */
	 public function __construct() {

		add_action( 'rest_api_init', array( $this, 'justifi_rest_routes') );

	}

	public function justifi_rest_routes() {

		/*
		 * Monitor for Sub Account Status Changes
		 * @refund_event - https://docs.justifi.tech/api-spec#tag/Events/operation/subAccountEvent
		 * @authentication - https://docs.justifi.tech/api-spec#tag/Webhook-Delivery
		 *
		 * The permission callback validates the signature hash from JustiFi
		 * If the permission returns true we get the request body and do the things
		 */

		register_rest_route( 'justifi/v2', '/sub-accounts', array(
			'methods'  				=> \WP_REST_Server::EDITABLE,
			'callback' 				=> array( $this, 'justifi_sub_account' ),
			'permission_callback'	=> array( $this, 'permission_check' ),
		) );

	}

	public function permission_check( $req ) {

		/* Get the Timestamp and hashed Signature from the JustiFi event */
		$timestamp = $req->get_header( 'Justifi-Timestamp' );
		$signature = $req->get_header( 'Justifi-Signature' );
		$jsonEvent = $req->get_body();
		$payload = "$timestamp.$jsonEvent";

		/* Hash the timestamp and payload, then compare to what we received from JustiFi */
		$hex = hash_hmac('sha256', $payload, $this->secret);

		return $signature == $hex;

	}

	public function justifi_sub_account( $req ) {

		$sub_account = json_decode( $req->get_body() );

		if ( $sub_account->event_name == 'sub_account.updated' ) {
			$this->update_account( $sub_account );
		}
	}

	public function update_account( $sub_account ) {

		/* Query the Organization using the Business ID (previously set as post meta when creating the account) */
		$args = array(
			'post_type'      => 'organization',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
			'meta_query' => array(
				array(
					'key' => 'justifi_business_id',
					'value' => $sub_account->data->business_id,
					'compare' => '='
				),

			),
		);
		$organization = new \WP_Query( $args );
		
		//error_log( print_r( $sub_account, true ) );

		$org_id = 0;
		$auth_id = 0;
		while ( $organization->have_posts() ) { $organization->the_post();
			$org_id = get_the_ID(); // Get the Organization ID
			$auth_id = get_the_author_meta( 'ID' ); // Get the Author ID (WordPress User)
		}

		wp_reset_postdata();

		/* Handle our notices ::
		 * Get the existing post_meta (array) and append the new account status 
		 * along with the event id and an "unread" status of true to display the alert on the bell
		 */
		$notices = get_post_meta( $org_id, '_organization_notices', true );
	
		if ( !is_array( $notices ) ) {
			$notices = array();
			$notice_count = 0;
			$notice_index = 0;
		} else {
			$notices = array_values( $notices );// Since were always removing, editing these notices let's ensure we're always starting with a fresh index.
			$notice_count = count( $notices );
			$notice_index = $notice_count++;
		}
		
		
		$notices[ $notice_index ]['id'] = $sub_account->id;
		$notices[ $notice_index ]['type'] = 'Account Update';
		$notices[ $notice_index ]['message'] = '<span class="message" data-notice-id="' .$sub_account->id. '"><b>Status:</b>&nbsp;<span>' .$sub_account->data->status. '</span></span>';
		$notices[ $notice_index ]['unread'] = true;
		
		//array_push( $notices, $new_notice );
		update_post_meta( $org_id, '_organization_notices', $notices );


		// Update the Justifi Account status for the Organization and User
		update_post_meta( $org_id, '_justifi_account_status', $sub_account->data->status );
		update_user_meta( $auth_id, '_justifi_account_status', $sub_account->data->status );

		/* Send out notifications to account owners */
		$this->send_owner_emails( $auth_id, $org_id, $sub_account );
		$this->send_admin_emails( $auth_id, $org_id, $sub_account );

		//return $sub;

	}

	public function send_owner_emails( $auth_id, $org_id, $sub_account ) {

		/* Prep our email settings */
		$owner = get_userdata( $auth_id );
		$owner_email = $owner->email;
		$owner_name = $owner->display_name;
		$owner_org = $sub_account->data->name;
		$owner_status = $sub_account->data->status;

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: Justifi <accounts@gmail.com>;',
			'Reply-To: <noreply@gmail.com>',
		);

		if ( $owner_status == 'enabled' ) {
			$subject = 'Your Account Has Been Approved!';
			$body = '
				<body>
					<table class="content" align="center" cellpadding="0" cellspacing="0" border="0" style="width: 100%; background-color: #fafafa;padding: 40px 0;">
						<tr>
							<td>
								<table class="content" align="center" cellpadding="0" cellspacing="0" border="0" style="width: 100%; max-width: 540px;background-color: #FFF;border-radius: 10px;padding: 20px;">
									  <tr>
										<td>
										  <p style="text-align:center;margin-bottom: 40px;">
										  	<a href="https://www.gmail.com/">
											  <img src="https://d2t3z6uf36951x.cloudfront.net/2021/03/justifi-logo-1.png"  width="300" style="height: auto; margin: 0 auto;display: block;width: 300px;height: auto !important">
											 </a>
										  </p>
										  <p>Hi ' .$owner_name. ',</p>
										  <p>We are thrilled to inform you that your payout information is now live and active! You can start collecting transactions immediately.</p>
										  <p>Once you start receiving payments, your payouts will be processed on a daily basis with advanced sales and attendee reporting within your dashboard.</p>
										  <h4>Refunds and Coupon Credits:</h4>
										  <p>Please note that if you need to issue a refund at any time, the amount will be returned to the same card used during the checkout process. The refunded amount will typically appear in the customer\'s account within 3 to 7 business days, if not sooner.</p>
										  <p>Because payouts are processed daily, you can refund at any time, and the amount will be deducted from the bank account on file. To view refunds and refund requests, visit your sales reports page for that specific event!</p>
										  <p>You are not required to give refunds or credits but we do recommend entering a refund policy for each event. </p>
										  <p>Your direct deposit and business information are securely stored off-site through an encrypted platform called JustiFi, ensuring the highest level of security.</p>
										  <p>Thank you for choosing Justifi. We are dedicated to providing you with the best tools and support for your events. If you have any questions or need assistance, please do not hesitate to contact our support team at <a href="mailto:sales@gmail.com">sales@gmail.com</a>.</p>
										  <p>Best regards,</p>
										  <p>Justifi Team</p>
										</td>
									  </tr>
								</table>
							</td>
						</tr>
					</table>
				</body>
			';

		} elseif ( $owner_status == 'rejected' ) {
			$subject = 'Update on Your Account Application';
			$body = '
				<body>
					<table class="content" align="center" cellpadding="0" cellspacing="0" border="0" style="width: 100%; background-color: #fafafa;padding: 40px 0;">
						<tr>
							<td>
								<table class="content" align="center" cellpadding="0" cellspacing="0" border="0" style="width: 100%; max-width: 540px;background-color: #FFF;border-radius: 10px;padding: 20px;">
									  <tr>
										<td>
										  <p style="text-align:center;margin-bottom: 40px;">
											  <a href="https://www.gmail.com/">
											  <img src="https://d2t3z6uf36951x.cloudfront.net/2021/03/justifi-logo-1.png"  width="300" style="height: auto; margin: 0 auto;display: block;width: 300px;height: auto !important">
											 </a>
										  </p>
										  <p>Hi ' .$owner_name. ',</p>
										  <p>We have identified some discrepancies in the information provided during your application process. To ensure your account is activated smoothly, we will need to verify a few details.</p>
										  <p>We appreciate your patience as we carefully review your application. Our team will reach out to you within the next 24 to 48 hours to assist with the necessary verification and guide you through the approval process.</p>
										  <p>Sincerely,</p>
										  <p>Justifi Team</p>
										</td>
									  </tr>
								</table>
							</td>
						</tr>
					</table>
				</body>
			';

		} elseif ( $owner_status == 'information_needed' ) {
			$subject = 'Account Creation Unsuccessful';
			$body = '
				<body>
					<table class="content" align="center" cellpadding="0" cellspacing="0" border="0" style="width: 100%; background-color: #fafafa;padding: 40px 0;">
						<tr>
							<td>
								<table class="content" align="center" cellpadding="0" cellspacing="0" border="0" style="width: 100%; max-width: 540px;background-color: #FFF;border-radius: 10px;padding: 20px;">
									  <tr>
										<td>
										  <p style="text-align:center;margin-bottom: 40px;">
											<a href="https://www.gmail.com/">
											<img src="https://d2t3z6uf36951x.cloudfront.net/2021/03/justifi-logo-1.png"  width="300" style="height: auto; margin: 0 auto;display: block;width: 300px;height: auto !important">
										   </a>
										  </p>
										  <p>Dear ' .$owner_name. ',</p>
										  <p>We have identified some discrepancies in the information provided during your application process. To ensure your account is activated smoothly, we will need to verify a few details.</p>
										  <p>We appreciate your patience as we carefully review your application. Our team will reach out to you within the next 24 to 48 hours to assist with the necessary verification and guide you through the approval process.</p>
										  <p>To resolve this, please reach out to our support team at <a hre="mailto:sales@gmail.com">sales@gmail.com</a> or <a href="tel:+18288388686">828-838-8686</a>. We are here to assist you and ensure that you can successfully create your account.</p>
										  <p>Thank you for your understanding and patience.</p>
										  <p>Best regards,</p>
										  <p>Justifi Team</p>
										</td>
									  </tr>
								</table>
							</td>
						</tr>
					</table>
				</body>
			';
		} else {
			$subject = 'Justifi Account Status Update';
			$body = '
				<body>
					<table class="content" align="center" cellpadding="0" cellspacing="0" border="0" style="width: 100%; background-color: #fafafa;padding: 40px 0;">
						<tr>
							<td>
								<table class="content" align="center" cellpadding="0" cellspacing="0" border="0" style="width: 100%; max-width: 540px;background-color: #FFF;border-radius: 10px;padding: 20px;">
				  					<tr>
										<td>
										<p style="text-align:center;margin-bottom: 40px;">
											  <a href="https://www.gmail.com/">
											  <img src="https://d2t3z6uf36951x.cloudfront.net/2021/03/justifi-logo-1.png"  width="300" style="height: auto; margin: 0 auto;display: block;width: 300px;height: auto !important">
											 </a>
										  </p>
					  					<p>Hi ' .$owner_name. '!</p>
					  					<p>This is a notice informing you that there has been an update to your payment processing account at Justifi.</p>
					  					<ul>
					  						<li>Organization: <strong>' .$owner_org. '</strong></li>
											<li>Account Status: <strong>'.$owner_status. '</strong></li>
					  					</ul>
					  					<p>If you have questions regarding this update please reach out to us using <a href="https://www.gmail.com/contact/">this form</a>.</p>
					  					<p>Thanks!</p>
					  					<p>Your friends at Justifi</p>
										</td>
				  					</tr>
								</table>
							</td>
						</tr>
					</table>
				</body>
			';
		}

		$send = wp_mail( $owner_email, $subject, $body, $headers );

	}
	
	public function send_admin_emails( $auth_id, $org_id, $sub_account ) {
		
		// All Admin Emails 
		/* $admins = get_users( array(
			'role' => 'administrator',
			'fields' => array('user_email')
		) );
		$admin_emails = wp_list_pluck($admins, 'user_email');
		$admin_emails = implode(', ', array_diff( $admin_emails, array('wordpress@bradmessenger.com') ) ); */
		
		// Sales Rep Email
		$sales_rep = get_field('organization_sales_rep', 'user_' .$auth_id);
		$sales_rep ? $sales_rep_email = $sales_rep['user_email'] : $sales_rep_email = '';
		
		//$send_to = $admin_emails. ', ' .$sales_rep_email;
		$send_to = 'members@gmail.com';
		if ( $sales_rep_email ) {
			$send_to = 'members@gmail.com, ' .$sales_rep_email;
		}
		
		// Organization Types
		$org_types = array();
		$types = get_the_terms( $org_id, 'event-type' );
		
		if ( $types ) {
			foreach( $types as $type ) {
				array_push($org_types, $type->name);
			}
			$org_types = implode(', ', $org_types );
		} else {
			$org_types = 'No types assigned';
		}
		
		// Organization Owner Details
		$owner = get_userdata( $auth_id );
		$owner_email = $owner->user_email;
		$owner_name = $owner->display_name;
		$owner_fname = $owner->first_name;
		$owner_lname = $owner->last_name;
		$owner_phone = $owner->billing_phone;
		$owner_org = $sub_account->data->name;
		$owner_status = $sub_account->data->status;
		
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: Justifi <accounts@gmail.com>;',
			'Reply-To: <noreply@gmail.com>',
		);
		
		$subject = 'Justifi Account Status Changed - ' .$owner_org;
		$body = '
			<body>
				<table class="content" align="center" cellpadding="0" cellspacing="0" border="0" style="width: 100%; background-color: #fafafa;padding: 40px 0;">
					<tr>
						<td>
							<table class="content" align="center" cellpadding="0" cellspacing="0" border="0" style="width: 100%; max-width: 540px;background-color: #FFF;border-radius: 10px;padding: 20px;">
								  <tr>
									<td>
									<p style="text-align:center;margin-bottom: 40px;">
										  <a href="https://www.gmail.com/">
										  <img src="https://d2t3z6uf36951x.cloudfront.net/2021/03/justifi-logo-1.png"  width="300" style="height: auto; margin: 0 auto;display: block;width: 300px;height: auto !important">
										 </a>
									  </p>
									  <h3 style="margin-bottom: 3px;">' .$owner_org. '</h3>
									  <h4 style="color: #999;margin:0 0 20px 0;">Account Status: ' .$owner_status. '</h4>
									  <p>The following account has been updated by the Justifi payment processor.</p>
									  <ul>
										<li>Organization: <strong>' .$owner_org. '</strong></li>
										<li>Owner Name: ' .$owner_name. '</li>
										<li>Owner Email: <strong>' .$owner_email. '</strong></li>
										<li>Owner Phone: <strong>' .$owner_phone. '</strong></li>
										<li>Organization Type: ' .$org_types. '</li>
										<li>Account Status: <strong>'.$owner_status. '</strong></li>
									  </ul>
									</td>
								  </tr>
							</table>
						</td>
					</tr>
				</table>
			</body>
		';
		
		$send = wp_mail( $send_to, $subject, $body, $headers );

	}
	
}

new \Justifi_Payments\Webhooks\Justifi_Sub_Accounts();