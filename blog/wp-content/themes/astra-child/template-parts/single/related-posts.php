<?php
/**
 * Entradas relacionadas.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

$related_query = astra_child_get_related_posts_query();
?>
<h2 style="margin-bottom: 10px;"><?php esc_html_e( 'También podría interesarte', 'astra-child' ); ?></h2>

<?php if ( $related_query->have_posts() ) : ?>
	<div class="grid3articulos">
		<?php
		while ( $related_query->have_posts() ) :
			$related_query->the_post();
			get_template_part( 'template-parts/single/related-post-card' );
		endwhile;
		wp_reset_postdata();
		?>
	</div>
<?php else : ?>
	<p><?php esc_html_e( 'No hay artículos relacionados.', 'astra-child' ); ?></p>
<?php endif; ?>
