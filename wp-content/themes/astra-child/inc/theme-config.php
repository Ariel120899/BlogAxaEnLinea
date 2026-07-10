<?php
/**
 * Configuración de marca y colores del blog.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Presets de color por marca.
 *
 * @return array<string, array<string, string>>
 */
function astra_child_get_brand_presets() {
	$presets = array(
		'axa'       => array(
			'label'                  => 'AXA',
			'primary'                => '#00008F',
			'accent'                 => '#FF1721',
			'accent_dark'            => '#960000',
			'card_bg'                => '#E2EFFF',
			'banner_bg'              => '#E6E5F5',
			'author_bg'              => '#FFEAEA',
			'author_name'            => '#FF1721',
			'footer_bg'              => '#00008F',
			'btn_primary_bg'         => '#FF1721',
			'btn_primary_hover'      => '#960000',
			'btn_readmore_bg'        => '#FF1721',
			'btn_readmore_hover'     => '#960000',
			'title'                  => '#FF1721',
			'category'               => '#00008F',
			'meta'                   => '#000000',
			'related_title'          => '#FF1721',
			'related_accent'         => '#FF1721',
			'link_hover'             => '#00008F',
			'border'                 => '#A6A6A6',
			'redactor_bg'            => '#FFEAEA',
			'footer_agent'           => 'Agente Autorizado por AXA México',
		),
		'qualitas'  => array(
			'label'                  => 'Quálitas',
			'primary'                => '#941B80',
			'accent'                 => '#0096AE',
			'accent_dark'            => '#490035',
			'card_bg'                => '#EBF5FF',
			'banner_bg'              => '#6E27C5',
			'author_bg'              => '#E5E5E5',
			'author_name'            => '#01C1D6',
			'footer_bg'              => '#941B80',
			'btn_primary_bg'         => '#0094AE',
			'btn_primary_hover'      => '#00647D',
			'btn_readmore_bg'        => '#941B80',
			'btn_readmore_hover'     => '#490035',
			'title'                  => '#0096AE',
			'category'               => '#941B80',
			'meta'                   => '#000000',
			'related_title'          => '#6E27C5',
			'related_accent'         => '#01C1D6',
			'link_hover'             => '#0096AE',
			'border'                 => '#A6A6A6',
			'redactor_bg'            => '#DCFAFA',
			'footer_agent'           => 'Agente Autorizado por Quálitas',
		),
	);

	return apply_filters( 'astra_child_brand_presets', $presets );
}

/**
 * Claves de color configurables.
 *
 * @return string[]
 */
function astra_child_get_color_keys() {
	return array(
		'primary',
		'accent',
		'accent_dark',
		'card_bg',
		'banner_bg',
		'author_bg',
		'author_name',
		'footer_bg',
		'btn_primary_bg',
		'btn_primary_hover',
		'btn_readmore_bg',
		'btn_readmore_hover',
		'title',
		'category',
		'meta',
		'related_title',
		'related_accent',
		'link_hover',
		'border',
		'redactor_bg',
	);
}

/**
 * Preset activo.
 */
function astra_child_get_active_preset_slug() {
	$preset  = get_theme_mod( 'astra_child_brand_preset', 'axa' );
	$presets = astra_child_get_brand_presets();

	if ( 'custom' === $preset ) {
		return 'axa';
	}

	if ( ! isset( $presets[ $preset ] ) ) {
		return 'axa';
	}

	return $preset;
}

/**
 * Slug de marca seleccionado en el customizer (incluye "custom").
 */
function astra_child_get_selected_brand_slug() {
	$preset = get_theme_mod( 'astra_child_brand_preset', 'axa' );

	return in_array( $preset, array( 'axa', 'qualitas', 'custom' ), true ) ? $preset : 'axa';
}

/**
 * Colores activos del blog (preset + overrides del customizer).
 *
 * @return array<string, string>
 */
function astra_child_get_theme_colors() {
	$presets  = astra_child_get_brand_presets();
	$preset   = astra_child_get_active_preset_slug();
	$defaults = $presets[ $preset ];
	$colors   = array();

	foreach ( astra_child_get_color_keys() as $key ) {
		$mod_value = get_theme_mod( "astra_child_color_{$key}", '' );

		if ( ! empty( $mod_value ) ) {
			$colors[ $key ] = sanitize_hex_color( $mod_value );
			continue;
		}

		$colors[ $key ] = $defaults[ $key ] ?? '#000000';
	}

	return apply_filters( 'astra_child_theme_colors', $colors, $preset );
}

/**
 * Texto legal del footer para la marca activa.
 */
function astra_child_get_footer_agent_text() {
	$custom = get_theme_mod( 'astra_child_footer_agent_text', '' );

	if ( ! empty( $custom ) ) {
		return $custom;
	}

	$presets = astra_child_get_brand_presets();
	$preset  = astra_child_get_active_preset_slug();

	return $presets[ $preset ]['footer_agent'] ?? '';
}

/**
 * CSS variables para el blog.
 */
function astra_child_get_css_variables() {
	$colors = astra_child_get_theme_colors();
	$vars   = array(
		'max-width' => '1266px',
	);

	foreach ( $colors as $key => $value ) {
		$vars[ $key ] = $value;
	}

	$arrow = astra_child_brand_asset_url( 'arrow_category' );
	if ( $arrow ) {
		$vars['arrow-category'] = sprintf( 'url(%s)', $arrow );
	}

	return $vars;
}

/**
 * Imprime variables CSS en el front.
 */
function astra_child_print_css_variables() {
	$vars = astra_child_get_css_variables();
	$css  = ':root {';

	foreach ( $vars as $key => $value ) {
		$css .= '--blog-' . str_replace( '_', '-', $key ) . ':' . $value . ';';
	}

	$css .= '}';

	wp_add_inline_style( 'astra-child-style', $css );
}
add_action( 'wp_enqueue_scripts', 'astra_child_print_css_variables', 20 );
