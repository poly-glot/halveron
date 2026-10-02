<?php
$main_lines = array(
	'Telephone: ' => halveron_phone( halveron_option( 'phone' ), halveron_option( 'phone_href' ) ),
	'Training: '  => halveron_phone( halveron_option( 'training_phone' ), halveron_option( 'training_phone_href' ) ),
	'Email: '     => halveron_email( halveron_option( 'email' ) ),
);
$team_lines = array(
	'Training and courses: '  => halveron_email( halveron_option( 'training_email' ) ),
	'Careers: '               => halveron_email( halveron_option( 'careers_email' ) ),
	'Lean Service Forum: '    => halveron_email( halveron_option( 'forum_email' ) ),
	'Conference and events: ' => halveron_email( halveron_option( 'events_email' ) ),
);
$join       = fn ( array $lines ): string => implode( '<br>', array_map( fn ( string $label, string $link ): string => esc_html( $label ) . $link, array_keys( $lines ), $lines ) );
?>
<p class="page-header__lede"><?php echo $join( $main_lines ); ?></p>
<p class="meta"><?php echo $join( $team_lines ); ?></p>
