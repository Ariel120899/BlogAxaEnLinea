<?php
/**
 * Astra Child — tema hijo de Astra
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

require_once get_stylesheet_directory() . '/inc/helpers.php';
require_once get_stylesheet_directory() . '/inc/class-walker-category.php';
require_once get_stylesheet_directory() . '/inc/theme-config.php';
require_once get_stylesheet_directory() . '/inc/brand-assets.php';
require_once get_stylesheet_directory() . '/inc/customizer.php';
require_once get_stylesheet_directory() . '/inc/blog-load-more.php';
require_once get_stylesheet_directory() . '/inc/archive-pagination.php';
require_once get_stylesheet_directory() . '/inc/quote-widget.php';
require_once get_stylesheet_directory() . '/inc/footer.php';

/**
 * Registra las barras laterales del blog.
 */
function astra_child_register_sidebars() {
	register_sidebar(
		array(
			'name'          => __( 'Widget superior de nota', 'astra-child' ),
			'id'            => 'blog-single-top',
			'description'   => __( 'Aparece arriba de las categorías en la nota (ej. formulario Cotiza).', 'astra-child' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);

	register_sidebar(
		array(
			'name'          => __( 'Sidebar del blog', 'astra-child' ),
			'id'            => 'blog-sidebar',
			'description'   => __( 'Widgets adicionales del blog.', 'astra-child' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
}
add_action( 'widgets_init', 'astra_child_register_sidebars' );

/**
 * Determina si se debe aplicar el layout del index del blog.
 */
function astra_child_is_blog_home_view() {
	return is_home() || is_front_page();
}

/**
 * Vistas de listado del blog que comparten estilos de tarjetas.
 */
function astra_child_is_blog_listing_view() {
	return astra_child_is_blog_home_view() || astra_child_is_paginated_blog_archive();
}

/**
 * Encola estilos y fuentes del tema hijo.
 */
function astra_child_enqueue_styles() {
	wp_enqueue_style(
		'astra-child-style',
		get_stylesheet_uri(),
		array( 'astra-theme-css' ),
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_style(
		'astra-child-footer',
		get_stylesheet_directory_uri() . '/assets/css/footer.css',
		array( 'astra-child-style' ),
		wp_get_theme()->get( 'Version' )
	);

	if ( astra_child_is_blog_home_view() ) {
		wp_enqueue_style(
			'astra-child-blog-home',
			get_stylesheet_directory_uri() . '/assets/css/blog-home.css',
			array( 'astra-child-style' ),
			wp_get_theme()->get( 'Version' )
		);

		wp_enqueue_script(
			'astra-child-blog-home',
			get_stylesheet_directory_uri() . '/assets/js/blog-home.js',
			array(),
			wp_get_theme()->get( 'Version' ),
			true
		);
	}

	if ( is_singular( 'post' ) ) {
		wp_enqueue_style(
			'astra-child-blog-single',
			get_stylesheet_directory_uri() . '/assets/css/blog-single.css',
			array( 'astra-child-style' ),
			wp_get_theme()->get( 'Version' )
		);
	}

	if ( astra_child_is_paginated_blog_archive() ) {
		wp_enqueue_style(
			'astra-child-blog-home',
			get_stylesheet_directory_uri() . '/assets/css/blog-home.css',
			array( 'astra-child-style' ),
			wp_get_theme()->get( 'Version' )
		);
	}

	if ( is_author() ) {
		wp_enqueue_style(
			'astra-child-blog-author',
			get_stylesheet_directory_uri() . '/assets/css/blog-author.css',
			array( 'astra-child-blog-home' ),
			wp_get_theme()->get( 'Version' )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'astra_child_enqueue_styles', 15 );

/**
 * Usa layout sin sidebar de Astra en home y entradas.
 */
function astra_child_blog_home_layout( $layout ) {
	if ( astra_child_is_blog_home_view() || is_singular( 'post' ) || astra_child_is_paginated_blog_archive() ) {
		return 'no-sidebar';
	}

	return $layout;
}
add_filter( 'astra_page_layout', 'astra_child_blog_home_layout' );

/**
 * Clases de body para estilos del blog.
 */
function astra_child_body_class( $classes ) {
	if ( astra_child_is_blog_home_view() ) {
		$classes[] = 'blog-qualitas-page';
	}

	$classes[] = 'blog-brand-' . astra_child_get_active_preset_slug();

	if ( is_singular( 'post' ) ) {
		$classes[] = 'blog-qualitas-single';
	}

	if ( is_author() ) {
		$classes[] = 'blog-qualitas-author-page';
	}

	if ( astra_child_is_paginated_blog_archive() && ! is_author() ) {
		$classes[] = 'blog-qualitas-archive-page';
	}

	return $classes;
}
add_filter( 'body_class', 'astra_child_body_class' );

/**
 * El home usa carga incremental; redirige páginas numeradas al inicio.
 */
function astra_child_redirect_paged_blog_home() {
	if ( astra_child_is_blog_home_view() && is_paged() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'astra_child_redirect_paged_blog_home' );

/**
 * Usa /autor/ como base de archivo de autor.
 */
function astra_child_set_author_base() {
	global $wp_rewrite;
	$wp_rewrite->author_base = 'autor';
}
add_action( 'init', 'astra_child_set_author_base' );

/**
 * Campos de redes sociales en el perfil de usuario.
 */
function astra_child_author_contact_fields( $fields ) {
	$fields['linkedin'] = __( 'LinkedIn URL', 'astra-child' );
	$fields['twitter']  = __( 'X (Twitter) URL', 'astra-child' );

	return $fields;
}
add_filter( 'user_contactmethods', 'astra_child_author_contact_fields' );
