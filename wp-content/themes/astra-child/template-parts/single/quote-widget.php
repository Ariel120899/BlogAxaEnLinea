<?php
/**
 * Widget de cotización del sidebar en notas.
 *
 * @package Astra_Child
 */

defined( 'ABSPATH' ) || exit;

$config        = astra_child_get_quote_widget_config();
$placeholders  = $config['placeholders'] ?? array();
$placeholder   = function ( $key, $default = '' ) use ( $placeholders ) {
	return $placeholders[ $key ] ?? $default;
};
?>
<div id="DivAuto" class="blog-quote-widget">
	<form id="FormAuto" class="blog-quote-form" action="<?php echo esc_url( $config['form_action'] ?? '' ); ?>" method="post" novalidate>
		<h1 class="blog-quote-form__title"><?php echo esc_html( $config['title'] ); ?></h1>

		<p class="blog-quote-form__section-title"><?php esc_html_e( 'Datos de tu auto', 'astra-child' ); ?></p>
		<div class="blog-quote-form__grid blog-quote-form__grid-auto">
			<div>
				<select name="marca" id="slc-marcas" required>
					<option value=""><?php echo esc_html( $placeholder( 'marca', __( 'Marca', 'astra-child' ) ) ); ?></option>
				</select>
				<div id="error-marca" class="blog-quote-form__error" aria-live="polite"></div>
			</div>
			<div>
				<select name="anio" id="slc-anio" required disabled>
					<option value=""><?php echo esc_html( $placeholder( 'modelo', __( 'Modelo', 'astra-child' ) ) ); ?></option>
				</select>
				<div id="error-anio" class="blog-quote-form__error" aria-live="polite"></div>
			</div>
			<div class="blog-quote-form__field-half">
				<select name="descripcion" id="slc-descripcion" required disabled>
					<option value=""><?php echo esc_html( $placeholder( 'submarca', __( 'Submarca', 'astra-child' ) ) ); ?></option>
				</select>
				<div id="error-descripcion" class="blog-quote-form__error" aria-live="polite"></div>
			</div>
			<input type="hidden" id="utmc" name="utm" value="">
		</div>

		<p class="blog-quote-form__section-title"><?php esc_html_e( 'Datos del conductor', 'astra-child' ); ?></p>
		<div class="blog-quote-form__grid">
			<div>
				<select name="FNacimiento" id="FNacimiento" required>
					<option value=""><?php echo esc_html( $placeholder( 'edad', __( 'Edad', 'astra-child' ) ) ); ?></option>
				</select>
				<div id="error-FNacimiento" class="blog-quote-form__error" aria-live="polite"></div>
			</div>
			<div>
				<select name="genero" id="genero" required>
					<option value=""><?php echo esc_html( $placeholder( 'genero', __( 'Género', 'astra-child' ) ) ); ?></option>
					<option value="1"><?php esc_html_e( 'Femenino', 'astra-child' ); ?></option>
					<option value="0"><?php esc_html_e( 'Masculino', 'astra-child' ); ?></option>
				</select>
				<div id="error-genero" class="blog-quote-form__error" aria-live="polite"></div>
			</div>
			<div>
				<input type="text" id="cepe" name="cepe" placeholder="<?php echo esc_attr( $placeholder( 'cp', __( 'Código postal', 'astra-child' ) ) ); ?>" class="blog-quote-form__numbers" maxlength="5" inputmode="numeric" required>
				<div id="error-cepe" class="blog-quote-form__error" aria-live="polite"></div>
			</div>
			<div>
				<input type="text" placeholder="<?php echo esc_attr( $placeholder( 'nombre', __( 'Nombre', 'astra-child' ) ) ); ?>" name="nombre" id="nombre" class="blog-quote-form__letters" required>
				<div id="error-nombre" class="blog-quote-form__error" aria-live="polite"></div>
			</div>
			<div>
				<input type="text" placeholder="<?php echo esc_attr( $placeholder( 'apellido', __( 'Apellido', 'astra-child' ) ) ); ?>" name="apellido" id="apellido" class="blog-quote-form__letters" required>
				<div id="error-apellido" class="blog-quote-form__error" aria-live="polite"></div>
			</div>
			<div>
				<input type="email" placeholder="<?php echo esc_attr( $placeholder( 'email', __( 'Email', 'astra-child' ) ) ); ?>" name="mail" id="mail" required>
				<div id="error-mail" class="blog-quote-form__error" aria-live="polite"></div>
			</div>
			<div class="blog-quote-form__field-half">
				<input type="text" placeholder="<?php echo esc_attr( $placeholder( 'celular', __( 'Número', 'astra-child' ) ) ); ?>" name="celular" id="celular" class="blog-quote-form__numbers" maxlength="10" inputmode="numeric" required>
				<div id="error-celular" class="blog-quote-form__error" aria-live="polite"></div>
			</div>
		</div>

		<div class="blog-quote-form__terms">
			<input type="checkbox" checked id="Check" name="privacidad" value="1">
			<p><?php echo esc_html( $config['privacy_text'] ); ?></p>
		</div>

		<div class="blog-quote-form__actions">
			<button class="blog-quote-form__submit btn-auto" id="btnCotizar" type="submit">
				<?php echo esc_html( $config['submit_label'] ); ?>
			</button>
		</div>
	</form>
</div>
