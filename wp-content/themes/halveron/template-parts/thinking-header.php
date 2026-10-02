<?php
$featured = halveron_ordered( halveron_meta( 'thinking_featured', halveron_page_id( 'thinking' ) ), 'post' );

get_template_part( 'template-parts/page-header', null, array(
	'after'      => $featured ? 'template-parts/featured-carousel' : '',
	'after_args' => array( 'posts' => $featured ),
	'text'       => halveron_text( 'intro', $args['page_id'] ),
	'title'      => get_the_title( $args['page_id'] ),
) );
