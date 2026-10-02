<div class="section band--paper">
	<div class="container">
		<section class="form-panel form-panel--centred" aria-labelledby="enquiry-title">
			<?php if ( halveron_sent( 'sector' ) ) : ?>
				<?php get_template_part( 'template-parts/form-success', null, array( 'id' => 'enquiry-title' ) ); ?>
			<?php else : ?>
				<h2 class="display" id="enquiry-title">Would you like to get in touch with us?</h2>
				<p class="form-panel__intro"><?php echo esc_html( halveron_text( 'form_intro', $args['post_id'] ) ); ?></p>
				<form class="contact-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
					<?php
					get_template_part( 'template-parts/form-security', null, array(
						'action'   => 'halveron_enquiry',
						'fields'   => array(
							'form'   => 'sector',
							'source' => $args['post_id'],
						),
						'honeypot' => 'enquiry-website',
					) );
					?>
					<div class="contact-form__row">
						<?php get_template_part( 'template-parts/field', null, array( 'autocomplete' => 'name', 'error' => 'Enter your name', 'label' => 'Name', 'name' => 'name', 'prefix' => 'enquiry', 'type' => 'text' ) ); ?>
						<?php get_template_part( 'template-parts/field', null, array( 'autocomplete' => 'organization', 'label' => 'Company', 'name' => 'company', 'prefix' => 'enquiry', 'type' => 'text' ) ); ?>
					</div>
					<div class="contact-form__row">
						<?php get_template_part( 'template-parts/field', null, array( 'autocomplete' => 'email', 'error' => 'Enter an email address in the correct format, like name@example.com', 'label' => 'Email', 'name' => 'email', 'prefix' => 'enquiry', 'type' => 'email' ) ); ?>
						<?php get_template_part( 'template-parts/field', null, array( 'autocomplete' => 'tel', 'label' => 'Phone', 'name' => 'phone', 'prefix' => 'enquiry', 'type' => 'tel' ) ); ?>
					</div>
					<?php get_template_part( 'template-parts/field', null, array( 'error' => 'Enter your message', 'label' => 'Message', 'name' => 'message', 'prefix' => 'enquiry' ) ); ?>
					<p class="meta"><?php echo esc_html( halveron_option( 'form_note' ) ); ?></p>
					<button class="button contact-form__submit" type="submit">Send my message</button>
				</form>
			<?php endif; ?>
		</section>
	</div>
</div>
