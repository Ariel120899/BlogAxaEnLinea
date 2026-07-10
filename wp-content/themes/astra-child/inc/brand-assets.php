<?php
/**
 * Assets por marca del blog.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Mapa de archivos por marca según la maqueta.
 *
 * @return array<string, array<string, string>>
 */
function astra_child_get_brand_assets_map() {
	$assets = array(
		'axa'      => array(
			'logo_footer'        => 'logo-seguro-inteligente-blanco.svg',
			'banner_home_desk'   => 'banner-blog-axa-A2.webp',
			'banner_home_mob'    => 'banner-blog-axa-A3.webp',
			'banner_sidebar_desk'=> 'banner-blog-axa-A1.webp',
			'banner_sidebar_mob' => 'banner-blog-axa-A1.webp',
			'arrow_category'     => 'flecha-roja.svg',
			'arrow_footer'       => 'flecha-blanca.svg',
			'icon_clock'         => 'icono-reloj.svg',
			'icon_linkedin'      => 'logo-linkedin.svg',
			'icon_x'             => 'logo-x.svg',
			'icon_home'          => 'icono-home.svg',
			'icon_search'        => 'icono-search.svg',
		),
		'qualitas' => array(
			'logo_footer'        => 'logo-qualitas-blanco.svg',
			'banner_home_desk'   => 'banner-blog-qualitas-A2.webp',
			'banner_home_mob'    => 'banner-blog-qualitas-A3.webp',
			'banner_sidebar_desk'=> 'banner-seguro-auto-sidebar.png',
			'banner_sidebar_mob' => 'banner-seguro-auto-sidebar.png',
			'arrow_category'     => 'flecha-morada.svg',
			'arrow_footer'       => 'flecha-morada.svg',
			'icon_clock'         => 'flecha-morada.svg',
			'icon_linkedin'      => '',
			'icon_x'             => '',
			'icon_home'          => '',
			'icon_search'        => '',
		),
	);

	return apply_filters( 'astra_child_brand_assets_map', $assets );
}

/**
 * URL de un asset de la marca activa.
 */
function astra_child_brand_asset_url( $key ) {
	$brand = astra_child_get_active_preset_slug();
	$map   = astra_child_get_brand_assets_map();
	$file  = $map[ $brand ][ $key ] ?? '';

	if ( empty( $file ) ) {
		return '';
	}

	$path = 'assets/img/brands/' . $brand . '/' . $file;

	if ( ! file_exists( get_stylesheet_directory() . '/' . $path ) ) {
		return '';
	}

	return astra_child_asset_url( $path );
}

/**
 * Imagen HTML de un asset de marca.
 */
function astra_child_brand_asset_img( $key, $attrs = array() ) {
	$url = astra_child_brand_asset_url( $key );

	if ( empty( $url ) ) {
		return '';
	}

	$defaults = array(
		'src'   => $url,
		'alt'   => '',
		'class' => '',
	);

	$attrs = wp_parse_args( $attrs, $defaults );
	$html  = '<img';

	foreach ( $attrs as $name => $value ) {
		if ( '' === $value ) {
			continue;
		}
		$html .= sprintf( ' %s="%s"', esc_attr( $name ), esc_attr( $value ) );
	}

	$html .= ' />';

	return $html;
}

/**
 * Enlaces del footer según la marca activa.
 *
 * @return array<int, array{label: string, url: string}>
 */
function astra_child_get_brand_footer_links() {
	$brand = astra_child_get_active_preset_slug();

	$links = array(
		'axa'      => array(
			array(
				'label' => __( 'Seguro de Auto AXA', 'astra-child' ),
				'url'   => home_url( '/' ),
			),
			array(
				'label' => __( 'Seguro de gastos médicos mayores AXA', 'astra-child' ),
				'url'   => home_url( '/' ),
			),
			array(
				'label' => __( 'AXA', 'astra-child' ),
				'url'   => home_url( '/' ),
			),
		),
		'qualitas' => array(
			array(
				'label' => __( 'Quálitas', 'astra-child' ),
				'url'   => home_url( '/' ),
			),
			array(
				'label' => __( 'Seguro de autos Quálitas', 'astra-child' ),
				'url'   => home_url( '/' ),
			),
		),
	);

	return apply_filters( 'astra_child_footer_links', $links[ $brand ] ?? $links['axa'] );
}
