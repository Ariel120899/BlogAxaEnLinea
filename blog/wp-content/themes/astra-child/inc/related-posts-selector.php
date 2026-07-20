<?php
/**
 * Selector de notas para "También podría interesarte".
 * Meta box en el editor con búsqueda por título.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

define( 'SI_RELATED_POSTS_META_KEY', '_si_notas_relacionadas' );
define( 'SI_RELATED_POSTS_MAX', 6 );

/**
 * Registrar meta box.
 */
function si_related_posts_add_meta_box() {
	add_meta_box(
		'si_related_posts',
		'También podría interesarte',
		'si_related_posts_meta_box_callback',
		'post',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'si_related_posts_add_meta_box' );

/**
 * Render del meta box.
 *
 * @param WP_Post $post Post actual.
 */
function si_related_posts_meta_box_callback( $post ) {
	wp_nonce_field( 'si_related_posts_save', 'si_related_posts_nonce' );

	$selected_ids = get_post_meta( $post->ID, SI_RELATED_POSTS_META_KEY, true );
	if ( ! is_array( $selected_ids ) ) {
		$selected_ids = array();
	}
	$selected_ids = array_map( 'absint', $selected_ids );
	$selected_ids = array_filter( $selected_ids );

	$selected_posts = array();
	if ( ! empty( $selected_ids ) ) {
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => SI_RELATED_POSTS_MAX,
				'post__in'       => $selected_ids,
				'orderby'        => 'post__in',
			)
		);
		foreach ( $posts as $p ) {
			$selected_posts[] = array(
				'id'    => $p->ID,
				'title' => $p->post_title,
			);
		}
	}
	?>
	<div id="si-related-posts-box" class="si-related-posts-box">
		<p class="description">
			Busca y selecciona hasta <?php echo (int) SI_RELATED_POSTS_MAX; ?> notas.
			Si no asignas ninguna, se mostrarán notas de la misma categoría (comportamiento actual).
		</p>

		<div class="si-related-search-wrap">
			<label for="si-related-search" class="screen-reader-text">Buscar notas</label>
			<input
				type="text"
				id="si-related-search"
				class="widefat"
				placeholder="Escribe el nombre de la nota..."
				autocomplete="off"
			/>
			<ul id="si-related-suggestions" class="si-related-suggestions" hidden></ul>
		</div>

		<ul id="si-related-selected" class="si-related-selected">
			<?php foreach ( $selected_posts as $item ) : ?>
				<li data-id="<?php echo esc_attr( $item['id'] ); ?>">
					<span class="si-related-title"><?php echo esc_html( $item['title'] ); ?></span>
					<button type="button" class="button-link si-related-remove" aria-label="Quitar nota">&times;</button>
				</li>
			<?php endforeach; ?>
		</ul>

		<input
			type="hidden"
			name="si_related_posts"
			id="si-related-posts-input"
			value="<?php echo esc_attr( implode( ',', $selected_ids ) ); ?>"
		/>
	</div>
	<?php
}

/**
 * Guardar selección.
 *
 * @param int $post_id ID del post.
 */
function si_related_posts_save_meta( $post_id ) {
	if ( ! isset( $_POST['si_related_posts_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['si_related_posts_nonce'] ) ), 'si_related_posts_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( ! isset( $_POST['si_related_posts'] ) ) {
		delete_post_meta( $post_id, SI_RELATED_POSTS_META_KEY );
		return;
	}

	$raw = sanitize_text_field( wp_unslash( $_POST['si_related_posts'] ) );
	$ids = array_filter( array_map( 'absint', explode( ',', $raw ) ) );
	$ids = array_values( array_unique( $ids ) );
	$ids = array_slice( $ids, 0, SI_RELATED_POSTS_MAX );

	// Excluir el post actual.
	$ids = array_values( array_diff( $ids, array( (int) $post_id ) ) );

	if ( empty( $ids ) ) {
		delete_post_meta( $post_id, SI_RELATED_POSTS_META_KEY );
	} else {
		update_post_meta( $post_id, SI_RELATED_POSTS_META_KEY, $ids );
	}
}
add_action( 'save_post_post', 'si_related_posts_save_meta' );

/**
 * AJAX: buscar posts por título.
 */
function si_related_posts_ajax_search() {
	check_ajax_referer( 'si_related_posts_search', 'nonce' );

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( array( 'message' => 'Sin permiso' ), 403 );
	}

	$term        = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';
	$exclude     = isset( $_GET['exclude'] ) ? absint( $_GET['exclude'] ) : 0;
	$exclude_ids = array();

	if ( ! empty( $_GET['selected'] ) ) {
		$exclude_ids = array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_GET['selected'] ) ) ) ) );
	}

	if ( $exclude ) {
		$exclude_ids[] = $exclude;
	}

	$exclude_ids = array_unique( $exclude_ids );

	if ( strlen( $term ) < 2 ) {
		wp_send_json_success( array() );
	}

	$query = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			's'                   => $term,
			'search_columns'      => array( 'post_title' ),
			'posts_per_page'      => 10,
			'post__not_in'        => $exclude_ids,
			'orderby'             => 'relevance',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);

	$results = array();
	foreach ( $query->posts as $p ) {
		$results[] = array(
			'id'    => $p->ID,
			'title' => $p->post_title,
		);
	}

	wp_send_json_success( $results );
}
add_action( 'wp_ajax_si_related_posts_search', 'si_related_posts_ajax_search' );

