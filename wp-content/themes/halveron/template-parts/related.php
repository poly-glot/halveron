<?php
get_template_part( 'template-parts/card-band', null, array(
	'heading' => 'Latest related content',
	'id'      => 'related-title',
	'posts'   => halveron_ordered( halveron_meta( 'related', $args['post_id'] ), 'post' ),
) );
