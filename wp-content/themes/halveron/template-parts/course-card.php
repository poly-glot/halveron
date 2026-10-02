<?php
$course     = $args['course'];
$title      = get_the_title( $course );
$next_date  = halveron_text( 'next_date', $course->ID );
$second_row = '' === $next_date ? halveron_text( 'card_line', $course->ID ) : 'Next course: ' . halveron_date( $next_date, 'j F Y' );
?>
<article class="course-card">
	<h4 class="course-card__title"><?php echo esc_html( $title ); ?></h4>
	<p class="course-card__facts"><?php echo esc_html( halveron_text( 'accreditation', $course->ID ) ); ?><br><?php echo esc_html( $second_row ); ?></p>
	<p class="course-card__text"><?php echo esc_html( halveron_text( 'summary', $course->ID ) ); ?></p>
	<a class="button" href="<?php echo esc_url( get_permalink( $course ) ); ?>">View course details and book<span class="visually-hidden"> for <?php echo esc_html( $title ); ?></span></a>
</article>
