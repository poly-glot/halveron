<section class="carousel carousel--logos" aria-label="<?php echo esc_attr( $args['label'] ); ?>" data-carousel>
	<?php get_template_part( 'template-parts/carousel-button', null, array( 'direction' => 'previous', 'label' => $args['previous'] ) ); ?>
	<ul class="carousel__track" tabindex="0" data-carousel-track>
		<?php foreach ( $args['organisations'] as $organisation ) : ?>
			<li class="carousel__slide" data-carousel-slide><?php echo halveron_logo( $organisation, 'carousel__logo' ); ?></li>
		<?php endforeach; ?>
	</ul>
	<?php get_template_part( 'template-parts/carousel-button', null, array( 'direction' => 'next', 'label' => $args['next'] ) ); ?>
</section>
