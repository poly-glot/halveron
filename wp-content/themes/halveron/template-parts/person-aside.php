<?php
$person = $args['person'];
$email  = halveron_text( 'email', $person->ID );
$name   = halveron_first_name( $person );

get_template_part( 'template-parts/contact-aside', null, array(
	'button_label' => $args['button_label'] ?? 'Contact ' . $name,
	'button_url'   => $args['button_url'] ?? halveron_mailto( $email ),
	'email'        => $email,
	'extra_email'  => $args['extra_email'] ?? '',
	'intro'        => $args['intro'] ?? 'Let us help you. Talk to ' . $name . ':',
	'person'       => $person,
	'phone'        => halveron_text( 'phone', $person->ID ),
	'phone_href'   => halveron_text( 'phone_href', $person->ID ),
) );
