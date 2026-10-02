<?php
$items = $args['items'];

$with_images = array_filter( $items, fn ( WP_Post $item ): bool => 'capability' !== $item->post_type );

halveron_prime_attachments( array_map( fn ( WP_Post $item ): int => (int) halveron_meta( 'image', $item->ID ), $with_images ) );
?>
<div class="section">
	<div class="container">
		<ul class="grid-3">
			<?php foreach ( $items as $index => $item ) : ?>
				<li>
					<?php get_template_part( 'template-parts/index-card', null, array( 'first' => 0 === $index, 'item' => $item ) ); ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
