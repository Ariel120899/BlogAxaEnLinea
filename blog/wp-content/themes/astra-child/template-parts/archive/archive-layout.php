<?php
/**
 * Layout de archivos del blog (categoría, etiqueta, búsqueda).
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

$title = isset( $args['title'] ) ? $args['title'] : '';
?>
<div class="blog-qualitas-archive">
	<?php if ( $title ) : ?>
		<h2 class="blog-qualitas-archive__title"><?php echo wp_kses_post( $title ); ?></h2>
	<?php endif; ?>

	<?php
	get_template_part(
		'template-parts/blog/posts-grid',
		null,
		array(
			'empty_message' => isset( $args['empty_message'] ) ? $args['empty_message'] : __( 'No hay artículos publicados todavía.', 'astra-child' ),
		)
	);
	?>
</div>
