<?php
/**
 * Botón Cotizar mid-nota (equivalente al bloque reutilizable de GNP).
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

$config = astra_child_get_quote_widget_config();
$url    = ! empty( $config['banner_link'] ) ? $config['banner_link'] : home_url( '/' );
$label  = __( '¡Cotiza aquí!', 'astra-child' );
?>
<div class="wp-block-buttons btn-cothr is-content-justification-center is-layout-flex wp-block-buttons-is-layout-flex">
	<hr>
	<div class="wp-block-button">
		<a
			class="wp-block-button__link has-background wp-element-button"
			href="<?php echo esc_url( $url ); ?>"
			target="_blank"
			rel="noopener noreferrer"
		>
			<strong><?php echo esc_html( $label ); ?></strong>
		</a>
	</div>
	<hr>
</div>
