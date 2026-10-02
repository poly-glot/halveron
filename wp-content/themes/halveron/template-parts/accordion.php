<div class="<?php echo esc_attr( $args['class'] ?? 'accordion' ); ?>">
	<?php foreach ( array_values( $args['items'] ) as $index => $item ) : ?>
		<details class="accordion__item"<?php echo 0 === $index ? ' open' : ''; ?>>
			<summary class="accordion__summary">
				<h3 class="accordion__title"><?php echo esc_html( $item['title'] ); ?></h3>
				<svg class="icon accordion__icon" aria-hidden="true" focusable="false"><use href="#icon-chevron-down" /></svg>
			</summary>
			<div class="accordion__body prose">
				<?php get_template_part( $args['body'], null, $item ); ?>
			</div>
		</details>
	<?php endforeach; ?>
</div>
