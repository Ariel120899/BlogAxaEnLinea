<?php
/**
 * Logo del header con respaldo al asset de marca.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Indica si el adjunto del custom logo existe en disco.
 *
 * @param int $attachment_id ID del adjunto.
 */
function astra_child_custom_logo_attachment_exists( $attachment_id ) {
	$attachment_id = (int) $attachment_id;

	if ( $attachment_id <= 0 ) {
		return false;
	}

	$file = get_attached_file( $attachment_id );

	return ! empty( $file ) && file_exists( $file );
}

/**
 * HTML del logo de marca para el header.
 */
function astra_child_get_brand_header_logo_html() {
	$url = astra_child_brand_asset_url( 'logo_header' );

	if ( empty( $url ) ) {
		return '';
	}

	$home_url = home_url( '/' );
	$alt      = get_bloginfo( 'name', 'display' );

	return sprintf(
		'<a href="%1$s" class="custom-logo-link" rel="home" aria-current="page"><img src="%2$s" class="custom-logo" alt="%3$s" decoding="async" /></a>',
		esc_url( $home_url ),
		esc_url( $url ),
		esc_attr( $alt )
	);
}

/**
 * Usa el SVG de marca si el custom logo de WordPress apunta a un archivo faltante.
 *
 * @param string $html    HTML del logo.
 * @param int    $blog_id ID del sitio.
 */
function astra_child_filter_custom_logo( $html, $blog_id ) {
	unset( $blog_id );

	$logo_id = (int) get_theme_mod( 'custom_logo' );

	if ( $logo_id > 0 && astra_child_custom_logo_attachment_exists( $logo_id ) ) {
		return $html;
	}

	$fallback = astra_child_get_brand_header_logo_html();

	return $fallback ? $fallback : $html;
}
add_filter( 'get_custom_logo', 'astra_child_filter_custom_logo', 20, 2 );
