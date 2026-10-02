<header class="page-header">
	<div class="page-header__inner container">
		<?php if ( $args['breadcrumb'] ?? true ) : ?>
			<?php get_template_part( 'template-parts/breadcrumb' ); ?>
		<?php endif; ?>
		<?php if ( ! empty( $args['icon'] ) ) : ?>
			<h1 class="page-header__title">
				<?php echo esc_html( $args['title'] ); ?>
				<svg class="icon icon--capability" aria-hidden="true" focusable="false"><use href="#icon-<?php echo esc_attr( $args['icon'] ); ?>" /></svg>
			</h1>
		<?php else : ?>
			<h1 class="page-header__title"><?php echo esc_html( $args['title'] ); ?></h1>
		<?php endif; ?>
		<?php if ( '' !== ( $args['text'] ?? '' ) ) : ?>
			<div class="page-header__text prose">
				<?php echo halveron_paragraphs( $args['text'] ); ?>
			</div>
		<?php endif; ?>
		<?php if ( '' !== ( $args['lede'] ?? '' ) ) : ?>
			<p class="page-header__lede"><?php echo esc_html( $args['lede'] ); ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $args['after'] ) ) : ?>
			<?php get_template_part( $args['after'], null, $args['after_args'] ?? array() ); ?>
		<?php endif; ?>
	</div>
</header>
