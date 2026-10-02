<?php
$contact_id = halveron_page_id( 'contact' );
?>
<h2 class="<?php echo esc_attr( $args['heading_class'] ?? 'display' ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>"><?php echo esc_html( halveron_text( 'contact_thank_you_heading', $contact_id ) ); ?></h2>
<p class="form-panel__intro" role="status"><?php echo esc_html( halveron_text( 'contact_thank_you_text', $contact_id ) ); ?></p>
