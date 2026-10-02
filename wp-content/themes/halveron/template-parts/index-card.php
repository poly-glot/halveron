<?php
$item  = $args['item'];
$title = get_the_title( $item );
?>
<article class="index-card">
	<h2 class="index-card__title"><?php echo esc_html( $title ); ?></h2>
	<?php if ( 'capability' === $item->post_type ) : ?>
		<svg class="icon icon--capability index-card__icon" aria-hidden="true" focusable="false"><use href="#icon-<?php echo esc_attr( $item->post_name ); ?>" /></svg>
	<?php else : ?>
		<?php echo halveron_image( (int) halveron_meta( 'image', $item->ID ), 'index-card__image', array( 'alt' => '', 'loading' => $args['first'] ? false : 'lazy' ) ); ?>
	<?php endif; ?>
	<p class="index-card__text"><?php echo esc_html( halveron_text( 'summary', $item->ID ) ); ?></p>
	<a class="index-card__more" href="<?php echo esc_url( get_permalink( $item ) ); ?>">View more<span class="visually-hidden"> about <?php echo esc_html( $title ); ?></span></a>
</article>
