<ul class="training__list">
	<?php foreach ( $args['rows'] as $row ) : ?>
		<li><?php echo esc_html( (string) ( $row['text'] ?? '' ) ); ?></li>
	<?php endforeach; ?>
</ul>
