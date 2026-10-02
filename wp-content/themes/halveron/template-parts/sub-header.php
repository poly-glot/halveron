<?php
$title_tag = 'p' === ( $args['title_tag'] ?? 'h1' ) ? 'p' : 'h1';
$classes   = trim( 'sub-header ' . ( $args['modifier'] ?? '' ) );
?>
<header class="<?php echo esc_attr( $classes ); ?>">
	<?php echo $args['image'] ?? ''; ?>
	<div class="sub-header__inner container">
		<?php get_template_part( 'template-parts/breadcrumb' ); ?>
		<<?php echo $title_tag; ?> class="sub-header__title"><?php echo esc_html( $args['title'] ); ?></<?php echo $title_tag; ?>>
		<?php if ( ! empty( $args['tabs'] ) ) : ?>
			<nav aria-label="In this section">
				<ul class="sub-header__tabs">
					<?php foreach ( $args['tabs'] as $tab ) : ?>
						<li><a class="sub-header__tab" href="<?php echo esc_url( $tab['url'] ); ?>"<?php echo $tab['current'] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $tab['label'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endif; ?>
		<?php if ( '' !== ( $args['statement'] ?? '' ) ) : ?>
			<p class="sub-header__statement"><?php echo esc_html( $args['statement'] ); ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $args['lines'] ) ) : ?>
			<p class="sub-header__lines"><?php foreach ( $args['lines'] as $line ) : ?><span><?php echo esc_html( $line ); ?></span><?php endforeach; ?></p>
		<?php endif; ?>
	</div>
	<?php echo $args['strip'] ?? ''; ?>
</header>
