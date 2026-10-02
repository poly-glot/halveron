<?php
get_header();

get_template_part( 'template-parts/page-header', null, array(
	'breadcrumb' => false,
	'title'      => 'We cannot find that page',
) );
?>
<section class="section" aria-label="Where to go next">
	<div class="container">
		<div class="prose">
			<p>The page may have moved, or the address may be mistyped. Try one of these instead, or use the search at the top of the page.</p>
		</div>
		<div class="button-row">
			<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>">Go to the home page</a>
			<a class="button button--outline" href="<?php echo esc_url( (string) get_post_type_archive_link( 'capability' ) ); ?>">Browse our capabilities</a>
		</div>
	</div>
</section>
<?php
get_footer();
