<ul class="<?php echo ! empty( $args['compact'] ) ? 'grid-4' : 'grid-3'; ?>">
	<?php foreach ( $args['posts'] as $card ) : ?>
		<li>
			<?php get_template_part( 'template-parts/insight-card', null, array( 'compact' => ! empty( $args['compact'] ), 'date' => ! empty( $args['date'] ), 'post' => $card ) ); ?>
		</li>
	<?php endforeach; ?>
</ul>
