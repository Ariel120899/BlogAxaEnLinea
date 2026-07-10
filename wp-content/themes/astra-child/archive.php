<?php
/**
 * Archivos de categoría y etiqueta del blog.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

get_header();

$title = '';

if ( is_category() ) {
	$title = sprintf(
		/* translators: %s: category name */
		__( 'Categoría: <strong>%s</strong>', 'astra-child' ),
		single_cat_title( '', false )
	);
} elseif ( is_tag() ) {
	$title = sprintf(
		/* translators: %s: tag name */
		__( 'Etiqueta: <strong>%s</strong>', 'astra-child' ),
		single_tag_title( '', false )
	);
}
?>
<div id="primary" class="content-area blog-qualitas-archive-page">
	<?php
	get_template_part(
		'template-parts/archive/archive-layout',
		null,
		array(
			'title'         => $title,
			'empty_message' => __( 'No hay artículos en este archivo.', 'astra-child' ),
		)
	);
	?>
</div>
<?php
get_footer();
