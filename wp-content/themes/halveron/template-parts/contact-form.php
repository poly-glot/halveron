<section class="form-panel" aria-labelledby="enquiry-title">
	<?php if ( halveron_sent( 'contact' ) ) : ?>
		<?php get_template_part( 'template-parts/form-success', null, array( 'heading_class' => 'heading', 'id' => 'enquiry-title' ) ); ?>
	<?php else : ?>
		<h2 class="heading" id="enquiry-title">Would you like to get in touch with us?</h2>
		<p class="form-panel__intro"><?php echo esc_html( halveron_text( 'contact_form_intro' ) ); ?></p>
		<form class="contact-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<?php
			get_template_part( 'template-parts/form-security', null, array(
				'action'   => 'halveron_enquiry',
				'fields'   => array(
					'form'   => 'contact',
					'source' => get_the_ID(),
				),
				'honeypot' => 'contact-website',
			) );
			?>
			<p class="meta">All fields are required unless marked optional.</p>
			<?php get_template_part( 'template-parts/field', null, array( 'autocomplete' => 'name', 'error' => 'Enter your name', 'label' => 'Name', 'name' => 'name', 'prefix' => 'contact', 'type' => 'text' ) ); ?>
			<?php get_template_part( 'template-parts/field', null, array( 'autocomplete' => 'email', 'error' => 'Enter an email address in the correct format, like name@example.com', 'hint' => 'We will only use this to reply to you.', 'label' => 'Email', 'name' => 'email', 'prefix' => 'contact', 'type' => 'email' ) ); ?>
			<?php get_template_part( 'template-parts/field', null, array( 'autocomplete' => 'organization', 'label' => 'Company', 'name' => 'company', 'prefix' => 'contact', 'type' => 'text' ) ); ?>
			<?php get_template_part( 'template-parts/field', null, array( 'autocomplete' => 'tel', 'hint' => 'Include the area code.', 'label' => 'Phone', 'name' => 'phone', 'prefix' => 'contact', 'type' => 'tel' ) ); ?>
			<?php get_template_part( 'template-parts/field', null, array( 'autocomplete' => 'off', 'error' => 'Enter your message', 'hint' => 'Tell us a little about your organisation and what you would like to discuss.', 'label' => 'Message', 'name' => 'message', 'prefix' => 'contact' ) ); ?>
			<div class="check">
				<input class="check__input" id="contact-newsletter" type="checkbox" name="newsletter">
				<label class="check__label" for="contact-newsletter">Sign up for the Halveron newsletter</label>
			</div>
			<button class="button contact-form__submit" type="submit">Send my message</button>
			<p class="meta"><?php echo esc_html( halveron_option( 'form_note' ) ); ?></p>
		</form>
	<?php endif; ?>
</section>
