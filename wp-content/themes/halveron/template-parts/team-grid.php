<section class="section" aria-labelledby="<?php echo esc_attr( $args['id'] ); ?>">
	<div class="container">
		<ul class="team-grid">
			<li class="team-grid__intro">
				<h2 class="display" id="<?php echo esc_attr( $args['id'] ); ?>"><?php echo esc_html( $args['heading'] ); ?></h2>
				<?php echo halveron_paragraphs( $args['intro'] ); ?>
			</li>
			<?php foreach ( $args['people'] as $person ) : ?>
				<?php $name = get_the_title( $person ); ?>
				<li class="team-card">
					<h3 class="team-card__name"><?php echo esc_html( $name ); ?></h3>
					<p class="team-card__role"><?php echo esc_html( halveron_text( 'role', $person->ID ) ); ?></p>
					<?php echo halveron_portrait( $person, 'team-card__portrait' ); ?>
					<p class="team-card__quote"><?php echo esc_html( halveron_quote( halveron_text( 'quote', $person->ID ) ) ); ?></p>
					<a class="team-card__email" href="<?php echo esc_url( halveron_mailto( halveron_text( 'email', $person->ID ) ) ); ?>">Email<span class="visually-hidden"> <?php echo esc_html( $name ); ?></span></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
