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
			<?php
			the_content();

			// Botón Cotizar mid-nota (en GNP va como bloque reutilizable en el contenido).
			if ( false === strpos( get_the_content( null, false ), 'btn-cothr' ) ) {
				get_template_part( 'template-parts/single/cotizar-button' );
			}
			?>
		</div>

		<br><br>

		<?php get_template_part( 'template-parts/single/author-box' ); ?>

		<br>

		<?php get_template_part( 'template-parts/single/related-posts' ); ?>
	</div>

	<?php get_template_part( 'template-parts/single/sidebar' ); ?>
</section>
