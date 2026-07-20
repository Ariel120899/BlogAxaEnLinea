<?php
/**
 * Sidebar de categorías.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

$categories = get_categories(
	array(
		'hide_empty' => false,
		'exclude'    => array_filter(
			array_map(
				function ( $slug ) {
					$category = get_category_by_slug( $slug );

					return $category ? (int) $category->term_id : 0;
				},
				astra_child_get_excluded_category_slugs()
			)
		),
		'orderby'    => 'name',
		'order'      => 'ASC',
	)
);
?>
<h2><?php esc_html_e( 'Categorías', 'astra-child' ); ?></h2>
<?php if ( ! empty( $categories ) ) : ?>
	<ul class="blog-qualitas-ulcategorias">
		<?php foreach ( $categories as $category ) : ?>
			<li>
				<a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>">
					<?php echo esc_html( $category->name ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
<?php else : ?>
	<p class="blog-qualitas-empty"><?php esc_html_e( 'No hay categorías disponibles.', 'astra-child' ); ?></p>
<?php endif; ?>
