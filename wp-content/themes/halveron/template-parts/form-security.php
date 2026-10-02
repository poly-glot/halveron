<input type="hidden" name="action" value="<?php echo esc_attr( $args['action'] ); ?>">
<input type="hidden" name="halveron_nonce" value="<?php echo esc_attr( wp_create_nonce( $args['action'] ) ); ?>">
<?php foreach ( $args['fields'] ?? array() as $name => $value ) : ?>
	<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $value ); ?>">
<?php endforeach; ?>
<div class="visually-hidden" aria-hidden="true">
	<label for="<?php echo esc_attr( $args['honeypot'] ); ?>">Leave this field empty</label>
	<input id="<?php echo esc_attr( $args['honeypot'] ); ?>" type="text" name="website" tabindex="-1" autocomplete="off">
</div>
