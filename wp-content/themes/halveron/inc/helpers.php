<?php

function halveron_meta( string $name, ?int $post_id = null ): mixed {
	return get_post_meta( $post_id ?? get_the_ID(), $name, true );
}

function halveron_text( string $name, ?int $post_id = null ): string {
	$value = halveron_meta( $name, $post_id );

	return is_scalar( $value ) ? (string) $value : '';
}

function halveron_rows( string $name, ?int $post_id = null ): array {
	$value = halveron_meta( $name, $post_id );

	return is_array( $value ) ? array_values( $value ) : array();
}

function halveron_option( string $name, string $fallback = '' ): string {
	$value = '' === $name ? '' : get_option( $name, '' );

	return is_scalar( $value ) && '' !== (string) $value ? (string) $value : $fallback;
}

function halveron_option_rows( string $name ): array {
	$value = get_option( $name, array() );

	return is_array( $value ) ? array_values( $value ) : array();
}

function halveron_ids( mixed $value ): array {
	return array_values( array_filter( array_map( 'intval', (array) $value ) ) );
}

function halveron_url( string $value ): string {
	return str_starts_with( $value, '/' ) ? home_url( $value ) : $value;
}

function halveron_page( string $path ): ?WP_Post {
	static $pages = array();

	if ( ! array_key_exists( $path, $pages ) ) {
		$pages[ $path ] = get_page_by_path( $path );
	}

	return $pages[ $path ];
}

function halveron_page_url( string $path ): string {
	$page = halveron_page( $path );

	return $page ? (string) get_permalink( $page ) : home_url( '/' );
}

function halveron_page_id( string $path ): int {
	return halveron_page( $path )?->ID ?? 0;
}

function halveron_query( array $args ): array {
	$query = new WP_Query( $args + array(
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'post_status'         => 'publish',
	) );

	update_post_thumbnail_cache( $query );

	return $query->posts;
}

function halveron_posts_by_id( mixed $ids, string|array $post_type ): array {
	$ids = halveron_ids( $ids );

	if ( ! $ids ) {
		return array();
	}

	$posts = halveron_query( array(
		'orderby'        => 'post__in',
		'post__in'       => $ids,
		'post_type'      => $post_type,
		'posts_per_page' => count( $ids ),
	) );

	return array_combine( wp_list_pluck( $posts, 'ID' ), $posts );
}

function halveron_ordered( mixed $ids, string|array $post_type ): array {
	return array_values( halveron_posts_by_id( $ids, $post_type ) );
}

function halveron_post( mixed $id, string $post_type ): ?WP_Post {
	return halveron_ordered( $id, $post_type )[0] ?? null;
}

function halveron_by_menu_order( string $post_type, array $args = array() ): array {
	return halveron_query( $args + array(
		'order'          => 'ASC',
		'orderby'        => 'menu_order',
		'post_type'      => $post_type,
		'posts_per_page' => -1,
	) );
}

function halveron_capabilities(): array {
	static $capabilities = null;

	$capabilities ??= halveron_by_menu_order( 'capability' );

	return $capabilities;
}

function halveron_prime_attachments( array $ids ): void {
	$ids = halveron_ids( $ids );

	if ( $ids ) {
		_prime_post_caches( $ids, false, true );
	}
}

function halveron_image( int $attachment_id, string $class, array $attributes = array(), string $size = 'full' ): string {
	if ( ! $attachment_id ) {
		return '';
	}

	return wp_get_attachment_image( $attachment_id, $size, false, array( 'class' => $class ) + $attributes );
}

function halveron_thumbnail( WP_Post $post, string $class, array $attributes = array(), string $size = 'full' ): string {
	return halveron_image( (int) get_post_thumbnail_id( $post ), $class, $attributes, $size );
}

function halveron_portrait( WP_Post $person, string $class ): string {
	return halveron_thumbnail( $person, $class, array(
		'alt'     => 'Portrait of ' . get_the_title( $person ),
		'loading' => 'lazy',
	) );
}

function halveron_insight_type( WP_Post $post ): string {
	foreach ( get_the_category( $post->ID ) as $term ) {
		if ( isset( HALVERON_INSIGHT_TYPES[ $term->slug ] ) ) {
			return $term->slug;
		}
	}

	return 'news';
}

function halveron_insight( WP_Post $post ): array {
	return HALVERON_INSIGHT_TYPES[ halveron_insight_type( $post ) ];
}

function halveron_insight_link( WP_Post $post ): string {
	$permalink = (string) get_permalink( $post );

	return 'white-paper' === halveron_insight_type( $post ) ? $permalink . '#download' : $permalink;
}

function halveron_date( string $ymd, string $format ): string {
	$date = DateTimeImmutable::createFromFormat( '!Y-m-d', $ymd, wp_timezone() );

	return $date ? (string) wp_date( $format, $date->getTimestamp() ) : '';
}

