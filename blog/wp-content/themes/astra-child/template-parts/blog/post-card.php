<?php
/**
 * Tarjeta de nota del blog.
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
<div class="blog-qualitas-boxnota">
	<a href="<?php echo esc_url( $permalink ); ?>">
		<img
			src="<?php echo esc_url( $image_url ); ?>"
			alt="<?php echo esc_attr( $title ); ?>"
			class="blog-qualitas-img-nota"
			loading="lazy"
		>
	</a>
	<div class="blog-qualitas-padding">
		<?php if ( $category ) : ?>
			<span class="blog-qualitas-categorialabel"><?php echo esc_html( $category->name ); ?></span>
		<?php endif; ?>

		<h4 class="blog-qualitas-titulo-nota">
			<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
		</h4>

		<p class="blog-qualitas-descripcion-nota"><?php echo esc_html( wp_trim_words( $excerpt, 18, '...' ) ); ?></p>

		<div class="blog-qualitas-flexnota">
			<div class="blog-qualitas-flex">
				<a class="blog-qualitas-author-link" href="<?php echo esc_url( $author_url ); ?>">
					<img
						src="<?php echo esc_url( get_avatar_url( $author_id, array( 'size' => 40 ) ) ); ?>"
						alt="<?php echo esc_attr( $author_name ); ?>"
						class="blog-qualitas-imgautor"
						height="20"
						width="20"
					>
					<p class="blog-qualitas-autor"><?php echo esc_html( $author_name ); ?></p>
				</a>
			</div>
			<div class="blog-qualitas-flex">
				<span><?php echo astra_child_clock_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<p class="blog-qualitas-timenota"><?php echo esc_html( $reading ); ?> minutos</p>
			</div>
		</div>

		<a href="<?php echo esc_url( $permalink ); ?>" class="blog-qualitas-leermas">Leer más</a>
	</div>
</div>
