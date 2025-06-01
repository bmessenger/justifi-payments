<?php if ( $biz_id && $submitted == false ) : ?>
	
		<section id="dash_w9" class="step step-4">
			<div class="step-icon">
				 <i class="fas fa-exclamation-triangle"></i>
			</div>
			<div class="step-content">
				<h4>W9 info for taxes and Banking info for direct deposits not complete</h4>
				<p>In order to start collecting payments online you will need enter in your business and banking information.</p>
			</div>
			<div class="step-action">
				<a href="/dashboard/justifi-payment-provision-form/" id="bizForm" class="btn-justifi">Get Started</a>
			</div>
		</section>
	
<?php elseif ( !isset( $biz_id ) ) : ?>
	
	<?php return; ?>
	
<?php else : ?>
	
	<section id="dash_w9" class="step step-3">
		<div class="step-icon">
			  <i class="fas fa-check"></i>
		 </div>
		 <div class="step-content">
			 <h4>W9 info for taxes and Banking info for direct deposits complete!</h4>
			 <p>Your W9 Information and Bank Account information has been submitted.</p>
		 </div>
		 <div class="step-action">
			 <a href="/dashboard/my-business-details/" id="bizDetails" class="btn-justifi">Review Details</a>
		 </div>
	</section>

<?php endif; ?>