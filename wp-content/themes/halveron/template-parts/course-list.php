<ul class="course-list">
	<?php foreach ( $args['courses'] as $course ) : ?>
		<li>
			<?php get_template_part( 'template-parts/course-card', null, array( 'course' => $course ) ); ?>
		</li>
	<?php endforeach; ?>
</ul>
