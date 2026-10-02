<?php
$items = array_values( array_filter( halveron_breadcrumb_items() ) );
$last  = count( $items ) - 1;
?>
<nav class="breadcrumb" aria-label="Breadcrumb">
	<ol class="breadcrumb__list">
		<?php foreach ( $items as $index => $item ) : ?>
			<?php if ( $index === $last ) : ?>
				<li class="breadcrumb__item"><span class="breadcrumb__current" aria-current="page"><?php echo esc_html( $item['label'] ); ?></span></li>
			<?php else : ?>
				<li class="breadcrumb__item"><a class="breadcrumb__link" href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a></li>
			<?php endif; ?>
		<?php endforeach; ?>
	</ol>
</nav>
