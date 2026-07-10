<?php
/**
 * Sección de redactores.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

$authors = astra_child_get_authors();

if ( empty( $authors ) ) {
	return;
}
?>
<h2><?php esc_html_e( 'Redactores', 'astra-child' ); ?></h2>
<div class="blog-qualitas-redactores">
	<?php foreach ( $authors as $author ) : ?>
		<?php
		$linkedin = get_user_meta( $author->ID, 'linkedin', true );
		$twitter  = get_user_meta( $author->ID, 'twitter', true );
		?>
		<div class="blog-qualitas-redactorbox">
			<a class="blog-qualitas-redactorbox-link" href="<?php echo esc_url( astra_child_get_author_url( $author->ID ) ); ?>">
			<img
				src="<?php echo esc_url( get_avatar_url( $author->ID, array( 'size' => 160 ) ) ); ?>"
				alt="<?php echo esc_attr( $author->display_name ); ?>"
				width="80"
				height="80"
				class="blog-qualitas-imgautor"
			>
			<div>
				<h3 class="blog-qualitas-nombre-redactor"><?php echo esc_html( $author->display_name ); ?></h3>
				<?php if ( $linkedin || $twitter ) : ?>
					<div class="blog-qualitas-redes">
						<?php if ( $linkedin ) : ?>
							<span aria-hidden="true">
								<?php echo astra_child_linkedin_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
						<?php endif; ?>
						<?php if ( $twitter ) : ?>
							<span aria-hidden="true">
								<?php echo astra_child_twitter_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
			</a>
		</div>
	<?php endforeach; ?>
</div>
