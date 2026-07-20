<?php
/**
 * Funciones auxiliares del tema hijo.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * URL base de assets del tema hijo.
 */
function astra_child_asset_url( $path = '' ) {
	return trailingslashit( get_stylesheet_directory_uri() ) . ltrim( $path, '/' );
}

/**
 * Calcula el tiempo estimado de lectura en minutos.
 */
function astra_child_reading_time( $post_id = null, $words_per_minute = 200 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$content = get_post_field( 'post_content', $post_id );

	if ( empty( $content ) ) {
		return 1;
	}

	$word_count = str_word_count( wp_strip_all_tags( $content ) );

	return max( 1, (int) ceil( $word_count / $words_per_minute ) );
}

/**
 * Slug de la categoría editorial de destacados.
 */
function astra_child_get_featured_category_slug() {
	return apply_filters( 'astra_child_featured_category_slug', 'destacado' );
}

/**
 * Categorías que no deben mostrarse como etiqueta principal en las tarjetas.
 *
 * @return string[]
 */
function astra_child_get_excluded_category_slugs() {
	$slugs = array(
		astra_child_get_featured_category_slug(),
		'sin-categoria',
		'uncategorized',
	);

	return apply_filters( 'astra_child_excluded_category_slugs', $slugs );
}

/**
 * IDs de categorías excluidas del listado del sidebar.
 *
 * @return int[]
 */
function astra_child_get_excluded_category_ids() {
	$ids = array_map(
		static function ( $slug ) {
			$category = get_category_by_slug( $slug );

			return $category ? (int) $category->term_id : 0;
		},
		astra_child_get_excluded_category_slugs()
	);

	return array_values( array_filter( $ids ) );
}

/**
 * Devuelve la primera categoría visible de un post.
 */
function astra_child_get_primary_category( $post_id = null ) {
	$post_id    = $post_id ? $post_id : get_the_ID();
	$categories = get_the_category( $post_id );
	$excluded   = astra_child_get_excluded_category_slugs();

	if ( empty( $categories ) ) {
		return null;
	}

	foreach ( $categories as $category ) {
		if ( ! in_array( $category->slug, $excluded, true ) ) {
			return $category;
		}
	}

	return null;
}

/**
 * Imagen destacada o placeholder del tema.
 */
function astra_child_get_post_image_url( $post_id = null, $size = 'medium_large' ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail_url( $post_id, $size );
	}

	return astra_child_asset_url( 'assets/img/placeholder-nota.svg' );
}

/**
 * Posts de la categoría Destacado para la sección del home.
 *
 * @return WP_Post[]
 */
function astra_child_get_featured_posts( $limit = 3 ) {
	$category = get_category_by_slug( astra_child_get_featured_category_slug() );

	if ( ! $category ) {
		return array();
	}

	$query = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $limit,
			'cat'                 => $category->term_id,
			'ignore_sticky_posts' => 1,
			'orderby'             => 'date',
			'order'               => 'DESC',
		)
	);

	return $query->posts;
}

/**
 * IDs de posts destacados para excluirlos de "Lo último".
 *
 * @return int[]
 */
function astra_child_get_featured_post_ids( $limit = 3 ) {
	$posts = astra_child_get_featured_posts( $limit );

	return wp_list_pluck( $posts, 'ID' );
}

/**
 * Autores con publicaciones publicadas.
 *
 * @return WP_User[]
 */
function astra_child_get_authors() {
	$authors = get_users(
		array(
			'capability' => array( 'edit_posts' ),
			'orderby'    => 'display_name',
			'order'      => 'ASC',
		)
	);

	return array_values(
		array_filter(
			$authors,
			function ( $author ) {
				return count_user_posts( $author->ID, 'post', true ) > 0;
			}
		)
	);
}

/**
 * Icono de reloj según la marca activa.
 */
function astra_child_clock_icon() {
	$icon = astra_child_brand_asset_img(
		'icon_clock',
		array(
			'alt'    => '',
			'width'  => '12',
			'height' => '12',
			'class'  => 'blog-icon-clock',
			'aria-hidden' => 'true',
		)
	);

	if ( $icon ) {
		return $icon;
	}

	return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 250 250" fill="currentColor" width="12" aria-hidden="true"><path d="M125,0C56.07,0,0,56.07,0,125s56.07,125,125,125,125-56.07,125-125S193.93,0,125,0ZM125,234.38c-60.31,0-109.38-49.07-109.38-109.38S64.69,15.62,125,15.62s109.38,49.07,109.38,109.38-49.07,109.38-109.38,109.38ZM176.31,163.2c-1.53,2.06-3.89,3.15-6.27,3.15-1.62,0-3.26-.5-4.66-1.55l-45.04-33.53c-1.98-1.47-3.15-3.8-3.15-6.27V53.52c0-4.31,3.5-7.81,7.81-7.81s7.81,3.5,7.81,7.81v67.56l41.9,31.19c3.46,2.58,4.18,7.47,1.6,10.93Z"/></svg>';
}

/**
 * Icono LinkedIn según la marca activa.
 */
function astra_child_linkedin_icon_small() {
	$icon = astra_child_brand_asset_img(
		'icon_linkedin',
		array(
			'alt'   => 'LinkedIn',
			'width' => '24',
			'height'=> '24',
		)
	);

	if ( $icon ) {
		return $icon;
	}

	return '<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M19.7,3H4.3C3.582,3,3,3.582,3,4.3v15.4C3,20.418,3.582,21,4.3,21h15.4c0.718,0,1.3-0.582,1.3-1.3V4.3 C21,3.582,20.418,3,19.7,3z M8.339,18.338H5.667v-8.59h2.672V18.338z M7.004,8.574c-0.857,0-1.549-0.694-1.549-1.548 c0-0.855,0.691-1.548,1.549-1.548c0.854,0,1.547,0.694,1.547,1.548C8.551,7.881,7.858,8.574,7.004,8.574z M18.339,18.338h-2.669 v-4.177c0-0.996-0.017-2.278-1.387-2.278c-1.389,0-1.601,1.086-1.601,2.206v4.249h-2.667v-8.59h2.559v1.174h0.037 c0.356-0.675,1.227-1.387,2.526-1.387c2.703,0,3.203,1.779,3.203,4.092V18.338z"/></svg>';
}

