<?php
get_header();

get_template_part( 'template-parts/page-header', null, array(
	'lede'  => halveron_option( 'sectors_archive_intro' ),
	'title' => post_type_archive_title( '', false ),
) );

get_template_part( 'template-parts/index-grid', null, array( 'items' => $GLOBALS['wp_query']->posts ) );

get_footer();
