<?php
/**
 * Banner superior del artículo.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

$author_id    = (int) get_the_author_meta( 'ID' );
$author_url   = astra_child_get_author_url( $author_id );
$category     = astra_child_get_primary_category();
$reading_time = astra_child_reading_time( get_the_ID(), 400 );
?>
<section class="banner-articulo">
	<div class="center">
		<img
			src="<?php echo esc_url( astra_child_get_post_image_url( get_the_ID(), 'large' ) ); ?>"
			alt="<?php echo esc_attr( get_the_title() ); ?>"
			width="250"
			id="imgarticulo"
		>
	</div>
	<div>
		<?php if ( $category ) : ?>
			<h4><?php echo esc_html( $category->name ); ?></h4>
		<?php endif; ?>

		<h1><?php the_title(); ?></h1>

		<div class="autordiv">
			<p class="minutes"><?php echo esc_html( get_the_date() ); ?></p>
			<div class="flex">
				<a class="blog-qualitas-author-link" href="<?php echo esc_url( $author_url ); ?>">
					<img
						src="<?php echo esc_url( get_avatar_url( $author_id, array( 'size' => 80 ) ) ); ?>"
						alt="<?php echo esc_attr( get_the_author() ); ?>"
						width="40"
						height="40"
						class="imgautor"
					>
					<p><?php the_author(); ?></p>
				</a>
			</div>
			<div class="flex align-center">
				<p class="minutes">
					<?php
					printf(
						/* translators: %d: reading time in minutes */
						esc_html__( 'Tiempo de lectura: %d minutos', 'astra-child' ),
						(int) $reading_time
					);
					?>
				</p>
			</div>
		</div>
	</div>
</section>
