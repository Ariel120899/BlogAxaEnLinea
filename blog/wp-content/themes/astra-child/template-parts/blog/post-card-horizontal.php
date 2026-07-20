<?php
/**
 * Tarjeta horizontal de nota (carga incremental del home).
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

$post_id = isset( $args['post_id'] ) ? absint( $args['post_id'] ) : get_the_ID();

if ( ! $post_id ) {
	return;
}

$permalink   = get_permalink( $post_id );
$title       = get_the_title( $post_id );
$excerpt     = get_the_excerpt( $post_id );
$author_id   = (int) get_post_field( 'post_author', $post_id );
$author_name = get_the_author_meta( 'display_name', $author_id );
$author_url  = astra_child_get_author_url( $author_id );
$category    = astra_child_get_primary_category( $post_id );
$image_url   = astra_child_get_post_image_url( $post_id );
$reading     = astra_child_reading_time( $post_id );
?>
<article class="blog-qualitas-boxnota-horizontal">
	<a class="blog-qualitas-boxnota-horizontal__media" href="<?php echo esc_url( $permalink ); ?>">
		<img
			src="<?php echo esc_url( $image_url ); ?>"
			alt="<?php echo esc_attr( $title ); ?>"
			class="blog-qualitas-boxnota-horizontal__img"
			loading="lazy"
		>
	</a>

	<div class="blog-qualitas-boxnota-horizontal__content">
		<?php if ( $category ) : ?>
			<span class="blog-qualitas-categorialabel"><?php echo esc_html( $category->name ); ?></span>
		<?php endif; ?>

		<h3 class="blog-qualitas-boxnota-horizontal__title">
			<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
		</h3>

		<p class="blog-qualitas-boxnota-horizontal__excerpt"><?php echo esc_html( wp_trim_words( $excerpt, 28, '...' ) ); ?></p>

		<div class="blog-qualitas-boxnota-horizontal__meta">
			<a class="blog-qualitas-author-link" href="<?php echo esc_url( $author_url ); ?>">
				<img
					src="<?php echo esc_url( get_avatar_url( $author_id, array( 'size' => 40 ) ) ); ?>"
					alt="<?php echo esc_attr( $author_name ); ?>"
					class="blog-qualitas-imgautor"
					height="24"
					width="24"
				>
				<p class="blog-qualitas-boxnota-horizontal__author">
					<?php
					printf(
						/* translators: 1: author name, 2: reading time */
						esc_html__( '%1$s · %2$d min de lectura', 'astra-child' ),
						esc_html( $author_name ),
						(int) $reading
					);
					?>
				</p>
			</a>
		</div>

		<a href="<?php echo esc_url( $permalink ); ?>" class="blog-qualitas-leermas blog-qualitas-leermas--outline">
			<?php esc_html_e( 'Leer más', 'astra-child' ); ?>
		</a>
	</div>
</article>
