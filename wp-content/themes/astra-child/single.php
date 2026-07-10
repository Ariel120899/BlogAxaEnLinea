<?php
/**
 * Plantilla de entrada individual.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div id="primary" class="content-area blog-qualitas-single">
	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/single/article-banner' );
		get_template_part( 'template-parts/single/article-layout' );
	endwhile;
	?>
</div>
<?php
get_footer();
