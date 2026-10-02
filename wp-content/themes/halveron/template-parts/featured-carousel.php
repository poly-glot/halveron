<?php
$slides = $args['posts'];
?>
<section class="carousel" aria-label="Featured articles" data-carousel>
	<?php get_template_part( 'template-parts/carousel-button', null, array( 'direction' => 'previous', 'label' => 'Previous featured article' ) ); ?>
	<ul class="carousel__track" tabindex="0" data-carousel-track>
		<?php foreach ( $slides as $index => $slide ) : ?>
			<li class="carousel__slide" data-carousel-slide>
				<article class="featured">
					<div class="featured__media">
						<?php echo halveron_thumbnail( $slide, 'featured__image', array( 'loading' => 0 === $index ? false : 'lazy' ) ); ?>
						<p class="tag"><?php echo esc_html( halveron_insight( $slide )['tag'] ); ?></p>
					</div>
					<div class="featured__body">
						<h2 class="featured__title"><a href="<?php echo esc_url( get_permalink( $slide ) ); ?>"><?php echo esc_html( get_the_title( $slide ) ); ?></a></h2>
						<p class="featured__excerpt"><?php echo esc_html( $slide->post_excerpt ); ?></p>
					</div>
				</article>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php get_template_part( 'template-parts/carousel-button', null, array( 'direction' => 'next', 'label' => 'Next featured article' ) ); ?>
	<?php get_template_part( 'template-parts/carousel-dots', null, array( 'titles' => array_map( 'get_the_title', $slides ) ) ); ?>
</section>
