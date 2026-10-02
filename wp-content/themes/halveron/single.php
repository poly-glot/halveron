<?php
get_header();

while ( have_posts() ) :
	the_post();

	$insight_id     = get_the_ID();
	$insight_type   = halveron_insight_type( get_post() );
	$insight        = HALVERON_INSIGHT_TYPES[ $insight_type ];
	$author_profile = halveron_post( halveron_meta( 'author_profile', $insight_id ), 'person' );
	$capabilities   = halveron_ordered( halveron_meta( 'capabilities', $insight_id ), 'capability' );
	$outcomes       = 'case-study' === $insight_type ? halveron_rows( 'outcomes', $insight_id ) : array();
	$covers         = 'white-paper' === $insight_type ? halveron_rows( 'paper_covers', $insight_id ) : array();
	$audience       = 'white-paper' === $insight_type ? halveron_text( 'paper_audience', $insight_id ) : '';
	?>
	<article>
		<header class="article-header">
			<div class="article-header__inner container">
				<?php get_template_part( 'template-parts/breadcrumb' ); ?>
				<p class="article-header__section"><?php echo esc_html( $insight['section'] ); ?></p>
				<h1 class="article-header__title"><?php echo esc_html( get_the_title() ); ?></h1>
				<p class="article-header__meta"><span class="tag"><?php echo esc_html( $insight['tag'] ); ?></span><span><span class="visually-hidden">Published </span><time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( get_the_date( 'j F Y' ) ); ?></time></span></p>
				<?php if ( 'white-paper' === $insight_type ) : ?>
					<div class="button-row">
						<a class="button" href="#download"><?php echo esc_html( $insight['link'] ); ?></a>
					</div>
				<?php endif; ?>
			</div>
		</header>
		<div class="article-lead">
			<div class="container">
				<?php echo halveron_thumbnail( get_post(), 'article-lead__image', array( 'loading' => false ) ); ?>
			</div>
		</div>
		<div class="with-aside container article-body">
			<div class="with-aside__main">
				<div class="prose">
					<?php the_content(); ?>
					<?php if ( $covers ) : ?>
						<h2>What the paper covers</h2>
						<ul>
							<?php foreach ( $covers as $cover ) : ?>
								<li><?php echo esc_html( (string) ( $cover['text'] ?? '' ) ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<?php if ( '' !== $audience ) : ?>
						<h2>Who it is for</h2>
						<?php echo halveron_rich( $audience ); ?>
					<?php endif; ?>
				</div>
				<?php if ( $outcomes ) : ?>
					<div class="stat-panel">
						<ul class="stat-grid">
							<?php foreach ( $outcomes as $outcome ) : ?>
								<li class="stat"><p class="stat__figure"><?php echo esc_html( (string) ( $outcome['figure'] ?? '' ) ); ?></p><p class="stat__label"><?php echo esc_html( (string) ( $outcome['label'] ?? '' ) ); ?></p></li>
							<?php endforeach; ?>
						</ul>
					</div>
					<div class="prose">
						<?php echo halveron_rich( halveron_text( 'outcomes_after', $insight_id ) ); ?>
					</div>
				<?php endif; ?>
				<p class="meta"><?php echo esc_html( halveron_option( 'insight_disclaimer' ) ); ?></p>
			</div>
			<aside class="with-aside__aside" aria-label="About this article">
				<?php if ( $author_profile ) : ?>
					<?php
					$author_name  = get_the_title( $author_profile );
					$author_email = halveron_text( 'email', $author_profile->ID );
					?>
					<div class="author-aside">
						<?php echo halveron_portrait( $author_profile, 'author-aside__portrait' ); ?>
						<p class="author-aside__label">Author:</p>
						<p class="author-aside__name"><?php echo esc_html( $author_name ); ?></p>
						<p><?php echo esc_html( halveron_text( 'role', $author_profile->ID ) ); ?></p>
						<?php echo halveron_phone( halveron_text( 'phone', $author_profile->ID ), halveron_text( 'phone_href', $author_profile->ID ) ); ?>
						<?php echo halveron_email( $author_email ); ?>
						<a class="button" href="<?php echo esc_url( halveron_mailto( $author_email ) ); ?>">Contact<span class="visually-hidden"> <?php echo esc_html( $author_name ); ?></span></a>
					</div>
				<?php endif; ?>
				<?php if ( $capabilities ) : ?>
					<section class="related-caps" aria-labelledby="related-caps-title">
						<h2 class="related-caps__title" id="related-caps-title">Related capabilities</h2>
						<ul class="related-caps__list">
							<?php foreach ( $capabilities as $capability ) : ?>
								<li>
									<a class="related-caps__link" href="<?php echo esc_url( get_permalink( $capability ) ); ?>">
										<svg class="icon icon--capability" aria-hidden="true" focusable="false"><use href="#icon-<?php echo esc_attr( $capability->post_name ); ?>" /></svg>
										<?php echo esc_html( get_the_title( $capability ) ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>
			</aside>
		</div>
	</article>
	<?php
	if ( 'white-paper' === $insight_type ) {
		get_template_part( 'template-parts/download-form', null, array( 'post' => get_post() ) );
	}

	get_template_part( 'template-parts/related', null, array( 'post_id' => $insight_id ) );
endwhile;

get_footer();
