<?php
get_header();

$insights = halveron_query( array(
	'post_type'      => 'post',
	'posts_per_page' => -1,
) );
$by_type  = array();

foreach ( $insights as $insight ) {
	$by_type[ halveron_insight_type( $insight ) ][] = $insight;
}

get_template_part( 'template-parts/thinking-header', null, array( 'page_id' => get_queried_object_id() ) );

$news_url = halveron_page_url( 'news' );
?>
<?php foreach ( HALVERON_THINKING_ROWS as $type => $row ) : ?>
	<?php
	$cards = array_slice( $by_type[ $type ] ?? array(), 0, $row['count'] > 0 ? $row['count'] : null );

	if ( ! $cards ) {
		continue;
	}

	$is_case_study = 'case-study' === $type;
	$anchor        = HALVERON_INSIGHT_TYPES[ $type ]['filter'];
	?>
	<section class="section section--tight"<?php echo $is_case_study ? ' id="case-studies"' : ''; ?> aria-labelledby="<?php echo esc_attr( $row['id'] ); ?>">
		<div class="container">
			<div class="section-head">
				<h2 class="display" id="<?php echo esc_attr( $row['id'] ); ?>"><?php echo esc_html( $row['title'] ); ?></h2>
				<a class="button button--outline" href="<?php echo esc_url( $news_url . '#' . $anchor ); ?>">View all<span class="visually-hidden"><?php echo esc_html( $row['hidden'] ); ?></span></a>
			</div>
			<?php
			get_template_part( 'template-parts/insight-grid', null, array(
				'compact' => $is_case_study,
				'date'    => ! $is_case_study,
				'posts'   => $cards,
			) );
			?>
		</div>
	</section>
<?php endforeach; ?>
<?php
get_footer();
