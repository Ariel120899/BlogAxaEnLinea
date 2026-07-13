<?php
/**
 * WordPress — producción Blog AXA.
 *
 * @package WordPress
 */

define( 'DB_NAME', 'lp_axaenlinea_db' );
define( 'DB_USER', 'blog' );
define( 'DB_PASSWORD', 'Seguro2023' );
define( 'DB_HOST', '172.26.110.9' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

define( 'AUTH_KEY',          '@e{1Pe711shlKmip&tdLFmBmCMC(Oq%r;<a/Jgm^Oe(}-PdS!{oa:imjPloS!4r^' );
define( 'SECURE_AUTH_KEY',   'Kw`F`p$qUFr>&2-7z|Z,s&C6#843aFG>l|Z~}3P2lI54aiNMD1x,acea03vM3BZ7' );
define( 'LOGGED_IN_KEY',     'bwfBoCkPk3$U/|P!UR-(/<[Ral2k#NPHW/%7lK.GDNc^w-FpQ.d27#{aeDdM$6/h' );
define( 'NONCE_KEY',         '6Qzp1|U:Ok43d(BW:-x~f6{O!D?B%Lg7h@OUp.a7aLIb%.Nf[@iwDIWa1~~tIXa3' );
define( 'AUTH_SALT',         'uu%CZk]/U?5hv62EcDeKK=v%m?Ch+5(Z-S48^=aR0GAL&q_rW]HPTjvQR`OD[:Jm' );
define( 'SECURE_AUTH_SALT',  '$@R_N;ZD</KI* vO^nf6]P]+Aoc*#.U}M.N:t^A/KX.ZVMS:K8o,RHA)~~8S fiN' );
define( 'LOGGED_IN_SALT',    'rJ};U]S<9oDsqG*~21UyiD|@U,|5vD1(1,&-QXN%Hmx i881z])VPw][uY@qS#y4' );
define( 'NONCE_SALT',        '1Z(?*XV|bN:lF8X*TT}rviZ{ooDoY>`hw._7Q9bc>SH|WkH:A0kf.yA!ES.[8?yH' );
define( 'WP_CACHE_KEY_SALT', 'VM6Vo1:L>OT#vRcQF|NrE;[F%z-`O26CFu/g^QvcO5/D8Yo02@o^GkA8s ,ZFM@r' );

$table_prefix = 'wp_';

define( 'WP_HOME', 'https://axasegurosenlinea.com.mx/blog' );
define( 'WP_SITEURL', 'https://axasegurosenlinea.com.mx/blog' );

define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );

define( 'AUTOMATIC_UPDATER_DISABLED', true );
define( 'WP_AUTO_UPDATE_CORE', false );
define( 'DISABLE_WP_CRON', true );
define( 'WP_CACHE', true );

define( 'AXA_GENERIC_IMAGES_KEY', 'axa-asignacion-imagenes-2026' );

if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
	$_SERVER['HTTPS'] = 'on';
}

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
