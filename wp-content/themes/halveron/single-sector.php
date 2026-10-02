<?php
get_header();

while ( have_posts() ) :
	the_post();

	$sector_id    = get_the_ID();
	$clients      = halveron_ordered( halveron_meta( 'clients', $sector_id ), 'organisation' );
	$case_studies = halveron_ordered( halveron_meta( 'case_studies', $sector_id ), 'post' );

	halveron_prime_attachments( array_map( fn ( WP_Post $client ): int => (int) halveron_meta( 'logo', $client->ID ), $clients ) );

	get_template_part( 'template-parts/page-header', null, array(
		'after'      => 'template-parts/header-media',
		'after_args' => array( 'post_id' => $sector_id ),
		'title'      => get_the_title(),
	) );

	get_template_part( 'template-parts/framework', null, array( 'post_id' => $sector_id ) );

	get_template_part( 'template-parts/enquiry-form', null, array( 'post_id' => $sector_id ) );
	?>
	<?php if ( $clients ) : ?>
		<div class="section section--tight">
			<div class="container">
				<?php get_template_part( 'template-parts/client-carousel', null, array( 'organisations' => $clients ) ); ?>
			</div>
		</div>
	<?php endif; ?>
	<?php if ( $case_studies ) : ?>
		<section class="section section--tight" aria-labelledby="case-studies-title">
			<div class="container">
				<div class="section-head">
					<h2 class="display" id="case-studies-title">Case studies</h2>
				</div>
				<?php get_template_part( 'template-parts/insight-grid', null, array( 'compact' => true, 'posts' => $case_studies ) ); ?>
			</div>
		</section>
	<?php endif; ?>
	<?php
	get_template_part( 'template-parts/related', null, array( 'post_id' => $sector_id ) );
endwhile;

get_footer();
