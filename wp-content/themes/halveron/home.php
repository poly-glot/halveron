<?php
get_header();

$news_id = (int) get_option( 'page_for_posts' );

get_template_part( 'template-parts/thinking-header', null, array( 'page_id' => $news_id ) );

update_post_thumbnail_cache();
?>
<section class="section filter" aria-labelledby="all-title">
	<div class="container">
		<h2 class="visually-hidden" id="all-title">All articles</h2>
		<?php foreach ( array_keys( HALVERON_NEWS_FILTERS ) as $anchor ) : ?>
			<span class="filter__target" id="<?php echo esc_attr( $anchor ); ?>"></span>
		<?php endforeach; ?>
		<nav aria-label="Filter by type">
			<ul class="filter-tabs">
				<?php foreach ( HALVERON_NEWS_FILTERS as $anchor => $label ) : ?>
					<li><a class="filter-tabs__link" href="#<?php echo esc_attr( $anchor ); ?>"><?php echo esc_html( $label ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<ul class="grid-3 filter__list">
			<?php while ( have_posts() ) : ?>
				<?php the_post(); ?>
				<li data-type="<?php echo esc_attr( halveron_insight_type( get_post() ) ); ?>">
					<?php get_template_part( 'template-parts/insight-card', null, array( 'date' => true, 'post' => get_post() ) ); ?>
				</li>
			<?php endwhile; ?>
		</ul>
	</div>
</section>
<?php
get_footer();
