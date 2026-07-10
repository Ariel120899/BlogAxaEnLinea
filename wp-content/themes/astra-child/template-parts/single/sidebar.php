<?php
/**
 * Sidebar del artículo.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

$parent_category_id = apply_filters( 'astra_child_category_parent_id', 0 );
$category_args      = array(
	'title_li'   => '',
	'hide_empty' => 0,
	'walker'     => new Astra_Child_Walker_Category(),
);

if ( $parent_category_id ) {
	$category_args['child_of'] = (int) $parent_category_id;
}
?>
<aside id="secondary" class="widget-area sidebar-right">
	<div class="blog-single-widget-top">
		<?php if ( is_active_sidebar( 'blog-single-top' ) ) : ?>
			<?php dynamic_sidebar( 'blog-single-top' ); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/single/quote-widget' ); ?>
		<?php endif; ?>
	</div>

	<?php get_template_part( 'template-parts/single/sidebar-promo' ); ?>

	<div class="widget-categorias">
		<h3 class="widget-title"><?php esc_html_e( 'Categorías', 'astra-child' ); ?></h3>
		<ul class="categorias-list">
			<?php wp_list_categories( $category_args ); ?>
		</ul>
	</div>
</aside>
