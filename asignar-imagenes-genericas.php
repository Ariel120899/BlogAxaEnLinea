<?php
/**
 * Asigna imágenes genéricas aleatorias a notas con imagen destacada rota.
 *
 * URL: /asignar-imagenes-genericas.php
 *      /asignar-imagenes-genericas.php?dry=1   (solo simula, no escribe)
 *
 * Requiere sesión de administrador o clave definida en wp-config:
 * define( 'AXA_GENERIC_IMAGES_KEY', 'tu-clave-secreta' );
 * /asignar-imagenes-genericas.php?key=tu-clave-secreta
 *
 * Producción:
 * - Subir la carpeta ImagenesAXA/ junto a la raíz del WordPress del blog.
 * - Agregar AXA_GENERIC_IMAGES_KEY en el wp-config.php del servidor (no el de Docker).
 * - Ejecutar una vez si faltan imágenes destacadas en uploads.
 *
 * @package Axasegurosenlinea
 */

define( 'SHORTINIT', false );

require_once __DIR__ . '/wp-load.php';

define( 'AXA_GENERIC_IMAGES_SUFFIX', '-asignacion-axa' );

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/**
 * Comprueba autorización.
 */
function axa_generic_images_can_run() {
	if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
		return true;
	}

	if ( defined( 'AXA_GENERIC_IMAGES_KEY' ) && AXA_GENERIC_IMAGES_KEY ) {
		$key = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : '';
		return hash_equals( (string) AXA_GENERIC_IMAGES_KEY, $key );
	}

	return false;
}

/**
 * Resuelve la carpeta de imágenes genéricas.
 */
function axa_generic_images_source_dir() {
	$candidates = array(
		ABSPATH . 'ImagenesAXA',
		dirname( ABSPATH ) . '/ImagenesAXA',
	);

	foreach ( $candidates as $path ) {
		$real = realpath( $path );
		if ( $real && is_dir( $real ) ) {
			return $real;
		}
	}

	return '';
}

/**
 * Lista archivos de imagen en la carpeta fuente.
 *
 * @return string[]
 */
function axa_generic_images_pool( $source_dir ) {
	$extensions = array( 'jpg', 'jpeg', 'png', 'webp', 'gif' );
	$files      = array();

	foreach ( scandir( $source_dir ) as $entry ) {
		if ( '.' === $entry || '..' === $entry ) {
			continue;
		}

		if ( preg_match( '/-\d+x\d+\.(jpe?g|png|webp|gif)$/i', $entry ) ) {
			continue;
		}

		$path = $source_dir . DIRECTORY_SEPARATOR . $entry;
		if ( ! is_file( $path ) ) {
			continue;
		}

		$ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
		if ( in_array( $ext, $extensions, true ) ) {
			$files[] = $path;
		}
	}

	return $files;
}

/**
 * Indica si el adjunto de imagen destacada no existe en uploads.
 */
function axa_generic_thumbnail_file_missing( $attachment_id ) {
	$attachment_id = (int) $attachment_id;
	if ( $attachment_id <= 0 ) {
		return false;
	}

	$attachment = get_post( $attachment_id );
	if ( ! $attachment || 'attachment' !== $attachment->post_type ) {
		return true;
	}

	$file = get_attached_file( $attachment_id );
	if ( empty( $file ) ) {
		return true;
	}

	return ! file_exists( $file );
}

/**
 * Genera el nombre de archivo con sufijo de asignación automática.
 */
function axa_generic_images_build_filename( $source_file ) {
	$basename = basename( $source_file );
	$name     = pathinfo( $basename, PATHINFO_FILENAME );
	$ext      = pathinfo( $basename, PATHINFO_EXTENSION );
	$filename = $name . AXA_GENERIC_IMAGES_SUFFIX;

	if ( $ext ) {
		$filename .= '.' . $ext;
	}

	return wp_unique_filename( wp_upload_dir()['path'], $filename );
}

/**
 * Importa una imagen al media library y la asigna como destacada.
 *
 * @return array{ok:bool, attachment_id?:int, message:string}
 */
function axa_generic_assign_random_image( $post_id, $source_file, $dry_run ) {
	$post_id = (int) $post_id;

	if ( $dry_run ) {
		$filename = axa_generic_images_build_filename( $source_file );

		return array(
			'ok'      => true,
			'message' => sprintf(
				'[DRY RUN] Se asignaría "%s" a la nota #%d',
				$filename,
				$post_id
			),
		);
	}

	$filename = axa_generic_images_build_filename( $source_file );
	$contents = file_get_contents( $source_file );
	$upload   = wp_upload_bits( $filename, null, $contents );

	if ( ! empty( $upload['error'] ) ) {
		return array(
			'ok'      => false,
			'message' => $upload['error'],
		);
	}

	$filetype = wp_check_filetype( $filename, null );
	$title    = sanitize_file_name( pathinfo( $filename, PATHINFO_FILENAME ) );

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => $filetype['type'],
			'post_title'     => $title,
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$upload['file'],
		$post_id
	);

	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return array(
			'ok'      => false,
			'message' => is_wp_error( $attachment_id ) ? $attachment_id->get_error_message() : 'No se pudo crear el adjunto.',
		);
	}

	$metadata = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
	wp_update_attachment_metadata( $attachment_id, $metadata );
	set_post_thumbnail( $post_id, $attachment_id );

	return array(
		'ok'            => true,
		'attachment_id' => (int) $attachment_id,
		'message'       => sprintf(
			'Asignada "%s" (adjunto #%d)',
			$filename,
			$attachment_id
		),
	);
}

