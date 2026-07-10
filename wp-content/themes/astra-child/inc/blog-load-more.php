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
 * Endpoint AJAX para cargar más artículos.
 */
function astra_child_ajax_load_more_posts() {
	check_ajax_referer( 'astra_child_load_more', 'nonce' );

	$offset   = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;
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
	$has_more     = $next_offset < $total;

	wp_send_json_success(
		array(
			'html'        => $html,
			'has_more'    => $has_more,
			'next_offset' => $next_offset,
		)
	);
}
add_action( 'wp_ajax_astra_child_load_more_posts', 'astra_child_ajax_load_more_posts' );
add_action( 'wp_ajax_nopriv_astra_child_load_more_posts', 'astra_child_ajax_load_more_posts' );
