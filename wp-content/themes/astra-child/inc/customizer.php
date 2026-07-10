<?php
/**
 * Customizer — configuración de marca del blog.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registra opciones del customizer.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function astra_child_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'astra_child_blog_brand',
		array(
			'title'    => __( 'Marca del blog', 'astra-child' ),
			'priority' => 30,
		)
	);

	$wp_customize->add_setting(
		'astra_child_brand_preset',
		array(
			'default'           => 'axa',
			'sanitize_callback' => 'astra_child_sanitize_brand_preset',
		)
	);

	$choices = array();
	foreach ( astra_child_get_brand_presets() as $slug => $preset ) {
		$choices[ $slug ] = $preset['label'];
	}
	$choices['custom'] = __( 'Personalizado', 'astra-child' );

	$wp_customize->add_control(
		'astra_child_brand_preset',
		array(
			'label'   => __( 'Preset de marca', 'astra-child' ),
			'section' => 'astra_child_blog_brand',
			'type'    => 'select',
			'choices' => $choices,
		)
	);

	$wp_customize->add_setting(
		'astra_child_footer_agent_text',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'astra_child_footer_agent_text',
		array(
			'label'       => __( 'Texto de agente autorizado (footer)', 'astra-child' ),
			'description' => __( 'Ej: Agente Autorizado por AXA México', 'astra-child' ),
			'section'     => 'astra_child_blog_brand',
			'type'        => 'text',
		)
	);

	$color_labels = array(
		'primary'            => __( 'Color primario', 'astra-child' ),
		'accent'             => __( 'Color de acento', 'astra-child' ),
		'accent_dark'        => __( 'Acento oscuro (hover/press)', 'astra-child' ),
		'card_bg'            => __( 'Fondo de tarjetas', 'astra-child' ),
		'banner_bg'          => __( 'Fondo del banner de nota', 'astra-child' ),
		'author_bg'          => __( 'Fondo caja de autor', 'astra-child' ),
		'author_name'        => __( 'Nombre de autor', 'astra-child' ),
		'footer_bg'          => __( 'Fondo del footer', 'astra-child' ),
		'btn_primary_bg'     => __( 'Botón primario', 'astra-child' ),
		'btn_primary_hover'  => __( 'Botón primario (hover)', 'astra-child' ),
		'btn_readmore_bg'    => __( 'Botón Leer más', 'astra-child' ),
		'btn_readmore_hover' => __( 'Botón Leer más (hover)', 'astra-child' ),
		'title'              => __( 'Títulos de notas', 'astra-child' ),
		'category'           => __( 'Etiquetas de categoría', 'astra-child' ),
		'meta'               => __( 'Meta (autor/tiempo)', 'astra-child' ),
		'related_title'      => __( 'Título artículos relacionados', 'astra-child' ),
		'related_accent'     => __( 'Acento artículos relacionados', 'astra-child' ),
		'link_hover'         => __( 'Enlaces (hover)', 'astra-child' ),
		'border'             => __( 'Bordes', 'astra-child' ),
		'redactor_bg'        => __( 'Fondo tarjeta redactor', 'astra-child' ),
	);

	foreach ( astra_child_get_color_keys() as $key ) {
		$wp_customize->add_setting(
			"astra_child_color_{$key}",
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_hex_color',
			)
		);

		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				"astra_child_color_{$key}",
				array(
					'label'   => $color_labels[ $key ] ?? $key,
					'section' => 'astra_child_blog_brand',
				)
			)
		);
	}
}
add_action( 'customize_register', 'astra_child_customize_register' );

/**
 * Sanitiza el preset de marca.
 */
function astra_child_sanitize_brand_preset( $value ) {
	$allowed = array_keys( astra_child_get_brand_presets() );
	$allowed[] = 'custom';

	return in_array( $value, $allowed, true ) ? $value : 'axa';
}