function halveron_lines( string $text ): array {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\R/', $text ) ) ) );
}

function halveron_paragraphs( string $text ): string {
	$paragraphs = array_filter( array_map( 'trim', preg_split( '/\R\s*\R/', trim( $text ) ) ) );

	return implode( "\n", array_map( fn ( string $paragraph ): string => '<p>' . esc_html( $paragraph ) . '</p>', $paragraphs ) );
}

function halveron_rich( string $html ): string {
	$has_blocks = (bool) preg_match( '/<(p|ul|ol|h[2-6]|blockquote)[\s>]/', $html );

	return wp_kses_post( $has_blocks ? $html : wpautop( $html ) );
}

function halveron_inline( string $html ): string {
	return wp_kses_post( trim( (string) preg_replace( '#</?p(\s[^>]*)?>#', '', $html ) ) );
}

function halveron_quote( string $text ): string {
	return '“' . $text . '”';
}

function halveron_phone( string $display, string $href ): string {
	return sprintf( '<a href="%s">%s</a>', esc_url( 'tel:' . $href ), str_replace( ' ', '&nbsp;', esc_html( $display ) ) );
}

function halveron_mailto( string $email, string $subject = '' ): string {
	return 'mailto:' . $email . ( '' === $subject ? '' : '?subject=' . rawurlencode( $subject ) );
}

function halveron_email( string $email ): string {
	return sprintf( '<a href="%s">%s</a>', esc_url( halveron_mailto( $email ) ), esc_html( $email ) );
}

function halveron_first_name( WP_Post $person ): string {
	return halveron_text( 'first_name', $person->ID ) ?: (string) strtok( get_the_title( $person ), ' ' );
}

function halveron_organisations( string $kind ): array {
	$organisations = halveron_by_menu_order( 'organisation', array(
		'meta_key'   => 'kind',
		'meta_value' => $kind,
	) );

	halveron_prime_attachments( array_map( fn ( WP_Post $organisation ): int => (int) halveron_meta( 'logo', $organisation->ID ), $organisations ) );

	return $organisations;
}

function halveron_logo( WP_Post $organisation, string $class ): string {
	$logo_id = (int) halveron_meta( 'logo', $organisation->ID );
	$prefix  = HALVERON_LOGO_PREFIXES[ halveron_text( 'kind', $organisation->ID ) ] ?? '';
	$src     = $logo_id ? wp_get_attachment_url( $logo_id ) : get_theme_file_uri( 'assets/logos/' . $prefix . $organisation->post_name . '.svg' );

	return sprintf(
		'<img class="%s" src="%s" alt="%s" width="240" height="80" loading="lazy">',
		esc_attr( $class ),
		esc_url( (string) $src ),
		esc_attr( get_the_title( $organisation ) )
	);
}

function halveron_crumb( WP_Post $page ): array {
	$section = halveron_text( 'section_title', $page->ID );

	return array(
		'label' => '' === $section ? get_the_title( $page ) : (string) preg_replace( '/^Halveron\s+/', '', $section ),
		'url'   => (string) get_permalink( $page ),
	);
}

function halveron_page_crumb( string $path ): array {
	$page = halveron_page( $path );

	return $page ? halveron_crumb( $page ) : array();
}

function halveron_archive_crumb( string $post_type ): array {
	return array(
		'label' => halveron_archive_title( '', $post_type ),
		'url'   => (string) get_post_type_archive_link( $post_type ),
	);
}

function halveron_current_crumb( string $label ): array {
	return array(
		'label' => $label,
		'url'   => '',
	);
}

