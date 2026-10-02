<?php
get_header();

get_template_part( 'template-parts/page-header', null, array( 'title' => wp_strip_all_tags( get_the_archive_title() ) ) );

update_post_thumbnail_cache();
?>
<div class="section">
	<div class="container">
		<?php get_template_part( 'template-parts/insight-grid', null, array( 'date' => true, 'posts' => $GLOBALS['wp_query']->posts ) ); ?>
	</div>
</div>
<?php
get_footer();
