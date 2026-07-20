<?php
/**
 * Footer personalizado del sitio.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

$footer_links = astra_child_get_brand_footer_links();
$privacy_url  = astra_child_get_privacy_policy_url();
$logo_url     = astra_child_brand_asset_url( 'logo_footer' );
$arrow_url    = astra_child_brand_asset_url( 'arrow_footer' );
?>
<footer id="colophon" class="site-footer">
	<div id="footer" class="blog-qualitas-footer">
		<div class="center-movil">
			<?php if ( $logo_url ) : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="120">
				</a>
			<?php endif; ?>
		</div>

		<div class="flex2">
			<?php foreach ( $footer_links as $link ) : ?>
				<div class="flex">
					<?php if ( $arrow_url ) : ?>
						<img src="<?php echo esc_url( $arrow_url ); ?>" alt="" width="12" aria-hidden="true">
					<?php else : ?>
						<span><?php echo astra_child_footer_arrow_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<?php endif; ?>
					<p>
						<a class="footer-link" href="<?php echo esc_url( $link['url'] ); ?>">
							<?php echo esc_html( $link['label'] ); ?>
						</a>
					</p>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="desk"></div>

		<div>
			<p class="terminos">
				<?php
				$agent_text = astra_child_get_footer_agent_text();
				$legal_text = sprintf(
					'© %1$s SEGURO INTELIGENTE. Todos los derechos reservados. El uso de este sitio implica que aceptas nuestros Términos y condiciones, así como el <a href="%2$s">Aviso de privacidad</a>. %3$s',
					esc_html( gmdate( 'Y' ) ),
					esc_url( $privacy_url ),
					esc_html( $agent_text )
				);
				echo wp_kses_post( $legal_text );
				?>
			</p>
		</div>
	</div>
</footer>
