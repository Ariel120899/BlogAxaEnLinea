<?php
/**
 * Walker personalizado para categorías con indicador.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Walker de categorías con flecha.
 */
class Astra_Child_Walker_Category extends Walker_Category {

	/**
	 * Inicio de elemento.
	 *
	 * @param string $output HTML acumulado.
	 * @param object $category Objeto de categoría.
	 * @param int    $depth Nivel de profundidad.
	 * @param array  $args Argumentos.
	 * @param int    $id ID del elemento.
	 */
	public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		$children     = get_term_children( $category->term_id, 'category' );
		$has_children = ! empty( $children ) && ! is_wp_error( $children );
		$class        = $has_children ? 'toggle-subcategories' : 'toggle-subcategories-none';

		$output .= '<li class="cat-item cat-item-' . esc_attr( $category->term_id ) . '">';
		$output .= '<span class="' . esc_attr( $class ) . '">❯</span>';
		$output .= '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '">';
		$output .= esc_html( $category->name );
		$output .= '</a>';
	}
}
