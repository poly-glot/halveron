<dl class="facts">
	<?php foreach ( $args['facts'] as $label => $value ) : ?>
		<div class="facts__item"><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo $value; ?></dd></div>
	<?php endforeach; ?>
</dl>