/**
 * Encolar assets del meta box solo en el editor de posts.
 *
 * @param string $hook_suffix Hook actual del admin.
 */
function si_related_posts_admin_assets( $hook_suffix ) {
	if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'post' !== $screen->post_type ) {
		return;
	}

	$css = '
		.si-related-posts-box { position: relative; }
		.si-related-search-wrap { position: relative; margin-bottom: 12px; }
		.si-related-suggestions {
			position: absolute; left: 0; right: 0; top: 100%; z-index: 20;
			margin: 0; padding: 0; list-style: none;
			background: #fff; border: 1px solid #c3c4c7; border-top: 0;
			max-height: 220px; overflow-y: auto; box-shadow: 0 2px 6px rgba(0,0,0,.08);
		}
		.si-related-suggestions li {
			margin: 0; padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f1;
		}
		.si-related-suggestions li:hover,
		.si-related-suggestions li.is-active { background: #f0f6fc; }
		.si-related-suggestions li:last-child { border-bottom: 0; }
		.si-related-selected { margin: 0; padding: 0; list-style: none; }
		.si-related-selected li {
			display: flex; align-items: center; justify-content: space-between;
			gap: 8px; margin: 0 0 6px; padding: 8px 10px;
			background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 4px;
		}
		.si-related-selected .si-related-title { flex: 1; }
		.si-related-remove {
			color: #b32d2e !important; font-size: 18px; line-height: 1;
			text-decoration: none !important; padding: 0 4px;
		}
		.si-related-empty { color: #646970; font-style: italic; margin: 0; }
	';
	wp_register_style( 'si-related-posts-admin', false );
	wp_enqueue_style( 'si-related-posts-admin' );
	wp_add_inline_style( 'si-related-posts-admin', $css );

	$js = <<<'JS'
(function ($) {
	var $box = $('#si-related-posts-box');
	if (!$box.length) return;

	var maxItems = %MAX%;
	var ajaxUrl = %AJAX_URL%;
	var nonce = %NONCE%;
	var excludeId = %EXCLUDE%;
	var $input = $('#si-related-search');
	var $suggestions = $('#si-related-suggestions');
	var $selected = $('#si-related-selected');
	var $hidden = $('#si-related-posts-input');
	var debounceTimer = null;
	var activeIndex = -1;

	function getSelectedIds() {
		var val = $hidden.val();
		if (!val) return [];
		return val.split(',').map(function (id) { return parseInt(id, 10); }).filter(Boolean);
	}

	function setSelectedIds(ids) {
		$hidden.val(ids.join(','));
	}

	function renderEmptyHint() {
		if ($selected.children('li').length === 0 && !$selected.find('.si-related-empty').length) {
			$selected.append('<p class="si-related-empty">Ninguna nota seleccionada. Se usará el listado por defecto.</p>');
		}
	}

	function clearEmptyHint() {
		$selected.find('.si-related-empty').remove();
	}

	function addItem(id, title) {
		var ids = getSelectedIds();
		if (ids.indexOf(id) !== -1) return;
		if (ids.length >= maxItems) {
			window.alert('Solo puedes seleccionar hasta ' + maxItems + ' notas.');
			return;
		}
		clearEmptyHint();
		ids.push(id);
		setSelectedIds(ids);
		$selected.append(
			'<li data-id="' + id + '">' +
				'<span class="si-related-title"></span>' +
				'<button type="button" class="button-link si-related-remove" aria-label="Quitar nota">&times;</button>' +
			'</li>'
		);
		$selected.children('li').last().find('.si-related-title').text(title);
		$input.val('');
		hideSuggestions();
	}

	function hideSuggestions() {
		$suggestions.attr('hidden', true).empty();
		activeIndex = -1;
	}

	function showSuggestions(items) {
		$suggestions.empty();
		if (!items.length) {
			$suggestions.append('<li class="si-related-no-results">Sin resultados</li>');
			$suggestions.removeAttr('hidden');
			return;
		}
		items.forEach(function (item) {
			var $li = $('<li></li>').attr('data-id', item.id).text(item.title);
			$suggestions.append($li);
		});
		$suggestions.removeAttr('hidden');
		activeIndex = -1;
	}

	function search(term) {
		if (term.length < 2) {
			hideSuggestions();
			return;
		}
		$.getJSON(ajaxUrl, {
			action: 'si_related_posts_search',
			nonce: nonce,
			term: term,
			exclude: excludeId,
			selected: getSelectedIds().join(',')
		}).done(function (response) {
			if (response && response.success) {
				showSuggestions(response.data || []);
			}
		});
	}

	$input.on('input', function () {
		var term = $.trim($input.val());
		clearTimeout(debounceTimer);
		debounceTimer = setTimeout(function () { search(term); }, 250);
	});

	$input.on('keydown', function (e) {
		var $items = $suggestions.children('li[data-id]');
		if (!$items.length || $suggestions.is('[hidden]')) return;

		if (e.key === 'ArrowDown') {
			e.preventDefault();
			activeIndex = Math.min(activeIndex + 1, $items.length - 1);
			$items.removeClass('is-active').eq(activeIndex).addClass('is-active');
		} else if (e.key === 'ArrowUp') {
			e.preventDefault();
			activeIndex = Math.max(activeIndex - 1, 0);
			$items.removeClass('is-active').eq(activeIndex).addClass('is-active');
		} else if (e.key === 'Enter') {
			e.preventDefault();
			var $active = $items.filter('.is-active');
			if (!$active.length) $active = $items.first();
			addItem(parseInt($active.attr('data-id'), 10), $active.text());
		} else if (e.key === 'Escape') {
			hideSuggestions();
		}
	});

	$suggestions.on('mousedown', 'li[data-id]', function (e) {
		e.preventDefault();
		addItem(parseInt($(this).attr('data-id'), 10), $(this).text());
	});

	$selected.on('click', '.si-related-remove', function () {
		var $li = $(this).closest('li');
		var id = parseInt($li.attr('data-id'), 10);
		var ids = getSelectedIds().filter(function (x) { return x !== id; });
		setSelectedIds(ids);
		$li.remove();
		renderEmptyHint();
	});

	$(document).on('click', function (e) {
		if (!$(e.target).closest('.si-related-search-wrap').length) {
			hideSuggestions();
		}
	});

	renderEmptyHint();
})(jQuery);
JS;

	$js = str_replace(
		array( '%MAX%', '%AJAX_URL%', '%NONCE%', '%EXCLUDE%' ),
		array(
			(string) SI_RELATED_POSTS_MAX,
			wp_json_encode( admin_url( 'admin-ajax.php' ) ),
			wp_json_encode( wp_create_nonce( 'si_related_posts_search' ) ),
			(string) (int) get_the_ID(),
		),
		$js
	);

	wp_register_script( 'si-related-posts-admin', false, array( 'jquery' ), false, true );
	wp_enqueue_script( 'si-related-posts-admin' );
	wp_add_inline_script( 'si-related-posts-admin', $js );
}
add_action( 'admin_enqueue_scripts', 'si_related_posts_admin_assets' );

/**
 * Obtener IDs de notas relacionadas configuradas.
 *
 * @param int $post_id ID del post.
 * @return int[]
 */
function si_get_related_post_ids( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$ids     = get_post_meta( $post_id, SI_RELATED_POSTS_META_KEY, true );

	if ( ! is_array( $ids ) || empty( $ids ) ) {
		return array();
	}

	$ids = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
	$ids = array_diff( $ids, array( $post_id ) );

	return array_slice( $ids, 0, SI_RELATED_POSTS_MAX );
}
