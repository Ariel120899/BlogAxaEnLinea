<?php
/**
 * Configuración y assets del formulario GMM del sidebar.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Configuración del formulario GMM.
 *
 * @return array<string, mixed>
 */
function astra_child_get_gmm_widget_config() {
	return apply_filters(
		'astra_child_gmm_widget_config',
		array(
			'lead_source' => 'Blog AXA',
			'ramo'        => 'ACCIDENTES Y ENFERMEDADES',
			'first_page'  => function_exists( 'home_url' ) ? home_url( '/' ) : '',
		)
	);
}

/**
 * Encola el script del formulario GMM en notas de gastos médicos.
 */
function astra_child_enqueue_gmm_widget_assets() {
	if ( ! is_singular( 'post' ) || ! astra_child_post_uses_gmm_sidebar_form() ) {
		return;
	}

	$script_path = get_stylesheet_directory() . '/assets/js/gmm-widget.js';
	if ( ! is_readable( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		'astra-child-gmm-widget',
		get_stylesheet_directory_uri() . '/assets/js/gmm-widget.js',
		array(),
		(string) filemtime( $script_path ),
		true
	);

	$api    = astra_child_get_quote_widget_api_config();
	$config = astra_child_get_gmm_widget_config();

	wp_localize_script(
		'astra-child-gmm-widget',
		'astraChildGmmWidget',
		array(
			'leadSource' => $config['lead_source'],
			'ramo'       => $config['ramo'],
			'firstPage'  => $config['first_page'],
			'api'        => array(
				'token_api'           => $api['token_api'],
				'prospect_api'        => $api['prospect_api'],
				'phone_validate_api'  => $api['phone_validate_api'],
				'phone_validate_token'=> $api['phone_validate_token'],
				'token_user'          => $api['token_user'],
				'token_pass'          => $api['token_pass'],
			),
			'i18n'       => array(
				'required'       => '*Es necesario llenar este campo',
				'invalidData'    => __( 'La información no es correcta', 'astra-child' ),
				'incompleteData' => __( 'Falta ingresar información', 'astra-child' ),
				'invalidPhone'   => __( 'Número no válido', 'astra-child' ),
				'success'        => __( 'Hemos recibido tus datos y nos pondremos en contacto para darte atención personalizada', 'astra-child' ),
				'selectOption'   => __( 'Es necesario elegir una opción', 'astra-child' ),
				'invalidEmail'   => __( 'El formato correo no es correcto', 'astra-child' ),
				'invalidCp'      => __( 'Debe contener 5 caracteres', 'astra-child' ),
				'invalidPhoneLen'=> __( 'Debe contener 10 caracteres', 'astra-child' ),
				'invalidName'    => __( 'Mínimo 3 letras', 'astra-child' ),
				'repeatedDigits' => __( 'No debe contener números repetidos', 'astra-child' ),
				'repeatedLetters'=> __( 'No debe contener letras repetidas', 'astra-child' ),
				'submitError'    => __( 'No pudimos enviar tu solicitud. Intenta de nuevo en unos momentos.', 'astra-child' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'astra_child_enqueue_gmm_widget_assets', 20 );
