<?php
$post_id = $args['post_id'];
$steps   = array_slice( halveron_rows( 'diagram_steps', $post_id ), 0, count( HALVERON_FLOW_NODES ) );
?>
<figure class="flow-diagram">
	<svg class="flow-diagram__svg" viewBox="0 0 680 470" role="img" aria-labelledby="flow-title flow-desc">
		<title id="flow-title"><?php echo esc_html( halveron_text( 'diagram_title', $post_id ) ); ?></title>
		<desc id="flow-desc"><?php echo esc_html( halveron_text( 'diagram_description', $post_id ) ); ?></desc>
		<circle class="flow-diagram__spoke" cx="340" cy="235" r="165" />
		<?php foreach ( array_slice( HALVERON_FLOW_SPOKES, 0, count( $steps ) ) as $spoke ) : ?>
			<path class="flow-diagram__spoke" d="<?php echo esc_attr( $spoke ); ?>" />
		<?php endforeach; ?>
		<circle class="flow-diagram__hub" cx="340" cy="235" r="72" />
		<?php foreach ( $steps as $index => $step ) : ?>
			<?php $node = HALVERON_FLOW_NODES[ $index ]; ?>
			<circle class="flow-diagram__node" cx="<?php echo esc_attr( (string) $node['x'] ); ?>" cy="<?php echo esc_attr( (string) $node['y'] ); ?>" r="24" />
			<text class="flow-diagram__number" x="<?php echo esc_attr( (string) $node['x'] ); ?>" y="<?php echo esc_attr( (string) ( $node['y'] + 5.5 ) ); ?>"><?php echo esc_html( (string) ( $index + 1 ) ); ?></text>
			<text class="<?php echo esc_attr( $node['label'] ); ?>" x="<?php echo esc_attr( (string) $node['label_x'] ); ?>" y="<?php echo esc_attr( (string) $node['label_y'] ); ?>"><?php echo esc_html( (string) ( $step['label'] ?? '' ) ); ?></text>
		<?php endforeach; ?>
		<text class="flow-diagram__hub-label"><?php echo halveron_tspans( halveron_lines( halveron_text( 'diagram_hub', $post_id ) ), 340, 242, 22 ); ?></text>
	</svg>
	<figcaption>
		<ol class="flow-diagram__steps">
			<?php foreach ( $steps as $step ) : ?>
				<li class="flow-diagram__step"><?php echo esc_html( (string) ( $step['label'] ?? '' ) ); ?></li>
			<?php endforeach; ?>
		</ol>
	</figcaption>
</figure>