if ( ! axa_generic_images_can_run() ) {
	status_header( 403 );
	wp_die(
		esc_html__( 'No autorizado. Inicia sesión como administrador o usa ?key= con AXA_GENERIC_IMAGES_KEY en wp-config.php.', 'default' ),
		esc_html__( 'Forbidden', 'default' ),
		array( 'response' => 403 )
	);
}

$dry_run    = isset( $_GET['dry'] ) && '1' === (string) $_GET['dry'];
$source_dir = axa_generic_images_source_dir();
$pool       = $source_dir ? axa_generic_images_pool( $source_dir ) : array();

header( 'Content-Type: text/html; charset=utf-8' );

echo '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><title>Imágenes genéricas AXA</title>';
echo '<style>body{font-family:Montserrat,sans-serif;max-width:900px;margin:40px auto;padding:0 20px;color:#111}';
echo 'table{border-collapse:collapse;width:100%;margin-top:20px}th,td{border:1px solid #ddd;padding:8px;text-align:left}';
echo 'th{background:#00008F;color:#fff}.ok{color:#00008F}.skip{color:#666}.err{color:#c00}';
echo 'code{background:#f5f5f5;padding:2px 6px;border-radius:4px}</style></head><body>';

echo '<h1>Asignar imágenes genéricas a notas (AXA)</h1>';
echo '<p><strong>Modo:</strong> ' . ( $dry_run ? 'simulación (dry run)' : 'ejecución real' ) . '</p>';

if ( ! $source_dir || empty( $pool ) ) {
	echo '<p class="err">No se encontraron imágenes en <code>ImagenesAXA</code>.</p>';
	echo '</body></html>';
	exit;
}

echo '<p>Origen: <code>' . esc_html( $source_dir ) . '</code> (' . count( $pool ) . ' imágenes)</p>';

$post_ids = get_posts(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => array(
			array(
				'key'     => '_thumbnail_id',
				'compare' => 'EXISTS',
			),
		),
	)
);

$processed = 0;
$fixed     = 0;
$skipped   = 0;
$errors    = 0;
$rows      = array();

foreach ( $post_ids as $post_id ) {
	$thumb_id = (int) get_post_thumbnail_id( $post_id );
	if ( $thumb_id <= 0 ) {
		continue;
	}

	if ( ! axa_generic_thumbnail_file_missing( $thumb_id ) ) {
		++$skipped;
		continue;
	}

	++$processed;
	$random_file = $pool[ array_rand( $pool ) ];
	$result      = axa_generic_assign_random_image( $post_id, $random_file, $dry_run );

	$status_class = $result['ok'] ? 'ok' : 'err';
	if ( $result['ok'] ) {
		++$fixed;
	} else {
		++$errors;
	}

	$rows[] = array(
		'post_id'  => $post_id,
		'title'    => get_the_title( $post_id ),
		'old_id'   => $thumb_id,
		'old_file' => get_attached_file( $thumb_id ) ?: '(sin ruta)',
		'message'  => $result['message'],
		'class'    => $status_class,
	);
}

echo '<ul>';
echo '<li>Notas con imagen destacada revisadas: ' . count( $post_ids ) . '</li>';
echo '<li>Con archivo faltante en uploads: ' . $processed . '</li>';
echo '<li>Corregidas: ' . $fixed . '</li>';
echo '<li>Omitidas (archivo OK): ' . $skipped . '</li>';
echo '<li>Errores: ' . $errors . '</li>';
echo '</ul>';

if ( $rows ) {
	echo '<table><thead><tr><th>Nota</th><th>Título</th><th>Adjunto anterior</th><th>Resultado</th></tr></thead><tbody>';
	foreach ( $rows as $row ) {
		echo '<tr>';
		echo '<td>#' . esc_html( (string) $row['post_id'] ) . '</td>';
		echo '<td>' . esc_html( $row['title'] ) . '</td>';
		echo '<td>#' . esc_html( (string) $row['old_id'] ) . '<br><small>' . esc_html( $row['old_file'] ) . '</small></td>';
		echo '<td class="' . esc_attr( $row['class'] ) . '">' . esc_html( $row['message'] ) . '</td>';
		echo '</tr>';
	}
	echo '</tbody></table>';
} else {
	echo '<p class="skip">No hay notas con imagen destacada rota.</p>';
}

if ( $dry_run ) {
	echo '<p><a href="?">Ejecutar de verdad</a> (requiere la misma autorización)</p>';
} else {
	echo '<p><a href="?dry=1">Simular sin cambios</a></p>';
}

echo '</body></html>';
