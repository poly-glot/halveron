<?php
get_header();

$capabilities = $GLOBALS['wp_query']->posts;

get_template_part( 'template-parts/page-header', null, array(
	'after'      => 'template-parts/capability-wheel',
	'after_args' => array( 'capabilities' => $capabilities ),
	'title'      => post_type_archive_title( '', false ),
) );

get_template_part( 'template-parts/index-grid', null, array( 'items' => $capabilities ) );

get_footer();
