<?php
$event = $args['event'];
$title = get_the_title( $event );
$date  = halveron_text( 'event_date', $event->ID );
?>
<article class="event-card">
	<div class="event-card__body">
		<h3 class="event-card__title"><?php echo esc_html( $title ); ?></h3>
		<p class="event-card__date"><time datetime="<?php echo esc_attr( $date ); ?>"><?php echo esc_html( halveron_date( $date, 'l j F Y' ) ); ?></time></p>
		<p class="event-card__summary"><?php echo esc_html( halveron_text( 'summary', $event->ID ) ); ?></p>
		<a class="button" href="<?php echo esc_url( get_permalink( $event ) ); ?>">View detail<span class="visually-hidden">: <?php echo esc_html( $title ); ?></span></a>
	</div>
	<?php echo halveron_thumbnail( $event, 'event-card__image', array( 'loading' => 'lazy' ) ); ?>
</article>
