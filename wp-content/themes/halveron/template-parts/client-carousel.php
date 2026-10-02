<?php
get_template_part( 'template-parts/logo-carousel', null, array(
	'label'         => 'Our clients',
	'next'          => 'Next clients',
	'organisations' => $args['organisations'],
	'previous'      => 'Previous clients',
) );
