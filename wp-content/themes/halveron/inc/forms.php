<?php

add_action( 'admin_post_nopriv_halveron_enquiry', 'halveron_handle_enquiry' );
add_action( 'admin_post_halveron_enquiry', 'halveron_handle_enquiry' );
add_action( 'admin_post_nopriv_halveron_newsletter', 'halveron_handle_newsletter' );
add_action( 'admin_post_halveron_newsletter', 'halveron_handle_newsletter' );

function halveron_posted( string $name ): string {
	return sanitize_text_field( wp_unslash( (string) ( $_POST[ $name ] ?? '' ) ) );
}

function halveron_redirect( string $url ): never {
	wp_safe_redirect( $url );
	exit;
}

function halveron_form_source(): ?WP_Post {
	$source_id = absint( $_POST['source'] ?? 0 );
	$source    = $source_id ? get_post( $source_id ) : null;

	$is_valid = $source && 'publish' === $source->post_status && in_array( $source->post_type, HALVERON_ENQUIRY_SOURCES, true );

	return $is_valid ? $source : null;
}

function halveron_form_is_genuine( string $action ): bool {
	$nonce = sanitize_text_field( wp_unslash( (string) ( $_POST['halveron_nonce'] ?? '' ) ) );

	return (bool) wp_verify_nonce( $nonce, $action );
}

function halveron_is_bot(): bool {
	return '' !== halveron_posted( 'website' );
}

function halveron_interest_ids(): array {
	$posted = array_filter( (array) ( $_POST['interests'] ?? array() ), 'is_string' );
	$slugs  = array_map( fn ( string $slug ): string => sanitize_title( wp_unslash( $slug ) ), $posted );

	if ( ! $slugs ) {
		return array();
	}

	$by_slug = wp_list_pluck( halveron_capabilities(), 'ID', 'post_name' );

	return array_values( array_intersect_key( $by_slug, array_flip( $slugs ) ) );
}

function halveron_enquiry_fields( string $form, int $source_id ): array {
	return array(
		'company'    => halveron_posted( 'company' ),
		'email'      => sanitize_email( wp_unslash( (string) ( $_POST['email'] ?? '' ) ) ),
		'form'       => $form,
		'interests'  => 'download' === $form ? halveron_interest_ids() : array(),
		'message'    => sanitize_textarea_field( wp_unslash( (string) ( $_POST['message'] ?? '' ) ) ),
		'name'       => halveron_posted( 'name' ),
		'newsletter' => 'contact' === $form && ! empty( $_POST['newsletter'] ),
		'phone'      => halveron_posted( 'phone' ),
		'source'     => $source_id,
	);
}

function halveron_enquiry_is_complete( array $fields ): bool {
	$needs_message = 'download' !== $fields['form'];
	$needs_company = 'download' === $fields['form'];

	return '' !== $fields['name']
		&& is_email( $fields['email'] )
		&& ( ! $needs_message || '' !== $fields['message'] )
		&& ( ! $needs_company || '' !== $fields['company'] );
}

function halveron_handle_enquiry(): void {
	$source = halveron_form_source();
	$back   = $source ? (string) get_permalink( $source ) : home_url( '/' );
	$form   = sanitize_key( (string) ( $_POST['form'] ?? '' ) );

	if ( ! $source || ! isset( HALVERON_ENQUIRY_FORMS[ $form ] ) || ! halveron_form_is_genuine( 'halveron_enquiry' ) ) {
		halveron_redirect( $back );
	}

	$sent = add_query_arg( 'sent', $form, $back ) . '#' . HALVERON_ENQUIRY_FORMS[ $form ]['anchor'];

	if ( halveron_is_bot() ) {
		halveron_redirect( $sent );
	}

	$fields = halveron_enquiry_fields( $form, $source->ID );

	if ( ! halveron_enquiry_is_complete( $fields ) ) {
		halveron_redirect( $back . '#' . HALVERON_ENQUIRY_FORMS[ $form ]['anchor'] );
	}

	wp_insert_post( array(
		'meta_input'  => $fields,
		'post_status' => 'private',
		'post_title'  => $fields['name'] . ' — ' . HALVERON_ENQUIRY_FORMS[ $form ]['label'],
		'post_type'   => 'enquiry',
	) );

	halveron_redirect( $sent );
}

function halveron_handle_newsletter(): void {
	$back = remove_query_arg( array( 'sent', 'subscribed' ), wp_get_referer() ?: home_url( '/' ) );

	if ( ! halveron_form_is_genuine( 'halveron_newsletter' ) ) {
		halveron_redirect( $back );
	}

	$email = sanitize_email( wp_unslash( (string) ( $_POST['email'] ?? '' ) ) );

	if ( halveron_is_bot() || is_email( $email ) ) {
		halveron_redirect( add_query_arg( 'subscribed', '1', $back ) . '#newsletter-title' );
	}

	halveron_redirect( $back . '#newsletter-title' );
}

function halveron_sent( string $form ): bool {
	return isset( $_GET['sent'] ) && $form === sanitize_key( wp_unslash( (string) $_GET['sent'] ) );
}

function halveron_subscribed(): bool {
	return isset( $_GET['subscribed'] );
}
