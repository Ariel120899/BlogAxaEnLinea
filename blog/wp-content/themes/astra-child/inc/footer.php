<?php
/**
 * Icono de flecha del footer.
 */
function astra_child_footer_arrow_icon() {
	return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 216.5 250" fill="#fff" width="12" aria-hidden="true"><polygon points="216.5 125 0 0 0 250 216.5 125"/></svg>';
}

/**
 * Enlaces del footer.
 *
 * @return array<int, array{label: string, url: string}>
 */
function astra_child_get_footer_links() {
	return astra_child_get_brand_footer_links();
}

/**
 * URL del aviso de privacidad.
 */
function astra_child_get_privacy_policy_url() {
	$privacy_url = get_privacy_policy_url();

	if ( $privacy_url ) {
		return $privacy_url;
	}

	return apply_filters( 'astra_child_privacy_policy_url', '#' );
}
