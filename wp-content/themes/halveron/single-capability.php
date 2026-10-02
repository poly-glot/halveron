<?php
get_header();

while ( have_posts() ) :
	the_post();

	$capability_id = get_the_ID();
	$approach      = halveron_rows( 'approach', $capability_id );
	$partners      = halveron_ordered( halveron_meta( 'partners', $capability_id ), 'organisation' );

	halveron_prime_attachments( array_map( fn ( WP_Post $partner ): int => (int) halveron_meta( 'logo', $partner->ID ), $partners ) );

	get_template_part( 'template-parts/page-header', null, array(
		'after'      => 'template-parts/header-media',
		'after_args' => array( 'post_id' => $capability_id ),
		'icon'       => get_post_field( 'post_name' ),
		'title'      => get_the_title(),
	) );

	get_template_part( 'template-parts/framework', null, array( 'post_id' => $capability_id ) );
	?>
	<?php if ( $approach ) : ?>
		<section class="section section--tight" aria-labelledby="approach-title">
			<div class="container">
				<div class="section-head">
					<h2 class="display" id="approach-title">Our approach</h2>
				</div>
				<ol class="approach-grid">
					<?php foreach ( $approach as $step ) : ?>
						<li class="approach-grid__item">
							<h3 class="approach-grid__title"><?php echo esc_html( (string) ( $step['title'] ?? '' ) ); ?></h3>
							<p class="approach-grid__text"><?php echo esc_html( (string) ( $step['text'] ?? '' ) ); ?></p>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		</section>
	<?php endif; ?>
	<?php if ( $partners ) : ?>
		<section class="section section--tight" aria-labelledby="partners-title">
			<div class="container">
				<div class="section-head">
					<h2 class="display" id="partners-title"><?php echo esc_html( halveron_text( 'partners_heading', $capability_id ) ); ?></h2>
					<p class="section-head__intro"><?php echo esc_html( halveron_text( 'partners_intro', $capability_id ) ); ?></p>
				</div>
				<?php get_template_part( 'template-parts/partner-list', null, array( 'organisations' => $partners ) ); ?>
			</div>
		</section>
	<?php endif; ?>
	<?php
	get_template_part( 'template-parts/related', null, array( 'post_id' => $capability_id ) );
endwhile;

get_footer();
