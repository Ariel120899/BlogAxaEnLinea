<?php
/**
 * Plantilla de la portada del sitio.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div id="primary" class="content-area blog-qualitas-page">
	<?php get_template_part( 'template-parts/blog/home-layout' ); ?>
</div>
<?php
get_footer();
