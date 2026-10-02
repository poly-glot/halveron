<?php
$is_next = 'next' === $args['direction'];
?>
<?php if ( $is_next ) : ?>
	<button class="carousel__button carousel__button--next" type="button" data-carousel-next hidden>
		<svg class="icon" aria-hidden="true" focusable="false"><use href="#icon-chevron-right" /></svg>
		<span class="visually-hidden"><?php echo esc_html( $args['label'] ); ?></span>
	</button>
<?php else : ?>
	<button class="carousel__button carousel__button--previous" type="button" data-carousel-previous hidden>
		<svg class="icon" aria-hidden="true" focusable="false"><use href="#icon-chevron-left" /></svg>
		<span class="visually-hidden"><?php echo esc_html( $args['label'] ); ?></span>
	</button>
<?php endif; ?>
