<?php
/**
 * Tarjeta de artículo relacionado.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

$post_id     = get_the_ID();
$category    = astra_child_get_primary_category( $post_id );
$author_id   = (int) get_post_field( 'post_author', $post_id );
$author_name = get_the_author_meta( 'display_name', $author_id );
$author_url  = astra_child_get_author_url( $author_id );
$reading     = astra_child_reading_time( $post_id, 200 );
?>
<div class="box-articulo">
	<?php if ( $category ) : ?>
		<h4><?php echo esc_html( $category->name ); ?></h4>
	<?php endif; ?>

	<h2>
		<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
	</h2>

	<div class="grid">
		<div class="flex">
			<a class="blog-qualitas-author-link" href="<?php echo esc_url( $author_url ); ?>">
				<img
					src="<?php echo esc_url( get_avatar_url( $author_id, array( 'size' => 60 ) ) ); ?>"
					alt="<?php echo esc_attr( $author_name ); ?>"
					width="25"
					height="25"
					class="imgautor"
				>
				<p><?php echo esc_html( $author_name ); ?></p>
			</a>
		</div>
		<p class="reading-time">
			<span class="reading-icon"><?php echo astra_child_clock_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<?php echo esc_html( $reading ); ?> <?php esc_html_e( 'minutos', 'astra-child' ); ?>
		</p>
	</div>

	<div class="btnmas">
		<a href="<?php the_permalink(); ?>"><?php esc_html_e( 'Leer más', 'astra-child' ); ?></a>
	</div>
</div>
