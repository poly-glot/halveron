<?php
$page = $args['page'];

get_template_part( 'template-parts/sub-header', null, array(
	'image'     => $args['image'] ?? '',
	'lines'     => $args['lines'] ?? array(),
	'modifier'  => $args['modifier'] ?? '',
	'statement' => ( $args['statement'] ?? true ) ? halveron_text( 'statement', $page->ID ) : '',
	'strip'     => $args['strip'] ?? '',
	'tabs'      => halveron_tabs( halveron_rows( 'section_tabs', $page->ID ), (string) get_permalink( get_queried_object_id() ) ),
	'title'     => halveron_text( 'section_title', $page->ID ),
	'title_tag' => $args['title_tag'] ?? 'h1',
) );
