<?php
get_header();

$training_page = halveron_page( 'training' );
$courses       = array_fill_keys( array_keys( HALVERON_COURSE_GROUPS ), array() );

foreach ( $GLOBALS['wp_query']->posts as $course ) {
	$group               = isset( HALVERON_COURSE_GROUPS[ halveron_text( 'course_type', $course->ID ) ] ) ? halveron_text( 'course_type', $course->ID ) : 'open';
	$courses[ $group ][] = $course;
}

get_template_part( 'template-parts/sub-header', null, array(
	'tabs'  => halveron_tabs( halveron_option_rows( 'courses_archive_tabs' ), (string) get_post_type_archive_link( 'course' ) ),
	'title' => $training_page ? halveron_text( 'section_title', $training_page->ID ) : post_type_archive_title( '', false ),
) );
?>
<section class="section band--purple" aria-labelledby="courses-title">
	<div class="with-aside container">
		<div class="with-aside__main">
			<h2 class="visually-hidden" id="courses-title">Training courses</h2>
			<p class="lede"><?php echo esc_html( halveron_option( 'courses_archive_intro' ) ); ?></p>
			<?php foreach ( HALVERON_COURSE_GROUPS as $group => $heading ) : ?>
				<?php if ( $courses[ $group ] ) : ?>
					<h3 class="rule-heading" id="<?php echo esc_attr( $heading['anchor'] ); ?>"><?php echo esc_html( $heading['title'] ); ?></h3>
					<?php get_template_part( 'template-parts/course-list', null, array( 'courses' => $courses[ $group ] ) ); ?>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
		<aside class="with-aside__aside" aria-labelledby="enquiries-title">
			<h2 class="rule-heading" id="enquiries-title"><?php echo esc_html( halveron_option( 'courses_enquiries_heading' ) ); ?></h2>
			<?php echo halveron_paragraphs( halveron_option( 'courses_enquiries_text' ) ); ?>
			<div class="contact-aside contact-aside--boxed">
				<?php echo halveron_email( halveron_option( 'training_email' ) ); ?>
				<?php echo halveron_phone( halveron_option( 'training_phone' ), halveron_option( 'training_phone_href' ) ); ?>
			</div>
		</aside>
	</div>
</section>
<?php
get_footer();
