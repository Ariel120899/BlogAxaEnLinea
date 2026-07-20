<?php
/**
 * Paginador de archivos del blog.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

$links = isset( $args['links'] ) && is_array( $args['links'] ) ? $args['links'] : array();

if ( empty( $links ) ) {
	return;
}
?>
<nav class="blog-qualitas-center blog-qualitas-pagination" aria-label="<?php esc_attr_e( 'Paginación de artículos', 'astra-child' ); ?>">
	<ul class="blog-qualitas-paginator">
		<?php foreach ( $links as $link ) : ?>
			<li><?php echo wp_kses_post( $link ); ?></li>
		<?php endforeach; ?>
	</ul>
</nav>
