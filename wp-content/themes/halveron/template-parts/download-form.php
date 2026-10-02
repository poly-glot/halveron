<?php
$paper   = $args['post'];
$file_id = (int) halveron_meta( 'paper_file', $paper->ID );
$intro   = str_replace( '{title}', '<em>' . esc_html( get_the_title( $paper ) ) . '</em>', esc_html( halveron_option( 'download_intro' ) ) );
?>
<section class="section band--lavender download" id="download" aria-labelledby="download-title">
	<div class="container">
		<div class="form-panel form-panel--centred">
			<?php if ( halveron_sent( 'download' ) ) : ?>
				<?php get_template_part( 'template-parts/form-success', null, array( 'id' => 'download-title' ) ); ?>
				<?php if ( $file_id ) : ?>
					<p><a class="button" href="<?php echo esc_url( (string) wp_get_attachment_url( $file_id ) ); ?>"><?php echo esc_html( halveron_option( 'download_heading' ) ); ?></a></p>
				<?php endif; ?>
			<?php else : ?>
				<h2 class="display" id="download-title"><?php echo esc_html( halveron_option( 'download_heading' ) ); ?></h2>
				<p class="form-panel__intro"><?php echo $intro; ?></p>
				<form class="contact-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
					<?php
					get_template_part( 'template-parts/form-security', null, array(
						'action'   => 'halveron_enquiry',
						'fields'   => array(
							'form'   => 'download',
							'source' => $paper->ID,
						),
						'honeypot' => 'download-website',
					) );
					?>
					<div class="contact-form__row">
						<?php get_template_part( 'template-parts/field', null, array( 'autocomplete' => 'name', 'error' => 'Enter your name', 'label' => 'Name', 'name' => 'name', 'prefix' => 'download', 'type' => 'text' ) ); ?>
						<?php get_template_part( 'template-parts/field', null, array( 'autocomplete' => 'organization', 'error' => 'Enter your company or organisation', 'label' => 'Company', 'name' => 'company', 'prefix' => 'download', 'type' => 'text' ) ); ?>
					</div>
					<div class="contact-form__row">
						<?php get_template_part( 'template-parts/field', null, array( 'autocomplete' => 'email', 'error' => 'Enter an email address in the correct format, like name@example.com', 'label' => 'Email', 'name' => 'email', 'prefix' => 'download', 'type' => 'email' ) ); ?>
						<?php get_template_part( 'template-parts/field', null, array( 'autocomplete' => 'tel', 'label' => 'Phone', 'name' => 'phone', 'prefix' => 'download', 'type' => 'tel' ) ); ?>
					</div>
					<fieldset class="fieldset">
						<legend class="fieldset__legend">Your key areas of interest</legend>
						<ul class="check-grid">
							<?php foreach ( halveron_capabilities() as $capability ) : ?>
								<li class="check">
									<input class="check__input" id="interest-<?php echo esc_attr( $capability->post_name ); ?>" type="checkbox" name="interests[]" value="<?php echo esc_attr( $capability->post_name ); ?>">
									<label class="check__label" for="interest-<?php echo esc_attr( $capability->post_name ); ?>"><?php echo esc_html( get_the_title( $capability ) ); ?></label>
								</li>
							<?php endforeach; ?>
						</ul>
					</fieldset>
					<p class="meta"><?php echo halveron_inline( halveron_option( 'download_privacy' ) ); ?></p>
					<p class="meta"><?php echo esc_html( halveron_option( 'download_note' ) ); ?></p>
					<button class="button contact-form__submit" type="submit">Complete profile and download</button>
				</form>
			<?php endif; ?>
		</div>
	</div>
</section>
