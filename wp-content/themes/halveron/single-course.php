<?php
get_header();

while ( have_posts() ) :
	the_post();

	$course_id      = get_the_ID();
	$training_email = halveron_option( 'training_email' );
	$contact        = halveron_post( halveron_meta( 'contact', $course_id ), 'person' );
	$next_date      = halveron_text( 'next_date', $course_id );
	$facts          = array_map( 'esc_html', array_filter( array(
		'Accreditation' => halveron_text( 'accreditation', $course_id ),
		'Next course'   => '' === $next_date ? '' : halveron_date( $next_date, 'j F Y' ),
		'Schedule'      => halveron_text( 'schedule', $course_id ),
		'Duration'      => halveron_text( 'duration', $course_id ),
		'Price'         => halveron_text( 'price', $course_id ),
		'Location'      => halveron_text( 'location', $course_id ),
	) ) );
	$modules        = array_map(
		fn ( array $module ): array => array(
			'text'  => (string) ( $module['text'] ?? '' ),
			'title' => (string) ( $module['title'] ?? '' ),
		),
		halveron_rows( 'modules', $course_id )
	);

	get_template_part( 'template-parts/page-header', null, array( 'title' => get_the_title() ) );
	?>
	<div class="with-aside container section">
		<div class="with-aside__main">
			<?php echo halveron_thumbnail( get_post(), 'article-lead__image', array( 'loading' => false ) ); ?>
			<div class="prose">
				<?php the_content(); ?>
			</div>
			<?php get_template_part( 'template-parts/facts', null, array( 'facts' => $facts ) ); ?>
			<section class="stack" aria-labelledby="who-title">
				<h2 class="heading" id="who-title">Who it is for</h2>
				<div class="prose">
					<?php echo halveron_paragraphs( halveron_text( 'who_for', $course_id ) ); ?>
				</div>
			</section>
			<?php if ( $modules ) : ?>
				<section class="stack" aria-labelledby="modules-title">
					<h2 class="heading" id="modules-title">Modules</h2>
					<?php echo halveron_paragraphs( halveron_text( 'modules_lead', $course_id ) ); ?>
					<?php get_template_part( 'template-parts/accordion', null, array( 'body' => 'template-parts/module-body', 'items' => $modules ) ); ?>
				</section>
			<?php endif; ?>
		</div>
		<?php if ( $contact ) : ?>
			<aside class="with-aside__aside" aria-label="Contact">
				<?php
				get_template_part( 'template-parts/contact-aside', null, array(
					'button_hidden' => ' about training',
					'button_label'  => 'Contact us',
					'button_url'    => halveron_mailto( $training_email ),
					'email'         => $training_email,
					'intro'         => 'Let us help you. Contact our training specialist:',
					'person'        => $contact,
					'phone'         => halveron_option( 'training_phone' ),
					'phone_href'    => halveron_option( 'training_phone_href' ),
				) );
				?>
			</aside>
		<?php endif; ?>
	</div>
	<?php
	get_template_part( 'template-parts/related', null, array( 'post_id' => $course_id ) );
endwhile;

get_footer();
