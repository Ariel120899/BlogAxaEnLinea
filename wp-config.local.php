<?php
/**
 * WordPress — Docker local (Blog AXA).
 * Copiar a wp-config.php para desarrollo local.
 *
 * @package WordPress
 */

if ( ! function_exists( 'getenv_docker' ) ) {
	function getenv_docker( $env, $default ) {
		if ( $fileEnv = getenv( $env . '_FILE' ) ) {
			return rtrim( file_get_contents( $fileEnv ), "\r\n" );
		} elseif ( ( $val = getenv( $env ) ) !== false ) {
			return $val;
		}

		return $default;
	}
}

define( 'DB_NAME', getenv_docker( 'WORDPRESS_DB_NAME', 'wordpress' ) );
define( 'DB_USER', getenv_docker( 'WORDPRESS_DB_USER', 'wordpress' ) );
define( 'DB_PASSWORD', getenv_docker( 'WORDPRESS_DB_PASSWORD', 'wordpress_secret' ) );
define( 'DB_HOST', getenv_docker( 'WORDPRESS_DB_HOST', 'db:3306' ) );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

define( 'AUTH_KEY',         getenv_docker( 'WORDPRESS_AUTH_KEY',         '86fba8dde77fa117addf405d6bf529830f4e564c' ) );
define( 'SECURE_AUTH_KEY',  getenv_docker( 'WORDPRESS_SECURE_AUTH_KEY',  '6158912177f7b16ec41ff5be0282b8361508d700' ) );
define( 'LOGGED_IN_KEY',    getenv_docker( 'WORDPRESS_LOGGED_IN_KEY',    '247e099e26114cf8f4aa4541e2bad662072cda44' ) );
define( 'NONCE_KEY',        getenv_docker( 'WORDPRESS_NONCE_KEY',        '4f54c1449a3c1b3f88b482b0369e7ea0cf36c380' ) );
define( 'AUTH_SALT',        getenv_docker( 'WORDPRESS_AUTH_SALT',        '29b518dd6f0708cb4e69a1446d2ca2545b3ddbcf' ) );
define( 'SECURE_AUTH_SALT', getenv_docker( 'WORDPRESS_SECURE_AUTH_SALT', 'f9762a69241dffcbee50b6ffd7b065d8389c2f6e' ) );
define( 'LOGGED_IN_SALT',   getenv_docker( 'WORDPRESS_LOGGED_IN_SALT',   '6973432a442d245ba59052834460c46915ea2ed3' ) );
define( 'NONCE_SALT',       getenv_docker( 'WORDPRESS_NONCE_SALT',       '25148f388959ff1376002ba88d8bdf13564b3069' ) );

$table_prefix = 'wp_';

if ( getenv( 'WORDPRESS_DB_HOST' ) ) {
	define( 'WP_HOME', 'http://localhost:8080' );
	define( 'WP_SITEURL', 'http://localhost:8080' );
	define( 'AXA_GENERIC_IMAGES_KEY', 'local-dev-axa-images' );
}

define( 'WP_DEBUG', false );

if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && strpos( $_SERVER['HTTP_X_FORWARDED_PROTO'], 'https' ) !== false ) {
	$_SERVER['HTTPS'] = 'on';
}

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
