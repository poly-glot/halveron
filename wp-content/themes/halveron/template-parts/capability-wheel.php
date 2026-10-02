<?php
$capabilities = array_slice( $args['capabilities'], 0, count( HALVERON_WHEEL ) );
?>
<div class="page-header__split">
	<svg class="wheel" viewBox="0 0 400 400" role="img" aria-labelledby="wheel-title wheel-desc">
		<title id="wheel-title"><?php echo esc_html( halveron_option( 'capabilities_wheel_title' ) ); ?></title>
		<desc id="wheel-desc"><?php echo esc_html( halveron_option( 'capabilities_wheel_description' ) ); ?></desc>
		<?php foreach ( $capabilities as $index => $capability ) : ?>
			<?php $segment = HALVERON_WHEEL[ $index ]; ?>
			<path class="<?php echo esc_attr( $segment['segment'] ); ?>" d="<?php echo esc_attr( $segment['d'] ); ?>" />
			<text class="<?php echo esc_attr( $segment['label'] ); ?>"><?php echo halveron_tspans( halveron_lines( halveron_text( 'wheel_label', $capability->ID ) ), $segment['x'], $segment['y'], 15 ); ?></text>
		<?php endforeach; ?>
		<circle class="wheel__hub" cx="200" cy="200" r="70" />
		<text class="wheel__hub-label"><?php echo halveron_tspans( halveron_lines( halveron_option( 'capabilities_wheel_hub' ) ), 200, 206, 20 ); ?></text>
	</svg>
	<p class="page-header__lede"><?php echo esc_html( halveron_option( 'capabilities_archive_intro' ) ); ?></p>
</div>
