<?php if ( '' !== $args['quote'] ) : ?>
	<blockquote class="pull-quote">
		<p><?php echo esc_html( halveron_quote( $args['quote'] ) ); ?></p>
		<?php if ( '' !== $args['attribution'] ) : ?>
			<cite class="pull-quote__attribution"><?php echo esc_html( $args['attribution'] ); ?></cite>
		<?php endif; ?>
	</blockquote>
<?php endif; ?>
