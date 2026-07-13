<?php
/**
 * Formularios del sidebar según categoría de la nota.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Slugs de categorías que muestran el formulario de auto del tema.
 *
 * @return string[]
 */
function astra_child_get_auto_sidebar_category_slugs() {
	return apply_filters(
		'astra_child_auto_sidebar_category_slugs',
		array(
			'seguro-de-auto-axa',
		)
	);
}

/**
 * Slugs de categorías que muestran el widget GMM legacy.
 *
 * @return string[]
 */
function astra_child_get_gmm_sidebar_category_slugs() {
	return apply_filters(
		'astra_child_gmm_sidebar_category_slugs',
		array(
			'seguro-de-gastos-medicos-mayores-axa',
		)
	);
}

/**
 * Comprueba si el post pertenece a alguna categoría (o ancestro) de la lista.
 *
 * @param int|null $post_id ID del post.
 * @param string[] $slugs   Slugs de categoría.
 */
function astra_child_post_in_category_slugs( $post_id, $slugs ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();

	if ( $post_id <= 0 ) {
		return false;
	}

	$categories = get_the_category( $post_id );

	if ( empty( $categories ) || ! is_array( $categories ) || empty( $slugs ) ) {
		return false;
	}

	foreach ( $categories as $category ) {
		if ( ! $category instanceof WP_Term ) {
			continue;
		}

		$current = $category;

		while ( $current instanceof WP_Term ) {
			if ( in_array( $current->slug, $slugs, true ) ) {
				return true;
			}

			if ( ! $current->parent ) {
				break;
			}

			$current = get_category( $current->parent );

			if ( ! $current instanceof WP_Term ) {
				break;
			}
		}
	}

	return false;
}

/**
 * Indica si la nota debe mostrar el formulario de auto del tema.
 *
 * @param int|null $post_id ID del post.
 */
function astra_child_post_uses_auto_sidebar_form( $post_id = null ) {
	return astra_child_post_in_category_slugs( $post_id, astra_child_get_auto_sidebar_category_slugs() );
}

/**
 * Indica si la nota debe mostrar el widget GMM legacy.
 *
 * @param int|null $post_id ID del post.
 */
function astra_child_post_uses_gmm_sidebar_form( $post_id = null ) {
	return astra_child_post_in_category_slugs( $post_id, astra_child_get_gmm_sidebar_category_slugs() );
}

/**
 * Tipo de formulario del sidebar para la nota actual.
 *
 * @param int|null $post_id ID del post.
 * @return string auto|gmm|none
 */
function astra_child_get_sidebar_form_type( $post_id = null ) {
	if ( astra_child_post_uses_gmm_sidebar_form( $post_id ) ) {
		return 'gmm';
	}

	if ( astra_child_post_uses_auto_sidebar_form( $post_id ) ) {
		return 'auto';
	}

	return 'none';
}

/**
 * Filtra el sidebar para mostrar solo el widget indicado.
 *
 * @param array<string, array<int, string>> $sidebars Sidebars registrados.
 * @param string                            $widget_id ID del widget (ej. block-8).
 * @return array<string, array<int, string>>
 */
function astra_child_filter_sidebar_to_widget( $sidebars, $widget_id ) {
	if ( empty( $sidebars['blog-sidebar'] ) ) {
		return $sidebars;
	}

	$filtered = array_values(
		array_filter(
			$sidebars['blog-sidebar'],
			static function ( $id ) use ( $widget_id ) {
				return $widget_id === $id;
			}
		)
	);

	if ( ! empty( $filtered ) ) {
		$sidebars['blog-sidebar'] = $filtered;
	}

	return $sidebars;
}

/**
 * Renderiza el widget GMM (block-8) del sidebar del blog.
 */
function astra_child_render_gmm_sidebar_form() {
	$filter = static function ( $sidebars ) {
		return astra_child_filter_sidebar_to_widget( $sidebars, 'block-8' );
	};

	add_filter( 'sidebars_widgets', $filter, 100 );

	echo '<div class="blog-single-widget-top blog-sidebar-widgets blog-sidebar-form-gmm">';

	if ( is_active_sidebar( 'blog-sidebar' ) ) {
		dynamic_sidebar( 'blog-sidebar' );
	}

	echo '</div>';

	remove_filter( 'sidebars_widgets', $filter, 100 );
}
