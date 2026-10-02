<?php
$field_id    = $args['prefix'] . '-' . $args['name'];
$is_required = ! empty( $args['error'] );
$described   = array_filter( array(
	empty( $args['hint'] ) ? '' : $field_id . '-hint',
	$is_required ? $field_id . '-error' : '',
) );
$tag         = 'message' === $args['name'] ? 'textarea' : 'input';
$attributes  = array(
	'class'            => 'field__control',
	'id'               => $field_id,
	'type'             => 'textarea' === $tag ? null : $args['type'],
	'name'             => $args['name'],
	'autocomplete'     => $args['autocomplete'] ?? null,
	'required'         => $is_required ? 'required' : null,
	'aria-describedby' => $described ? implode( ' ', $described ) : null,
);
$markup      = '';

foreach ( array_filter( $attributes, 'is_string' ) as $attribute => $value ) {
	$markup .= 'required' === $attribute ? ' required' : sprintf( ' %s="%s"', $attribute, esc_attr( $value ) );
}
?>
<div class="field">
	<label class="field__label" for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $args['label'] ); ?><?php if ( ! $is_required ) : ?> <span class="field__optional">(optional)</span><?php endif; ?></label>
	<?php if ( 'textarea' === $tag ) : ?>
		<textarea<?php echo $markup; ?>></textarea>
	<?php else : ?>
		<input<?php echo $markup; ?>>
	<?php endif; ?>
	<?php if ( ! empty( $args['hint'] ) ) : ?>
		<p class="field__hint" id="<?php echo esc_attr( $field_id ); ?>-hint"><?php echo esc_html( $args['hint'] ); ?></p>
	<?php endif; ?>
	<?php if ( $is_required ) : ?>
		<p class="field__error" id="<?php echo esc_attr( $field_id ); ?>-error"><?php echo esc_html( $args['error'] ); ?></p>
	<?php endif; ?>
</div>
