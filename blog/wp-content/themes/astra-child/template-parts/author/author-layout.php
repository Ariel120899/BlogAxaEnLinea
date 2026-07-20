<?php
/**
 * Layout de la página de autor.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

$author      = get_queried_object();
$author_id   = $author instanceof WP_User ? (int) $author->ID : (int) get_queried_object_id();
$author_name = get_the_author_meta( 'display_name', $author_id );
$author_role = astra_child_get_author_role( $author_id );
$author_bio  = astra_child_get_author_bio_text( $author_id );
$linkedin    = get_the_author_meta( 'linkedin', $author_id );
$twitter     = get_the_author_meta( 'twitter', $author_id );
?>
<div class="blog-qualitas-author">
	<section class="blog-qualitas-author__profile">
		<div class="blog-qualitas-author__intro">
			<?php if ( $author_role ) : ?>
				<h1 class="blog-qualitas-author__role"><?php echo esc_html( $author_role ); ?></h1>
			<?php else : ?>
				<h1 class="blog-qualitas-author__role"><?php echo esc_html( $author_name ); ?></h1>
			<?php endif; ?>

			<?php if ( $author_bio ) : ?>
				<div class="blog-qualitas-author__bio">
					<?php echo wp_kses_post( wpautop( $author_bio ) ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $linkedin || $twitter ) : ?>
				<div class="blog-qualitas-author__social blog-qualitas-redes">
					<?php if ( $linkedin ) : ?>
						<a href="<?php echo esc_url( $linkedin ); ?>" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
							<?php echo astra_child_linkedin_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					<?php endif; ?>
					<?php if ( $twitter ) : ?>
						<a href="<?php echo esc_url( $twitter ); ?>" target="_blank" rel="noopener noreferrer" aria-label="X">
							<?php echo astra_child_twitter_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="blog-qualitas-author__avatar">
			<img
				src="<?php echo esc_url( get_avatar_url( $author_id, array( 'size' => 320 ) ) ); ?>"
				alt="<?php echo esc_attr( $author_name ); ?>"
				width="180"
				height="180"
				class="blog-qualitas-imgautor"
			>
		</div>
	</section>

	<section class="blog-qualitas-author__posts">
		<?php
		get_template_part(
			'template-parts/blog/posts-grid',
			null,
			array(
				'empty_message' => __( 'Este autor aún no tiene artículos publicados.', 'astra-child' ),
			)
		);
		?>
	</section>
</div>
