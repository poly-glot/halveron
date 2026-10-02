<ul class="course-list">
	<?php foreach ( $args['events'] as $event ) : ?>
		<li>
			<?php get_template_part( 'template-parts/event-card', null, array( 'event' => $event ) ); ?>
		</li>
	<?php endforeach; ?>
</ul>
