<?php
/**
 * Layout principal del home del blog.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

$paged          = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$featured_posts = astra_child_get_featured_posts( 3 );
$featured_ids   = wp_list_pluck( $featured_posts, 'ID' );
$initial_count  = 3;
$latest_total   = astra_child_get_latest_posts_total();
$has_more_posts = $latest_total > $initial_count;

$latest_query = new WP_Query(
	astra_child_get_latest_posts_query_args(
		array(
			'posts_per_page' => $initial_count,
			'offset'         => 0,
		)
	)
);
?>
<div class="blog-qualitas-home">
	<section class="blog-qualitas-section">
		<div>
			<?php if ( 1 === $paged ) : ?>
				<h2><?php esc_html_e( 'Destacado', 'astra-child' ); ?></h2>
				<?php if ( ! empty( $featured_posts ) ) : ?>
					<div class="blog-qualitas-grid3">
						<?php foreach ( $featured_posts as $featured_post ) : ?>
							<div>
								<?php
								get_template_part(
									'template-parts/blog/post-card',
									null,
									array( 'post_id' => $featured_post->ID )
								);
								?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<p class="blog-qualitas-empty"><?php esc_html_e( 'No hay artículos destacados.', 'astra-child' ); ?></p>
				<?php endif; ?>

				<?php get_template_part( 'template-parts/blog/promo-banner' ); ?>
			<?php endif; ?>

			<h2><?php esc_html_e( 'Lo último', 'astra-child' ); ?></h2>

			<?php if ( $latest_query->have_posts() ) : ?>
				<div class="blog-qualitas-grid3" data-blog-latest-grid>
					<?php
					while ( $latest_query->have_posts() ) :
						$latest_query->the_post();
						?>
						<div>
							<?php get_template_part( 'template-parts/blog/post-card' ); ?>
						</div>
					<?php endwhile; ?>
				</div>

				<div class="blog-qualitas-latest-more" data-blog-latest-more></div>

				<?php if ( $has_more_posts ) : ?>
					<div class="blog-qualitas-center blog-qualitas-pagination">
						<button
							type="button"
							class="blog-qualitas-masarticulos"
							data-blog-load-more
							data-offset="<?php echo esc_attr( $initial_count ); ?>"
							data-ajax-url="<?php echo esc_url( rest_url( 'astra-child/v1/load-more' ) ); ?>"
							data-nonce="<?php echo esc_attr( wp_create_nonce( 'astra_child_load_more' ) ); ?>"
							data-loading-text="<?php esc_attr_e( 'Cargando...', 'astra-child' ); ?>"
						>
							<?php esc_html_e( 'Ver más artículos', 'astra-child' ); ?>
						</button>
					</div>
				<?php endif; ?>
			<?php else : ?>
				<p class="blog-qualitas-empty"><?php esc_html_e( 'No hay artículos publicados todavía.', 'astra-child' ); ?></p>
			<?php endif; ?>

			<?php wp_reset_postdata(); ?>

			<?php if ( 1 === $paged ) : ?>
				<br>
				<?php get_template_part( 'template-parts/blog/authors-section' ); ?>
			<?php endif; ?>
		</div>

		<aside>
			<?php get_template_part( 'template-parts/blog/categories-sidebar' ); ?>
		</aside>
	</section>
</div>