/**
 * Icono X según la marca activa.
 */
function astra_child_twitter_icon_small() {
	$icon = astra_child_brand_asset_img(
		'icon_x',
		array(
			'alt'   => 'X',
			'width' => '24',
			'height'=> '24',
		)
	);

	if ( $icon ) {
		return $icon;
	}

	return '<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M13.982 10.622 20.54 3h-1.554l-5.693 6.618L8.745 3H3.5l6.876 10.007L3.5 21h1.554l6.012-6.989L15.868 21h5.245l-7.131-10.378Zm-2.128 2.474-.697-.997-5.543-7.93H8l4.474 6.4.697.996 5.815 8.318h-2.387l-4.745-6.787Z"/></svg>';
}

/**
 * Icono SVG de LinkedIn.
 */
function astra_child_linkedin_icon() {
	return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 250 250" width="25" aria-hidden="true"><path d="M250,250v-91.6c0-45-9.7-79.4-62.2-79.4s-42.2,13.8-49.1,26.9h-.6v-22.8h-49.7v166.9h51.9v-82.8c0-21.9,4.1-42.8,30.9-42.8s26.9,24.7,26.9,44.1v81.3h51.9v.3Z"/><path d="M4.1,83.1h51.9v166.9H4.1V83.1Z"/><path d="M30,0C13.4,0,0,13.4,0,30s13.4,30.3,30,30.3,30-13.7,30-30.3S46.6,0,30,0Z"/></svg>';
}

/**
 * Icono SVG de X/Twitter.
 */
function astra_child_twitter_icon() {
	return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 244.6 250" width="18" aria-hidden="true"><path d="M145.6,105.9L236.6,0h-21.6l-79.1,91.9L72.8,0H0l95.5,139L0,250h21.6l83.5-97.1,66.7,97.1h72.8l-99-144.1h0ZM116,140.2l-9.7-13.8L29.4,16.3h33.2l62.1,88.9,9.7,13.8,80.8,115.5h-33.1l-65.9-94.3h0Z"/></svg>';
}

/**
 * URL del archivo del autor.
 */
function astra_child_get_author_url( $author_id = null ) {
	$author_id = $author_id ? $author_id : get_the_author_meta( 'ID' );

	return get_author_posts_url( $author_id );
}

/**
 * Rol o título del autor (primer texto en negritas de la biografía).
 */
function astra_child_get_author_role( $author_id = null ) {
	$author_id  = $author_id ? $author_id : get_the_author_meta( 'ID' );
	$author_bio = get_the_author_meta( 'description', $author_id );

	if ( empty( $author_bio ) ) {
		return '';
	}

	if ( preg_match( '/<b>(.*?)<\/b>/', $author_bio, $matches ) ) {
		return trim( wp_strip_all_tags( $matches[1] ) );
	}

	$lines = preg_split( '/\r\n|\r|\n/', wp_strip_all_tags( $author_bio ) );
	$lines = array_values( array_filter( array_map( 'trim', $lines ) ) );

	return $lines[0] ?? '';
}

/**
 * Biografía completa del autor sin el rol destacado.
 */
function astra_child_get_author_bio_text( $author_id = null ) {
	$author_id  = $author_id ? $author_id : get_the_author_meta( 'ID' );
	$author_bio = get_the_author_meta( 'description', $author_id );

	if ( empty( $author_bio ) ) {
		return '';
	}

	$bio_text = preg_replace( '/<b>.*?<\/b>/', '', $author_bio );
	$bio_text = trim( wp_kses_post( $bio_text ) );

	return $bio_text;
}

/**
 * Biografía resumida del autor (contenido en etiquetas <b>).
 */
function astra_child_get_author_bio_highlight( $author_id = null ) {
	$author_id  = $author_id ? $author_id : get_the_author_meta( 'ID' );
	$author_bio = get_the_author_meta( 'description', $author_id );

	if ( empty( $author_bio ) ) {
		return '';
	}

	preg_match_all( '/<b>.*?<\/b>/', $author_bio, $matches );

	if ( empty( $matches[0] ) ) {
		return wp_kses_post( wp_trim_words( wp_strip_all_tags( $author_bio ), 40, '...' ) );
	}

	return wp_kses_post( implode( ' ', $matches[0] ) );
}

/**
 * Entradas relacionadas por meta personalizada o categoría.
 *
 * @param int|null $post_id ID del post.
 * @param int      $limit   Cantidad máxima.
 * @return WP_Query
 */
function astra_child_get_related_posts_query( $post_id = null, $limit = 6 ) {
	$post_id            = $post_id ? $post_id : get_the_ID();
	$manual_related_ids = function_exists( 'si_get_related_post_ids' ) ? si_get_related_post_ids( $post_id ) : array();

	if ( ! empty( $manual_related_ids ) ) {
		$args = array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'post__in'       => $manual_related_ids,
			'orderby'        => 'post__in',
		);
	} else {
		$args = array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'post__not_in'   => array( $post_id ),
			'orderby'        => 'rand',
		);

		$category = astra_child_get_primary_category( $post_id );

		if ( $category ) {
			$args['cat'] = $category->term_id;
		}
	}

	return new WP_Query( $args );
}
