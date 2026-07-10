<?php
/**
 * Resultados de búsqueda del blog.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

get_header();

$search_query = get_search_query();
$title        = $search_query
	? sprintf(
		/* translators: %s: search term */
		__( 'Resultados de búsqueda para: <strong>%s</strong>', 'astra-child' ),
		esc_html( $search_query )
	)
	: __( 'Resultados de búsqueda', 'astra-child' );
?>
<div id="primary" class="content-area blog-qualitas-archive-page">
	<?php
	get_template_part(
		'template-parts/archive/archive-layout',
		null,
		array(
			'title'         => $title,
			'empty_message' => __( 'No se encontraron artículos para tu búsqueda.', 'astra-child' ),
		)
	);
	?>
</div>
<?php
get_footer();
