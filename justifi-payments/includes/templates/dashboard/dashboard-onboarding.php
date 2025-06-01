<?php if ( $biz_id && $ba_id ) : ?>
	<section id="dash_onboard" class="step step-complete step-2">
		 <div class="step-icon">
			  <i class="fas fa-check"></i>
		 </div>
		 <div class="step-content">
			 <h4>Banking info for direct deposit complete</h4>
			 <p>Your banking information for direct deposits has been submitted.</p>
		</div>
	</section>
	
<?php elseif ( $biz_id && !$ba_id ) : ?>

		<section id="dash_onboard" class="step step-2">
			<div class="step-icon">
				 <i class="fas fa-exclamation-triangle"></i>
			</div>
			<div class="step-content">
				<h4>Banking info for direct deposit not complete</h4>
				<p>In order to collect payments you will need to enter in your routing and account number in our secure form.</p>
			</div>
			<div class="step-action">
				<a href="/dashboard/justifi-new-account-onboarding/" id="onboardingFormBtn" class="btn-justifi">View</a>
			</div>
		</section>

<?php endif; ?>