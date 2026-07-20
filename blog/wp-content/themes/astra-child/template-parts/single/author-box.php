<?php
/**
 * Caja de autor al final del artículo.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

$author_id   = (int) get_the_author_meta( 'ID' );
$author_url  = astra_child_get_author_url( $author_id );
$author_bio  = astra_child_get_author_bio_highlight( $author_id );
$linkedin    = get_the_author_meta( 'linkedin', $author_id );
$twitter     = get_the_author_meta( 'twitter', $author_id );
?>
<div class="redesautor flex">
	<div>
		<img
			src="<?php echo esc_url( get_avatar_url( $author_id, array( 'size' => 190 ) ) ); ?>"
			alt="<?php echo esc_attr( get_the_author() ); ?>"
			width="95"
			height="95"
			class="imgautor"
		>
	</div>
	<div>
		<h3>
			<a href="<?php echo esc_url( $author_url ); ?>"><?php the_author(); ?></a>
		</h3>
		<?php if ( $linkedin || $twitter ) : ?>
			<div class="flex">
				<?php if ( $linkedin ) : ?>
					<a href="<?php echo esc_url( $linkedin ); ?>" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
						<?php echo astra_child_linkedin_icon_small(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				<?php endif; ?>
				<?php if ( $twitter ) : ?>
					<a href="<?php echo esc_url( $twitter ); ?>" target="_blank" rel="noopener noreferrer" aria-label="X">
						<?php echo astra_child_twitter_icon_small(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php if ( $author_bio ) : ?>
			<div class="descripcion-autor">
				<p style="white-space: pre-line; line-height: 1.2;"><?php echo $author_bio; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
			</div>
		<?php endif; ?>
	</div>
</div>
