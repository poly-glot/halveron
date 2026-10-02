<?php
get_header();

$search_query = trim( get_search_query( false ) );
$results      = '' === $search_query ? array() : $GLOBALS['wp_query']->posts;
$count        = count( $results );
$query_markup = '<strong>' . esc_html( $search_query ) . '</strong>';

$summary = match ( true ) {
	'' === $search_query => esc_html( 'Type a word or phrase above to search the site.' ),
	0 === $count         => "We found no results for '" . $query_markup . "'. Try a different word, or browse our capabilities and sectors.",
	default              => sprintf( "We found %d %s for '%s'", $count, 1 === $count ? 'result' : 'results', $query_markup ),
};

get_template_part( 'template-parts/page-header', null, array( 'title' => 'Search' ) );
?>
<section class="search-panel" aria-label="Search">
	<div class="search-panel__inner container">
		<?php get_template_part( 'template-parts/search-form', null, array( 'id' => 'search-page-input', 'value' => $search_query ) ); ?>
	</div>
</section>
<section class="section section--tight" aria-labelledby="results-title">
	<div class="container search-results">
		<h2 class="visually-hidden" id="results-title">Results</h2>
		<p class="search-results__summary" role="status"><?php echo $summary; ?></p>
		<ul class="search-results__list">
			<?php foreach ( $results as $result ) : ?>
				<?php get_template_part( 'template-parts/search-result', null, array( 'item' => $result ) ); ?>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<?php
get_footer();
