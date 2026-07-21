<?php
/**
 * Banner promocional del home.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

$banner_desk_url = astra_child_brand_asset_url( 'banner_home_desk' );
$banner_movil_url = astra_child_brand_asset_url( 'banner_home_mob' );

if ( empty( $banner_desk_url ) && empty( $banner_movil_url ) ) {
	return;
}
?>
<div class="blog-qualitas-promocional1">
	<?php if ( $banner_desk_url ) : ?>
		<div class="blog-qualitas-desk" onclick="window.location.href='https://axasegurosenlinea.com.mx/'" style="cursor:pointer">
			<img src="<?php echo esc_url( $banner_desk_url ); ?>" alt="<?php esc_attr_e( 'Promoción', 'astra-child' ); ?>">
		</div>
	<?php endif; ?>
	<?php if ( $banner_movil_url ) : ?>
		<div class="blog-qualitas-movil" onclick="window.location.href='https://axasegurosenlinea.com.mx/'" style="cursor:pointer">
			<img src="<?php echo esc_url( $banner_movil_url ); ?>" alt="<?php esc_attr_e( 'Promoción', 'astra-child' ); ?>">
		</div>
	<?php endif; ?>
</div>
