<?php
/**
 * Configuración del widget de cotización del sidebar.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Endpoints compartidos del formulario de auto.
 *
 * @return array<string, string>
 */
function astra_child_get_quote_widget_api_config() {
	return apply_filters(
		'astra_child_quote_widget_api_config',
		array(
			'brands_api'          => 'https://nodejsapisiva-production.up.railway.app/getMarcas',
			'model_api'           => 'https://gen_wsapp.segurointeligente.mx/WsGenericoSI.svc/GetModelo',
			'subbrand_api'        => 'https://gen_wsapp.segurointeligente.mx/WsGenericoSI.svc/GetSubMarcas',
			'token_api'           => 'https://wsservicios.gmag.com.mx/System/WsController/GenerarToken',
			'prospect_api'        => 'https://wsservicios.gmag.com.mx/ZoohoTools/CRM/CrearProspectosSI',
			'phone_validate_api'  => 'https://wsgenerico.segurointeligente.mx/Servicios/ValidatePhone',
			'ws_user'             => 'SIWS',
			'ws_pass'             => 'Gmag2020*',
			'model_range'         => '2005',
			'token_user'          => 'ADMIN',
			'token_pass'          => 'Hola123',
			'phone_validate_token'=> '68AS4D68A1D6SAD08AD9A1D8ASD9AD6A1BOWINBI',
		)
	);
}

/**
 * Configuración del formulario por marca.
 *
 * @return array<string, mixed>
 */
function astra_child_get_quote_widget_config() {
	$brand = astra_child_get_active_preset_slug();

	$configs = array(
		'axa'      => array(
			'title'        => __( 'Cotiza tu Seguro de Auto', 'astra-child' ),
			'privacy_text' => __( 'He leído y estoy de acuerdo con el Aviso de Privacidad y Políticas de Uso. Acepto recibir información de SeguroInteligente.mx y de sus filiales.', 'astra-child' ),
			'submit_label' => __( '¡Cotiza ahora!', 'astra-child' ),
			'banner_link'  => 'https://axasegurosenlinea.com.mx/',
			'lead_source'  => 'Blog AXA',
			'placeholders' => array(
				'marca'    => __( 'Selecciona la marca', 'astra-child' ),
				'modelo'   => __( 'Selecciona la modelo', 'astra-child' ),
				'submarca' => __( 'Selecciona la submarca', 'astra-child' ),
				'edad'     => __( 'Edad', 'astra-child' ),
				'genero'   => __( 'Género', 'astra-child' ),
				'cp'       => __( 'Ingresa tu código postal', 'astra-child' ),
				'nombre'   => __( 'Ingresa tu nombre', 'astra-child' ),
				'apellido' => __( 'Ingresa tu apellido', 'astra-child' ),
				'email'    => __( 'Ingresa tu mail', 'astra-child' ),
				'celular'  => __( 'Ingresa tu número celular', 'astra-child' ),
			),
		),
		'qualitas' => array(
			'title'        => __( 'Cotiza tu Seguro de Auto', 'astra-child' ),
			'privacy_text' => __( 'He leído y estoy de acuerdo con el Aviso de Privacidad y Políticas de Uso. Acepto recibir información de Qualitas Seguros y de sus filiales.', 'astra-child' ),
			'submit_label' => __( '¡Cotiza ahora!', 'astra-child' ),
			'banner_link'  => 'https://qualitasseguroscoche.mx/',
			'lead_source'  => 'Blog Quálitas',
			'placeholders' => array(
				'marca'    => __( 'Selecciona la marca', 'astra-child' ),
				'modelo'   => __( 'Selecciona la modelo', 'astra-child' ),
				'submarca' => __( 'Selecciona la submarca', 'astra-child' ),
				'edad'     => __( 'Edad', 'astra-child' ),
				'genero'   => __( 'Género', 'astra-child' ),
				'cp'       => __( 'Ingresa tu código postal', 'astra-child' ),
				'nombre'   => __( 'Ingresa tu nombre', 'astra-child' ),
				'apellido' => __( 'Ingresa tu apellido', 'astra-child' ),
				'email'    => __( 'Ingresa tu mail', 'astra-child' ),
				'celular'  => __( 'Ingresa tu número celular', 'astra-child' ),
			),
		),
	);

	$config = $configs[ $brand ] ?? $configs['axa'];

	return apply_filters( 'astra_child_quote_widget_config', $config, $brand );
}

/**
 * Encola assets del widget de cotización en notas.
 */
function astra_child_enqueue_quote_widget_assets() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}

	wp_enqueue_script(
		'astra-child-quote-widget',
		get_stylesheet_directory_uri() . '/assets/js/quote-widget.js',
		array(),
		wp_get_theme()->get( 'Version' ),
		true
	);

	$config = astra_child_get_quote_widget_config();
	$api    = astra_child_get_quote_widget_api_config();

	wp_localize_script(
		'astra-child-quote-widget',
		'astraChildQuoteWidget',
		array(
			'placeholders' => $config['placeholders'],
			'leadSource'   => $config['lead_source'],
			'api'          => $api,
			'i18n'         => array(
				'required'       => '*Es necesario llenar este campo',
				'terms'          => __( 'Se tienen que aceptar los términos y condiciones para continuar', 'astra-child' ),
				'invalidData'    => __( '¡Uno o más datos no son válidos!', 'astra-child' ),
				'incompleteData' => __( '¡Aún no ha completado la información correspondiente!', 'astra-child' ),
				'success'        => __( 'Hemos recibido tus datos y nos pondremos en contacto para darte atención personalizada', 'astra-child' ),
				'invalidPhone'   => __( 'Dato no válido', 'astra-child' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'astra_child_enqueue_quote_widget_assets', 20 );
