<?php

require_once get_theme_file_path( 'inc/constants.php' );
require_once get_theme_file_path( 'inc/helpers.php' );
require_once get_theme_file_path( 'inc/navigation.php' );
require_once get_theme_file_path( 'inc/forms.php' );

function halveron_setup(): void {
	add_rewrite_rule( '^search/?$', 'index.php?s=', 'top' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );

	register_nav_menus( array(
		'primary'   => 'Primary',
		'secondary' => 'Secondary',
		'footer-1'  => 'Footer column 1',
		'footer-2'  => 'Footer column 2',
		'footer-3'  => 'Footer column 3',
		'legal'     => 'Legal',
	) );

	add_image_size( 'hero', 1920, 900, true );
	add_image_size( 'hero-1440', 1440, 675, true );
	add_image_size( 'hero-960', 960, 450, true );
}

add_action( 'after_setup_theme', 'halveron_setup' );

function halveron_stylesheets(): array {
	$css   = get_theme_file_path( 'assets/css/' );
	$parts = array_merge( glob( $css . 'atoms/*.css' ), glob( $css . 'components/*.css' ) );

	return array_merge(
		array( 'base/tokens.css', 'base/base.css', 'base/layout.css' ),
		array_map( fn ( string $path ): string => substr( $path, strlen( $css ) ), $parts )
	);
}

function halveron_enqueue_assets(): void {
	wp_enqueue_style( 'halveron-fonts', 'https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400&display=swap', array(), null );

	$version = wp_get_theme()->get( 'Version' );
	$bundle  = get_theme_file_path( 'assets/css/main.css' );

	if ( file_exists( $bundle ) ) {
		wp_enqueue_style( 'halveron-main', get_theme_file_uri( 'assets/css/main.css' ), array( 'halveron-fonts' ), (string) filemtime( $bundle ) );
	} else {
		$previous = array( 'halveron-fonts' );

		foreach ( halveron_stylesheets() as $sheet ) {
			$handle = 'halveron-' . str_replace( array( '/', '.css' ), array( '-', '' ), $sheet );
			wp_enqueue_style( $handle, get_theme_file_uri( 'assets/css/' . $sheet ), $previous, $version );
			$previous = array( $handle );
		}
	}

	foreach ( array( 'navigation', 'search-panel', 'carousel' ) as $script ) {
		wp_enqueue_script( 'halveron-' . $script, get_theme_file_uri( 'assets/js/' . $script . '.js' ), array(), $version, array( 'in_footer' => false, 'strategy' => 'defer' ) );
	}

	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
}

add_action( 'wp_enqueue_scripts', 'halveron_enqueue_assets', 20 );

remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_filter( 'the_content', 'wpautop' );

add_filter( 'run_wptexturize', '__return_false' );

add_filter( 'document_title_separator', fn (): string => '|' );

add_filter( 'image_editor_output_format', fn ( array $formats ): array => $formats + array( 'image/jpeg' => 'image/webp' ) );

function halveron_title_parts( array $parts ): array {
	if ( is_search() ) {
		$parts['title'] = 'Search';
	}

	return $parts;
}

add_filter( 'document_title_parts', 'halveron_title_parts' );

function halveron_archive_title( string $title, string $post_type ): string {
	$option = HALVERON_ARCHIVE_TITLES[ $post_type ] ?? null;

	return $option ? halveron_option( $option[0], $option[1] ) : $title;
}

add_filter( 'post_type_archive_title', 'halveron_archive_title', 10, 2 );

function halveron_meta_description(): void {
	$description = halveron_description();

	if ( '' !== $description ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
	}
}

add_action( 'wp_head', 'halveron_meta_description', 1 );

function halveron_main_query( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_post_type_archive( array( 'capability', 'sector', 'course' ) ) ) {
		$query->set( 'order', 'ASC' );
		$query->set( 'orderby', 'menu_order' );
		$query->set( 'posts_per_page', -1 );

		return;
	}

	if ( $query->is_home() || $query->is_search() ) {
		$query->set( 'posts_per_page', -1 );
	}

	if ( $query->is_search() && '' === trim( (string) $query->get( 's' ) ) ) {
		$query->set( 'post__in', array( 0 ) );
	}
}

add_action( 'pre_get_posts', 'halveron_main_query' );

function halveron_search_request( array $vars ): array {
	if ( ! isset( $vars['s'] ) ) {
		return $vars;
	}

	$query = trim( (string) $vars['s'] );

	return array_merge( $vars, array( 's' => '' === $query ? ' ' : $query ) );
}

add_filter( 'request', 'halveron_search_request' );
