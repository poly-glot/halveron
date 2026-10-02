<?php

const HALVERON_SECTION_MENUS = array( 'primary', 'secondary' );

const HALVERON_FOOTER_MENUS = array( 'footer-1', 'footer-2', 'footer-3' );

function halveron_section_url(): string {
	$is_capability = is_singular( 'capability' ) || is_post_type_archive( 'capability' );
	$is_sector     = is_singular( 'sector' ) || is_post_type_archive( 'sector' );
	$is_thinking   = is_home() || is_singular( 'post' ) || is_category() || is_page( 'thinking' );
	$is_training   = is_page( 'training' ) || is_singular( 'course' ) || is_post_type_archive( 'course' );
	$is_careers    = is_page( array( 'careers', 'opportunities' ) );
	$is_forum      = is_page( 'forum' ) || is_singular( 'event' );

	return match ( true ) {
		$is_capability          => (string) get_post_type_archive_link( 'capability' ),
		$is_sector              => (string) get_post_type_archive_link( 'sector' ),
		$is_thinking            => halveron_page_url( 'thinking' ),
		is_page( 'about' )      => halveron_page_url( 'about' ),
		$is_training            => halveron_page_url( 'training' ),
		$is_careers             => halveron_page_url( 'careers' ),
		$is_forum               => halveron_page_url( 'forum' ),
		is_page( 'conference' ) => halveron_page_url( 'conference' ),
		default                 => '',
	};
}

function halveron_menu_link_attributes( array $atts, object $item, object $args ): array {
	static $section_url = null;

	$location             = (string) ( $args->theme_location ?? '' );
	$atts['aria-current'] = '';

	if ( in_array( $location, HALVERON_FOOTER_MENUS, true ) ) {
		return array( 'class' => 'site-footer__link' ) + $atts;
	}

	if ( ! in_array( $location, HALVERON_SECTION_MENUS, true ) ) {
		return $atts;
	}

	$section_url ??= untrailingslashit( halveron_section_url() );

	$url = untrailingslashit( halveron_url( (string) $item->url ) );

	$atts['aria-current'] = '' !== $section_url && $url === $section_url ? 'page' : '';

	return array( 'class' => 'site-nav__link' ) + $atts;
}

add_filter( 'nav_menu_link_attributes', 'halveron_menu_link_attributes', 10, 3 );

add_filter( 'nav_menu_css_class', fn (): array => array() );

add_filter( 'nav_menu_item_id', fn (): string => '' );

function halveron_menu( string $location, string $list_class ): void {
	wp_nav_menu( array(
		'container'      => false,
		'depth'          => 1,
		'fallback_cb'    => false,
		'item_spacing'   => 'discard',
		'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
		'menu_class'     => $list_class,
		'theme_location' => $location,
	) );
}

function halveron_menu_name( string $location ): string {
	$locations = get_nav_menu_locations();
	$menu      = wp_get_nav_menu_object( $locations[ $location ] ?? 0 );

	return $menu ? $menu->name : '';
}
