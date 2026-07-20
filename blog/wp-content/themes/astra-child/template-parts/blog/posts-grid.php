<?php
/**
 * Grid de notas con paginador para archivos del blog.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

$empty_message = isset( $args['empty_message'] ) ? $args['empty_message'] : __( 'No hay artículos publicados todavía.', 'astra-child' );
?>
<?php if ( have_posts() ) : ?>
	<?php
	global $wp_query;
	$archive_max_pages = (int) $wp_query->max_num_pages;
	?>
	<div class="blog-qualitas-grid3">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<div>
				<?php get_template_part( 'template-parts/blog/post-card' ); ?>
			</div>
		<?php endwhile; ?>
	</div>

	<?php astra_child_render_archive_pagination( $archive_max_pages ); ?>
<?php else : ?>
	<p class="blog-qualitas-empty"><?php echo esc_html( $empty_message ); ?></p>
<?php endif; ?>
