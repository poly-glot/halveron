<?php if ( $args['items'] ) : ?>
	<ul class="sitemap__list">
		<?php foreach ( $args['items'] as $item ) : ?>
			<li><a href="<?php echo esc_url( get_permalink( $item ) ); ?>"><?php echo esc_html( get_the_title( $item ) ); ?></a></li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
