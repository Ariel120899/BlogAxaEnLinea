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
	'exclude'    => astra_child_get_excluded_category_ids(),
	'walker'     => new Astra_Child_Walker_Category(),
);

if ( $parent_category_id ) {
	$category_args['child_of'] = (int) $parent_category_id;
}
?>
<aside id="secondary" class="widget-area sidebar-right">
	<?php if ( astra_child_post_uses_gmm_sidebar_form() ) : ?>
		<?php astra_child_render_gmm_sidebar_form(); ?>
	<?php elseif ( astra_child_post_uses_auto_sidebar_form() ) : ?>
		<div class="blog-single-widget-top blog-sidebar-widgets blog-sidebar-form-auto">
			<?php get_template_part( 'template-parts/single/quote-widget' ); ?>
		</div>
	<?php elseif ( is_active_sidebar( 'blog-sidebar' ) ) : ?>
		<div class="blog-single-widget-top blog-sidebar-widgets">
			<?php dynamic_sidebar( 'blog-sidebar' ); ?>
		</div>
	<?php elseif ( is_active_sidebar( 'blog-single-top' ) ) : ?>
		<div class="blog-single-widget-top blog-sidebar-widgets">
			<?php dynamic_sidebar( 'blog-single-top' ); ?>
		</div>
	<?php else : ?>
		<div class="blog-single-widget-top blog-sidebar-widgets">
			<?php get_template_part( 'template-parts/single/quote-widget' ); ?>
		</div>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/single/sidebar-promo' ); ?>

	<div class="widget-categorias">
		<h3 class="widget-title"><?php esc_html_e( 'Categorías', 'astra-child' ); ?></h3>
		<ul class="categorias-list">
			<?php wp_list_categories( $category_args ); ?>
		</ul>
	</div>
</aside>
