<?php
$result = halveron_result( $args['item'] );
?>
<li class="result">
	<p class="result__type"><?php echo esc_html( $result['type'] ); ?></p>
	<h3 class="result__title"><?php echo esc_html( $result['title'] ); ?></h3>
	<p class="result__excerpt"><?php echo esc_html( $result['excerpt'] ); ?></p>
	<a class="text-link" href="<?php echo esc_url( get_permalink( $args['item'] ) ); ?>">View more<span class="visually-hidden">: <?php echo esc_html( $result['title'] ); ?></span><svg class="icon" aria-hidden="true" focusable="false"><use href="#icon-chevron-right" /></svg></a>
</li>
