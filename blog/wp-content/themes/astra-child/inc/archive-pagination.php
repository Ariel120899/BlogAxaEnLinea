<?php
/**
 * Paginación de archivos del blog (autor, búsqueda, categorías).
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Entradas por página en archivos paginados del blog.
 */
function astra_child_get_archive_posts_per_page() {
	return (int) apply_filters( 'astra_child_archive_posts_per_page', 6 );
}

/**
 * Vistas de archivo que usan paginación clásica.
 */
function astra_child_is_paginated_blog_archive() {
	return is_author() || is_search() || is_category() || is_tag();
}

/**
 * Define 6 entradas por página en archivos del blog.
 *
 * @param WP_Query $query Consulta principal.
 */
function astra_child_archive_posts_per_page( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_author() || $query->is_search() || $query->is_category() || $query->is_tag() ) {
		$query->set( 'posts_per_page', astra_child_get_archive_posts_per_page() );
	}
}
add_action( 'pre_get_posts', 'astra_child_archive_posts_per_page', 999 );

/**
 * Astra vuelve a fijar posts_per_page en parse_tax_query; lo anulamos después.
 *
 * @param WP_Query $query Consulta principal.
 */
function astra_child_archive_posts_per_page_after_astra( $query ) {
	astra_child_archive_posts_per_page( $query );
}
add_action( 'parse_tax_query', 'astra_child_archive_posts_per_page_after_astra', 20 );

/**
 * Respeta 6 entradas en archivos del blog cuando Astra aplica su límite global.
 *
 * @param int $limit Límite configurado en Astra.
 * @return int
 */
function astra_child_filter_astra_blog_posts_per_page( $limit ) {
	if ( astra_child_is_paginated_blog_archive() ) {
		return astra_child_get_archive_posts_per_page();
	}

	return $limit;
}
add_filter( 'astra_blog_post_per_page', 'astra_child_filter_astra_blog_posts_per_page' );

/**
 * Renderiza el paginador numérico del archivo actual.
 *
 * @param int|null $max_pages Total de páginas (opcional).
 */
function astra_child_render_archive_pagination( $max_pages = null ) {
	global $wp_query;

	if ( null === $max_pages ) {
		$max_pages = isset( $wp_query->max_num_pages ) ? (int) $wp_query->max_num_pages : 0;
	}

	if ( $max_pages <= 1 ) {
		return;
	}

	$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	$links = paginate_links(
		array(
			'total'     => $max_pages,
			'current'   => $paged,
			'type'      => 'array',
			'prev_text' => '&laquo; ' . esc_html__( 'Anterior', 'astra-child' ),
			'next_text' => esc_html__( 'Siguiente', 'astra-child' ) . ' &raquo;',
			'mid_size'  => 1,
			'end_size'  => 1,
		)
	);

	if ( empty( $links ) || ! is_array( $links ) ) {
		return;
	}

	get_template_part(
		'template-parts/blog/pagination',
		null,
		array( 'links' => $links )
	);
}