function halveron_breadcrumb_items(): array {
	$home = array(
		'label' => 'Home',
		'url'   => home_url( '/' ),
	);

	if ( is_home() ) {
		return array( $home, halveron_page_crumb( 'thinking' ), halveron_current_crumb( get_the_title( (int) get_option( 'page_for_posts' ) ) ) );
	}

	if ( is_singular( 'post' ) || is_category() ) {
		return array( $home, halveron_page_crumb( 'thinking' ), halveron_current_crumb( is_category() ? single_cat_title( '', false ) : get_the_title() ) );
	}

	if ( is_post_type_archive( 'course' ) ) {
		return array( $home, halveron_page_crumb( 'training' ), halveron_current_crumb( halveron_archive_title( '', 'course' ) ) );
	}

	if ( is_post_type_archive() ) {
		return array( $home, halveron_current_crumb( post_type_archive_title( '', false ) ) );
	}

	if ( is_singular( array( 'capability', 'sector' ) ) ) {
		return array( $home, halveron_archive_crumb( (string) get_post_type() ), halveron_current_crumb( get_the_title() ) );
	}

	if ( is_singular( 'course' ) ) {
		$group = HALVERON_COURSE_GROUPS[ halveron_text( 'course_type' ) ] ?? HALVERON_COURSE_GROUPS['open'];
		$crumb = array(
			'label' => $group['title'],
			'url'   => get_post_type_archive_link( 'course' ) . '#' . $group['anchor'],
		);

		return array( $home, halveron_page_crumb( 'training' ), $crumb, halveron_current_crumb( get_the_title() ) );
	}

	if ( is_singular( 'event' ) ) {
		return array( $home, halveron_page_crumb( 'forum' ), halveron_current_crumb( get_the_title() ) );
	}

	if ( is_page() ) {
		$page      = get_queried_object();
		$ancestors = array_map( fn ( int $id ): array => halveron_crumb( get_post( $id ) ), array_reverse( get_post_ancestors( $page ) ) );
		$current   = halveron_crumb( $page );

		return array_merge( array( $home ), $ancestors, array( halveron_current_crumb( $current['label'] ) ) );
	}

	if ( is_search() ) {
		return array( $home, halveron_current_crumb( 'Search' ) );
	}

	return array( $home, halveron_current_crumb( wp_strip_all_tags( get_the_archive_title() ) ) );
}

function halveron_description(): string {
	$fallback = (string) get_bloginfo( 'description' );

	if ( is_search() ) {
		return halveron_option( 'search_description', $fallback );
	}

	if ( is_404() ) {
		return 'The page may have moved, or the address may be mistyped.';
	}

	if ( is_post_type_archive() ) {
		$post_type = get_query_var( 'post_type' );
		$post_type = is_array( $post_type ) ? (string) reset( $post_type ) : (string) $post_type;

		return halveron_option( HALVERON_ARCHIVE_DESCRIPTIONS[ $post_type ] ?? '', $fallback );
	}

	$post_id = is_home() ? (int) get_option( 'page_for_posts' ) : ( is_singular() ? get_queried_object_id() : 0 );
	$post    = $post_id ? get_post( $post_id ) : null;

	if ( ! $post ) {
		return $fallback;
	}

	$stored = halveron_text( 'meta_description', $post->ID );

	$has_summary = in_array( $post->post_type, array( 'capability', 'sector', 'course', 'event' ), true );

	return match ( true ) {
		'' !== $stored                                           => $stored,
		'post' === $post->post_type && '' !== $post->post_excerpt => $post->post_excerpt,
		$has_summary                                             => halveron_text( 'summary', $post->ID ) ?: $fallback,
		default                                                  => $fallback,
	};
}

function halveron_shows_newsletter(): bool {
	$is_legal = is_page() && '' !== halveron_text( 'last_updated', get_queried_object_id() );

	return ! ( $is_legal || is_page( 'sitemap' ) || is_search() || is_404() );
}

function halveron_tspans( array $lines, float $x, float $centre, float $step ): string {
	$first = $centre - $step * ( count( $lines ) - 1 ) / 2;
	$spans = array();

	foreach ( array_values( $lines ) as $index => $line ) {
		$spans[] = sprintf( '<tspan x="%s" y="%s">%s</tspan>', esc_attr( (string) $x ), esc_attr( (string) round( $first + $step * $index, 1 ) ), esc_html( $line ) );
	}

	return implode( '', $spans );
}

function halveron_tabs( array $tabs, string $current_url ): array {
	$current = untrailingslashit( $current_url );

	return array_map( function ( array $tab ) use ( $current ): array {
		$url        = halveron_url( (string) ( $tab['url'] ?? '' ) );
		$is_current = ! empty( $tab['current'] ) || ( ! str_contains( $url, '#' ) && untrailingslashit( $url ) === $current );

		return array(
			'current' => $is_current,
			'label'   => (string) ( $tab['label'] ?? '' ),
			'url'     => $url,
		);
	}, $tabs );
}

function halveron_result( WP_Post $post ): array {
	$is_front = (int) get_option( 'page_on_front' ) === $post->ID;

	return match ( $post->post_type ) {
		'post'  => array(
			'excerpt' => $post->post_excerpt,
			'title'   => get_the_title( $post ),
			'type'    => halveron_insight( $post )['tag'],
		),
		'page'  => array(
			'excerpt' => halveron_text( 'meta_description', $post->ID ),
			'title'   => $is_front ? get_bloginfo( 'name' ) : ( halveron_text( 'section_title', $post->ID ) ?: get_the_title( $post ) ),
			'type'    => HALVERON_RESULT_TYPES['page'],
		),
		default => array(
			'excerpt' => halveron_text( 'summary', $post->ID ),
			'title'   => get_the_title( $post ),
			'type'    => HALVERON_RESULT_TYPES[ $post->post_type ] ?? HALVERON_RESULT_TYPES['page'],
		),
	};
}
