<?php
/**
 * Banner promocional del sidebar de la nota.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

$banner_desk_url  = astra_child_brand_asset_url( 'banner_sidebar_desk' );
$banner_movil_url = astra_child_brand_asset_url( 'banner_sidebar_mob' );

if ( empty( $banner_desk_url ) && empty( $banner_movil_url ) ) {
	return;
}

$config = astra_child_get_quote_widget_config();
$link   = ! empty( $config['banner_link'] ) ? $config['banner_link'] : '';
$single = $banner_desk_url && $banner_desk_url === $banner_movil_url;
?>
<div class="blog-sidebar-promo"<?php echo $link ? ' data-promo-link="' . esc_url( $link ) . '"' : ''; ?>>
	<?php if ( $single ) : ?>
		<img src="<?php echo esc_url( $banner_desk_url ); ?>" alt="<?php esc_attr_e( 'Promoción lateral', 'astra-child' ); ?>">
	<?php else : ?>
		<?php if ( $banner_desk_url ) : ?>
			<img class="blog-sidebar-promo-desk" src="<?php echo esc_url( $banner_desk_url ); ?>" alt="<?php esc_attr_e( 'Promoción lateral', 'astra-child' ); ?>">
		<?php endif; ?>
		<?php if ( $banner_movil_url ) : ?>
			<img class="blog-sidebar-promo-mob" src="<?php echo esc_url( $banner_movil_url ); ?>" alt="<?php esc_attr_e( 'Promoción lateral', 'astra-child' ); ?>">
		<?php endif; ?>
	<?php endif; ?>
</div>
