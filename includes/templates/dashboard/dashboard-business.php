<?php if ( $biz_id && $submitted == false ) : ?>
	
		<section id="dash_w9" class="step step-4">
			<div class="step-icon">
				 <i class="fas fa-exclamation-triangle"></i>
			</div>
			<div class="step-content">
				<h4>W9 info for taxes not complete</h4>
				<p>In order to start collecting payments online you will need enter in your business information.</p>
			</div>
			<div class="step-action">
				<a href="/dashboard/justifi-submit-business-details/" id="bizForm" class="btn-justifi">View</a>
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
			 <h4>W9 info for taxes complete</h4>
			 <p>Your W9 Information has been submitted.</p>
		 </div>
		 <div class="step-action">
			 <a href="/dashboard/justifi-business-details/" id="bizDetails" class="btn-justifi">Review Details</a>
		 </div>
	</section>

<?php endif; ?>