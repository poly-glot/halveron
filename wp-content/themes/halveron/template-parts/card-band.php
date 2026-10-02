<?php if ( ! empty( $args['posts'] ) ) : ?>
	<section class="section band--paper" aria-labelledby="<?php echo esc_attr( $args['id'] ); ?>">
		<div class="container">
			<div class="section-head section-head--centred">
				<h2 class="display" id="<?php echo esc_attr( $args['id'] ); ?>"><?php echo esc_html( $args['heading'] ); ?></h2>
			</div>
			<?php get_template_part( 'template-parts/insight-grid', null, array( 'date' => ! empty( $args['date'] ), 'posts' => $args['posts'] ) ); ?>
		</div>
	</section>
<?php endif; ?>
