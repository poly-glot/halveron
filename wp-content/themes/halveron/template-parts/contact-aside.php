<?php
$person = $args['person'];
?>
<div class="contact-aside">
	<p class="contact-aside__intro"><?php echo esc_html( $args['intro'] ); ?></p>
	<?php echo halveron_portrait( $person, 'contact-aside__portrait' ); ?>
	<p class="contact-aside__name"><?php echo esc_html( get_the_title( $person ) ); ?></p>
	<p class="contact-aside__role"><?php echo esc_html( halveron_text( 'role', $person->ID ) ); ?></p>
	<?php echo halveron_phone( $args['phone'], $args['phone_href'] ); ?>
	<?php echo halveron_email( $args['email'] ); ?>
	<?php if ( ! empty( $args['extra_email'] ) ) : ?>
		<?php echo halveron_email( $args['extra_email'] ); ?>
	<?php endif; ?>
	<a class="button" href="<?php echo esc_url( $args['button_url'] ); ?>"><?php echo esc_html( $args['button_label'] ); ?><?php if ( ! empty( $args['button_hidden'] ) ) : ?><span class="visually-hidden"><?php echo esc_html( $args['button_hidden'] ); ?></span><?php endif; ?></a>
</div>
