<?php
get_header();

while ( have_posts() ) :
	the_post();

	$last_updated = halveron_text( 'last_updated' );
	$notice       = halveron_text( 'notice' );

	get_template_part( 'template-parts/page-header', null, array(
		'lede'  => halveron_text( 'intro' ),
		'title' => get_the_title(),
	) );
	?>
	<?php if ( '' !== $last_updated ) : ?>
		<div class="container section legal">
			<p class="legal__updated"><?php echo esc_html( HALVERON_UPDATED_LABELS[ halveron_text( 'updated_label' ) ] ?? HALVERON_UPDATED_LABELS['updated'] ); ?> <time datetime="<?php echo esc_attr( $last_updated ); ?>"><?php echo esc_html( halveron_date( $last_updated, 'j F Y' ) ); ?></time></p>
			<?php if ( '' !== $notice ) : ?>
				<h2 class="visually-hidden">Demonstration notice</h2>
				<p class="legal__notice"><?php echo esc_html( $notice ); ?></p>
			<?php endif; ?>
			<div class="prose">
				<?php the_content(); ?>
			</div>
		</div>
	<?php else : ?>
		<div class="container section">
			<div class="prose">
				<?php the_content(); ?>
			</div>
		</div>
	<?php endif; ?>
	<?php
endwhile;

get_footer();
