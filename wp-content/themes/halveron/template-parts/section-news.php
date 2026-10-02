<?php
$page = $args['page'];

get_template_part( 'template-parts/card-band', null, array(
	'date'    => true,
	'heading' => halveron_text( 'news_heading', $page->ID ),
	'id'      => $page->post_name . '-news-title',
	'posts'   => halveron_ordered( halveron_meta( 'news_posts', $page->ID ), 'post' ),
) );
