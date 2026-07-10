<?php
/**
 * Carga incremental de artículos en el home del blog.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Argumentos base para la sección "Lo último".
 *
 * @param array<string, mixed> $args Argumentos adicionales.
 * @return array<string, mixed>
 */
function astra_child_get_latest_posts_query_args( $args = array() ) {
	$defaults = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => 1,
		'orderby'             => 'date',
		'order'               => 'DESC',
		'post__not_in'        => astra_child_get_featured_post_ids( 3 ),
	);

	return array_merge( $defaults, $args );
}

/**
 * Total de entradas disponibles en "Lo último".
 */
function astra_child_get_latest_posts_total() {
	$query = new WP_Query(
		astra_child_get_latest_posts_query_args(
			array(
				'posts_per_page'         => 1,
				'fields'                 => 'ids',
				'no_found_rows'          => false,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		)
	);

	return (int) $query->found_posts;
}

/**
 * Renderiza tarjetas horizontales para la carga incremental.
 *
 * @param WP_Post[] $posts Entradas a renderizar.
 */
function astra_child_render_horizontal_post_cards( $posts ) {
	foreach ( $posts as $post ) {
		get_template_part(
			'template-parts/blog/post-card-horizontal',
			null,
			array( 'post_id' => $post->ID )
		);
	}
}

/**
 * Genera la respuesta de carga incremental.
 *
 * @param int $offset Desplazamiento actual.
 * @return array<string, mixed>
 */
function astra_child_get_load_more_response( $offset ) {
	$offset   = absint( $offset );
	$per_page = 3;

	$query = new WP_Query(
		astra_child_get_latest_posts_query_args(
			array(
				'posts_per_page' => $per_page,
				'offset'         => $offset,
			)
		)
	);

	ob_start();

	if ( $query->have_posts() ) {
		astra_child_render_horizontal_post_cards( $query->posts );
	}

	$html = ob_get_clean();

	$loaded_count = (int) $query->post_count;
	$next_offset  = $offset + $loaded_count;
	$total        = astra_child_get_latest_posts_total();

	return array(
		'html'        => $html,
		'has_more'    => $next_offset < $total,
		'next_offset' => $next_offset,
	);
}

/**
 * Valida el nonce de carga incremental.
 *
 * @param string $nonce Nonce recibido.
 * @return bool
 */
function astra_child_validate_load_more_nonce( $nonce ) {
	return (bool) wp_verify_nonce( $nonce, 'astra_child_load_more' );
}

/**
 * Endpoint AJAX para cargar más artículos.
 */
function astra_child_ajax_load_more_posts() {
	check_ajax_referer( 'astra_child_load_more', 'nonce' );

	$offset = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;

	wp_send_json_success( astra_child_get_load_more_response( $offset ) );
}
add_action( 'wp_ajax_astra_child_load_more_posts', 'astra_child_ajax_load_more_posts' );
add_action( 'wp_ajax_nopriv_astra_child_load_more_posts', 'astra_child_ajax_load_more_posts' );

/**
 * Endpoint REST para entornos que bloquean POST a admin-ajax.php.
 *
 * @param WP_REST_Request $request Petición REST.
 * @return WP_REST_Response|WP_Error
 */
function astra_child_rest_load_more_posts( WP_REST_Request $request ) {
	$nonce = (string) $request->get_param( 'nonce' );

	if ( ! astra_child_validate_load_more_nonce( $nonce ) ) {
		return new WP_Error(
			'astra_child_invalid_nonce',
			__( 'Solicitud no válida.', 'astra-child' ),
			array( 'status' => 403 )
		);
	}

	$offset = absint( $request->get_param( 'offset' ) );

	return rest_ensure_response( astra_child_get_load_more_response( $offset ) );
}

/**
 * Registra la ruta REST de carga incremental.
 */
function astra_child_register_load_more_rest_route() {
	register_rest_route(
		'astra-child/v1',
		'/load-more',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'astra_child_rest_load_more_posts',
			'permission_callback' => '__return_true',
			'args'                => array(
				'offset' => array(
					'required'          => true,
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
					'validate_callback' => function ( $value ) {
						return is_numeric( $value ) && $value >= 0;
					},
				),
				'nonce'  => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'astra_child_register_load_more_rest_route' );
