<?php
$total = count( $args['titles'] );
?>
<ul class="carousel__dots" hidden>
	<?php foreach ( array_values( $args['titles'] ) as $index => $title ) : ?>
		<li><button class="carousel__dot" type="button" data-carousel-dot><span class="visually-hidden"><?php echo esc_html( sprintf( 'Show slide %d of %d: %s', $index + 1, $total, $title ) ); ?></span></button></li>
	<?php endforeach; ?>
</ul>
