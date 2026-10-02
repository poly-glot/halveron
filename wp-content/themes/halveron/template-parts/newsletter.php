<section class="newsletter" aria-labelledby="newsletter-title">
	<div class="container">
		<h2 class="heading newsletter__title" id="newsletter-title"><?php echo esc_html( halveron_option( 'newsletter_heading' ) ); ?></h2>
		<?php if ( halveron_subscribed() ) : ?>
			<p class="newsletter__text" role="status">Thank you for signing up. As this is a demonstration, nothing has been sent or stored.</p>
		<?php else : ?>
			<form class="newsletter__form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
				<?php
				get_template_part( 'template-parts/form-security', null, array(
					'action'   => 'halveron_newsletter',
					'honeypot' => 'newsletter-website',
				) );
				?>
				<div class="field newsletter__field">
					<label class="visually-hidden" for="newsletter-email">Email address</label>
					<input class="field__control" id="newsletter-email" type="email" name="email" placeholder="Enter your email address…" autocomplete="email" required aria-describedby="newsletter-hint">
				</div>
				<button class="button" type="submit">Sign up</button>
			</form>
		<?php endif; ?>
		<p class="newsletter__text"><?php echo esc_html( halveron_option( 'newsletter_text' ) ); ?></p>
		<p class="newsletter__hint" id="newsletter-hint"><?php echo esc_html( halveron_option( 'newsletter_hint' ) ); ?></p>
	</div>
</section>
