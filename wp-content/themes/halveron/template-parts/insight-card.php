<?php
$card    = $args['post'];
$insight = halveron_insight( $card );
$compact = ! empty( $args['compact'] );
$title   = get_the_title( $card );
?>
<article class="<?php echo $compact ? 'insight-card insight-card--compact' : 'insight-card'; ?>">
	<h3 class="insight-card__title"><a href="<?php echo esc_url( get_permalink( $card ) ); ?>"><?php echo esc_html( $title ); ?></a></h3>
	<div class="insight-card__media">
		<?php echo halveron_thumbnail( $card, 'insight-card__image', array( 'loading' => 'lazy' ) ); ?>
		<p class="tag"><?php echo esc_html( $insight['tag'] ); ?></p>
	</div>
	<?php if ( ! empty( $args['date'] ) ) : ?>
		<p class="insight-card__date"><span class="visually-hidden">Published </span><time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d', $card ) ); ?>"><?php echo esc_html( get_the_date( 'j F Y', $card ) ); ?></time></p>
	<?php endif; ?>
	<?php if ( ! $compact ) : ?>
		<p class="insight-card__excerpt"><?php echo esc_html( $card->post_excerpt ); ?></p>
		<a class="text-link" href="<?php echo esc_url( halveron_insight_link( $card ) ); ?>"><?php echo esc_html( $insight['link'] ); ?><span class="visually-hidden">: <?php echo esc_html( $title ); ?></span><svg class="icon" aria-hidden="true" focusable="false"><use href="#icon-chevron-right" /></svg></a>
	<?php endif; ?>
</article>
