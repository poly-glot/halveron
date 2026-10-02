<?php
get_header();

while ( have_posts() ) :
	the_post();

	$address = halveron_lines( halveron_option( 'address' ) );

	get_template_part( 'template-parts/page-header', null, array(
		'after' => 'template-parts/contact-lines',
		'text'  => halveron_text( 'intro' ),
		'title' => get_the_title(),
	) );
	?>
	<div class="with-aside container">
		<div class="with-aside__main">
			<section class="stack" aria-labelledby="office-title">
				<h2 class="display" id="office-title"><?php echo esc_html( halveron_text( 'contact_office_heading' ) ); ?></h2>
				<div class="prose">
					<address><?php echo implode( '<br>', array_map( 'esc_html', $address ) ); ?></address>
					<p><?php echo esc_html( sprintf( '%s is registered in England and Wales, company number %s. Registered office as above.', halveron_option( 'legal_name' ), halveron_option( 'company_number' ) ) ); ?></p>
					<p class="meta"><?php echo esc_html( halveron_text( 'contact_address_note' ) ); ?></p>
				</div>
			</section>
			<section class="stack" aria-labelledby="find-title">
				<h2 class="display" id="find-title"><?php echo esc_html( halveron_text( 'contact_directions_heading' ) ); ?></h2>
				<div class="prose">
					<?php foreach ( halveron_rows( 'contact_directions' ) as $direction ) : ?>
						<h3><?php echo esc_html( (string) ( $direction['title'] ?? '' ) ); ?></h3>
						<?php echo halveron_rich( (string) ( $direction['text'] ?? '' ) ); ?>
					<?php endforeach; ?>
				</div>
			</section>
		</div>
		<div class="with-aside__aside">
			<?php get_template_part( 'template-parts/contact-form' ); ?>
		</div>
	</div>
	<div class="map-band">
		<?php echo halveron_image( (int) halveron_meta( 'contact_map' ), 'map-band__image', array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
	</div>
	<?php
endwhile;

get_footer();
