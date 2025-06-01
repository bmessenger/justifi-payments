<?php if ( isset( $biz_id ) ) : ?>
	
		 <section id="dash_connect" class="step step-complete step-1">
			 <div class="step-icon">
				 <i class="fas fa-check"></i>
			</div>
			<div class="step-content">
				<h4>Account is Active - Take Payments, Track Sales, Blast Emails, View Attendees and Buyer Information, and more.</h4>
			</div>
		</section>
	
<?php else : ?>

	<section id="dash_connect" class="step step-1  dash-connect">
		 <div class="step-icon">
			 <i class="fas fa-exclamation-triangle"></i>
		</div>
		<div class="step-content">
			<h4>Connect to the Justifi Payment Processor</h4>
			<p>Justifi is an all-in-one secure solution for individuals and businesses of any size to manage payments and payouts.  Get started in minutes with no setup or hidden fees.</p>
		</div>
		<div class="step-action">
			<button type="submit" id="bizBtn" class="btn-justifi" data-nonce="<?php echo wp_create_nonce( 'activate-and-connect' ); ?>">Activate and Connect</button>
		</div>
	</section>
	
<?php endif; ?>