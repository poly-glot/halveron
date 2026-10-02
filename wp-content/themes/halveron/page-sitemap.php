<?php
get_header();

$sitemap_groups = array(
	array(
		'heading' => 'Our capabilities',
		'items'   => halveron_capabilities(),
		'url'     => (string) get_post_type_archive_link( 'capability' ),
	),
	array(
		'heading' => 'Our sectors',
		'items'   => halveron_by_menu_order( 'sector' ),
		'url'     => (string) get_post_type_archive_link( 'sector' ),
	),
	array(
		'heading' => 'Our thinking',
		'items'   => halveron_query( array(
			'post_type'      => 'post',
			'posts_per_page' => -1,
		) ),
		'url'     => halveron_page_url( 'thinking' ),
	),
);
$events         = halveron_query( array(
	'meta_key'       => 'event_date',
	'order'          => 'DESC',
	'orderby'        => 'meta_value',
	'post_type'      => 'event',
	'posts_per_page' => -1,
) );
$legal_pages    = array_filter( array_map( 'halveron_page', array( 'privacy', 'cookies', 'terms', 'accessibility', 'sitemap' ) ) );
$main_links     = array(
	'Home'              => home_url( '/' ),
	'About us'          => halveron_page_url( 'about' ),
	'Contact'           => halveron_page_url( 'contact' ),
	'News and insights' => halveron_page_url( 'news' ),
	'Search'            => home_url( '/search/' ),
);

while ( have_posts() ) :
	the_post();

	get_template_part( 'template-parts/page-header', null, array(
		'lede'  => halveron_text( 'intro' ),
		'title' => get_the_title(),
	) );
endwhile;
?>
<div class="container section">
	<ul class="sitemap">
		<li>
			<h2 class="sitemap__heading">Main pages</h2>
			<ul class="sitemap__list">
				<?php foreach ( $main_links as $label => $url ) : ?>
					<li><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</li>
		<?php foreach ( $sitemap_groups as $group ) : ?>
			<li>
				<h2 class="sitemap__heading"><a href="<?php echo esc_url( $group['url'] ); ?>"><?php echo esc_html( $group['heading'] ); ?></a></h2>
				<?php get_template_part( 'template-parts/sitemap-list', null, array( 'items' => $group['items'] ) ); ?>
			</li>
		<?php endforeach; ?>
		<li>
			<h2 class="sitemap__heading"><a href="<?php echo esc_url( halveron_page_url( 'training' ) ); ?>">Training</a></h2>
			<ul class="sitemap__list">
				<li><a href="<?php echo esc_url( (string) get_post_type_archive_link( 'course' ) ); ?>"><?php echo esc_html( halveron_archive_title( '', 'course' ) ); ?></a>
					<?php get_template_part( 'template-parts/sitemap-list', null, array( 'items' => halveron_by_menu_order( 'course' ) ) ); ?>
				</li>
			</ul>
		</li>
		<li>
			<h2 class="sitemap__heading"><a href="<?php echo esc_url( halveron_page_url( 'careers' ) ); ?>">Careers</a></h2>
			<?php get_template_part( 'template-parts/sitemap-list', null, array( 'items' => array_filter( array( halveron_page( 'careers/opportunities' ) ) ) ) ); ?>
		</li>
		<li>
			<h2 class="sitemap__heading"><a href="<?php echo esc_url( halveron_page_url( 'forum' ) ); ?>">Forum and events</a></h2>
			<?php get_template_part( 'template-parts/sitemap-list', null, array( 'items' => $events ) ); ?>
		</li>
		<li>
			<h2 class="sitemap__heading"><a href="<?php echo esc_url( halveron_page_url( 'conference' ) ); ?>">Conference</a></h2>
		</li>
		<li>
			<h2 class="sitemap__heading">Legal and accessibility</h2>
			<ul class="sitemap__list">
				<?php foreach ( $legal_pages as $legal_page ) : ?>
					<li><a href="<?php echo esc_url( get_permalink( $legal_page ) ); ?>"><?php echo esc_html( get_the_title( $legal_page ) ); ?></a></li>
				<?php endforeach; ?>
				<li><a href="<?php echo esc_url( home_url( '/404.html' ) ); ?>">Page not found</a></li>
			</ul>
		</li>
	</ul>
</div>
<?php
get_footer();
