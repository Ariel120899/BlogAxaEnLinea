<?php
/**
 * Contenido principal del artículo.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="articulo">
	<div>
		<div class="entry-content">
			<?php the_content(); ?>
		</div>

		<br><br>

		<?php get_template_part( 'template-parts/single/author-box' ); ?>

		<br>

		<?php get_template_part( 'template-parts/single/related-posts' ); ?>
	</div>

	<?php get_template_part( 'template-parts/single/sidebar' ); ?>
</section>
