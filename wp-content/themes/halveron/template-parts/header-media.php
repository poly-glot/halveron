<div class="page-header__media">
	<?php
	get_template_part( 'template-parts/media-block', null, array(
		'image' => halveron_image( (int) halveron_meta( 'image', $args['post_id'] ), 'media-block__image', array( 'alt' => '', 'loading' => false ) ),
		'text'  => halveron_text( 'intro', $args['post_id'] ),
	) );
	?>
</div>
