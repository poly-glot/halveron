<?php
get_header();

$opportunities_page = get_queried_object();
$careers_page       = $opportunities_page->post_parent ? get_post( $opportunities_page->post_parent ) : halveron_page( 'careers' );
$jobs               = array_map(
	fn ( WP_Post $job ): array => array(
		'job'   => $job,
		'title' => get_the_title( $job ),
	),
	halveron_by_menu_order( 'job' )
);

if ( $careers_page ) {
	get_template_part( 'template-parts/section-header', null, array(
		'modifier'  => 'sub-header--black',
		'page'      => $careers_page,
		'statement' => false,
		'title_tag' => 'p',
	) );
}
?>
<section class="section band--purple" aria-labelledby="opportunities-title">
	<div class="container">
		<div class="stack">
			<h1 class="display" id="opportunities-title"><?php echo esc_html( get_the_title( $opportunities_page ) ); ?></h1>
			<div class="prose">
				<?php echo halveron_rich( halveron_text( 'opportunities_intro', $opportunities_page->ID ) ); ?>
			</div>
			<h2 class="rule-heading"><?php echo esc_html( halveron_text( 'opportunities_lead', $opportunities_page->ID ) ); ?></h2>
		</div>
		<?php
		get_template_part( 'template-parts/accordion', null, array(
			'body'  => 'template-parts/job-body',
			'class' => 'accordion accordion--dark',
			'items' => $jobs,
		) );
		?>
	</div>
</section>
<?php
if ( $careers_page ) {
	get_template_part( 'template-parts/section-news', null, array( 'page' => $careers_page ) );
}

get_footer();
