<ul class="partner-list grid-4">
	<?php foreach ( $args['organisations'] as $organisation ) : ?>
		<li class="partner-list__item">
			<?php echo halveron_logo( $organisation, 'partner-list__logo' ); ?>
			<p class="partner-list__text"><?php echo esc_html( halveron_text( 'descriptor', $organisation->ID ) ); ?></p>
		</li>
	<?php endforeach; ?>
</ul>
