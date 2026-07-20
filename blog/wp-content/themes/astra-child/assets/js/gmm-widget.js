(function () {
	'use strict';

	var config = window.astraChildGmmWidget || {};
	var api = config.api || {};
	var i18n = config.i18n || {};
	var requestCount = 0;
	var form = document.getElementById('formGMM');

	if (!form) {
		return;
	}

	sessionStorage.setItem('intentos', '2');

	var requiredFields = [
		'gmm1',
		'dia1',
		'mes1',
		'anio1',
		'nombres1',
		'apellidos1',
		'cp1',
		'email1',
		'genero1',
		'phone1',
	];

	function parametroURL(name) {
		if (!window.location.search) {
			return '';
		}
		return new URLSearchParams(window.location.search).get(name) || '';
	}

	function ensureHiddenTrackingFields() {
		['utmc', 'gclid'].forEach(function (id) {
			if (document.getElementById(id)) {
				return;
			}

			var input = document.createElement('input');
			input.type = 'hidden';
			input.id = id;
			input.name = id;
			input.value = '';
			form.appendChild(input);
		});
	}

	function setTrackingFromQuery() {
		var utm = document.getElementById('utmc');
		var gclid = document.getElementById('gclid');

		if (utm) {
			utm.value = parametroURL('utm_campaign') || parametroURL('utm') || '';
		}
		if (gclid) {
			gclid.value = parametroURL('gclid') || '';
		}
	}

	function getField(fieldId) {
		return document.getElementById(fieldId);
	}

	function markInvalid(field) {
		if (field) {
			field.classList.add('is-invalid');
		}
	}

	function markValid(field) {
		if (field) {
			field.classList.remove('is-invalid');
		}
	}

	function isEmpty(value) {
		return value === null || value === undefined || String(value).trim() === '';
	}

	function validateSelect(fieldId) {
		var field = getField(fieldId);
		if (!field) {
			return true;
		}

		if (isEmpty(field.value)) {
			markInvalid(field);
			return false;
		}

		markValid(field);
		return true;
	}

	function validateRequiredInput(fieldId) {
		var field = getField(fieldId);
		if (!field) {
			return true;
		}

		if (isEmpty(field.value)) {
			markInvalid(field);
			return false;
		}

		markValid(field);
		return true;
	}

	function validateExactLength(fieldId, length) {
		var field = getField(fieldId);
		if (!field) {
			return true;
		}

		if (field.value.length !== length) {
			markInvalid(field);
			return false;
		}

		markValid(field);
		return true;
	}

	function validateMail(fieldId) {
		var field = getField(fieldId);
		if (!field) {
			return true;
		}

		if (/([A-Z0-9a-z_-][^@])+?@[^$#<>?]+?\.[\w]{2,4}/.test(field.value)) {
			markValid(field);
			return true;
		}

		markInvalid(field);
		return false;
	}

	function validarNumeroCelular(numero) {
		return /^\d{10}$/.test(numero) && !/(\d)\1{3,}/.test(numero);
	}

	function celularOptimo(numero) {
		if (numero.length !== 10) {
			return false;
		}

		for (var i = 0; i < numero.length - 3; i++) {
			var digit = numero.charAt(i);
			if (
				numero.charAt(i + 1) === digit &&
				numero.charAt(i + 2) === digit &&
				numero.charAt(i + 3) === digit
			) {
				return false;
			}
		}

		return true;
	}

	function validarDatoNombre(value, fieldId) {
		var field = getField(fieldId);
		if (!field) {
			return true;
		}

		if (value.length < 3) {
			markInvalid(field);
			return false;
		}

		for (var i = 0; i < value.length - 2; i++) {
			var letter = value.charAt(i);
			if (value.charAt(i + 1) === letter && value.charAt(i + 2) === letter) {
				markInvalid(field);
				return false;
			}
		}

		markValid(field);
		return true;
	}

	function mapGeneroForApi(genero) {
		if (genero === 'Masculino') {
			return '0';
		}
		if (genero === 'Femenino') {
			return '1';
		}
		return genero;
	}

	function generoLabel(genero) {
		if (genero === 'Masculino' || genero === '0') {
			return 'Masculino';
		}
		if (genero === 'Femenino' || genero === '1') {
			return 'Femenino';
		}
		return genero;
	}

	function validatePhoneNumber(numero) {
		if (numero.length < 10) {
			return Promise.resolve(false);
		}
		if (/(\d)\1{3,}/.test(numero)) {
			return Promise.resolve(false);
		}
		if (requestCount >= 2) {
			return Promise.resolve(true);
		}
		if (!validarNumeroCelular(numero)) {
			requestCount++;
			return Promise.resolve(false);
		}

		return fetch(api.phone_validate_api, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				Authorization: 'Bearer ' + (api.phone_validate_token || ''),
			},
			body: JSON.stringify({ phone: '+52' + numero }),
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (result) {
				requestCount++;
				return Boolean(result && result.Valid);
			})
			.catch(function () {
				requestCount++;
				return true;
			});
	}

	function validateAllFields() {
		var allValid = requiredFields.every(function (fieldId) {
			var field = getField(fieldId);
			if (!field) {
				return true;
			}
			if (field.tagName === 'SELECT') {
				return validateSelect(fieldId);
			}
			return validateRequiredInput(fieldId);
		});

		allValid = validateExactLength('cp1', 5) && allValid;
		allValid = validateExactLength('phone1', 10) && allValid;
		allValid = validateMail('email1') && allValid;

		var nombre = getField('nombres1') ? getField('nombres1').value : '';
		var apellido = getField('apellidos1') ? getField('apellidos1').value : '';
		var phone = getField('phone1') ? getField('phone1').value : '';

		allValid = validarDatoNombre(nombre, 'nombres1') && allValid;
		allValid = validarDatoNombre(apellido, 'apellidos1') && allValid;
		allValid = celularOptimo(phone) && allValid;

		return allValid;
	}

	function obtenerToken() {
		return fetch(api.token_api, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify({
				API_USUARIO: {
					USUARIO: api.token_user || '',
					CONTRASENIA: api.token_pass || '',
				},
			}),
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (result) {
				if (result && result.Token) {
					localStorage.setItem('tokenWS', result.Token);
					return result;
				}
				throw new Error('Token unavailable');
			});
	}

	function loader(status) {
		var overlay = document.querySelector('.blog-quote-loader-overlay');

		if (status) {
			if (overlay) {
				return;
			}
			overlay = document.createElement('div');
			overlay.className = 'blog-quote-loader-overlay';
			overlay.innerHTML = '<div class="blog-quote-loader"><div class="blog-quote-spinner"></div></div>';
			document.body.appendChild(overlay);
			return;
		}

		if (overlay) {
			overlay.remove();
		}
	}

	function prospectWasCreated(result) {
		if (!result) {
			return false;
		}

		if (result.data && result.data[0] && result.data[0].code === 'SUCCESS') {
			return true;
		}

		if (result.success === true || result.Success === true) {
			return true;
		}

		return false;
	}

	function handleSubmit(suffix) {
		var nombre = getField('nombres' + suffix).value.trim();
		var apellido = getField('apellidos' + suffix).value.trim();
		var cp = getField('cp' + suffix).value.trim();
		var genero = getField('genero' + suffix).value.trim();
		var dia = getField('dia' + suffix).value.trim();
		var anio = getField('anio' + suffix).value.trim();
		var email = getField('email' + suffix).value.trim();
		var phone = getField('phone' + suffix).value.trim();
		var gmm = getField('gmm' + suffix).value.trim();
		var mes = getField('mes' + suffix).value.trim();
		var utmc = getField('utmc') ? getField('utmc').value : '';
		var gclid = getField('gclid') ? getField('gclid').value : '';
		var generoApi = mapGeneroForApi(genero);

		return obtenerToken()
			.then(function (tokenResult) {
				return fetch(api.prospect_api, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						Authorization: 'Bearer ' + tokenResult.Token,
					},
					body: JSON.stringify({
						ProspectoZoho: {
							email: email,
							mkT_Campaigns: utmc || '',
							ramo: config.ramo || 'ACCIDENTES Y ENFERMEDADES',
							zip_Code: cp,
							firstPage: config.firstPage || window.location.href,
							description:
								'El cliente cotizo un seguro con los siguientes datos CP: ' +
								cp +
								' Genero: ' +
								generoLabel(genero) +
								' Fecha de Nacimiento: ' +
								dia +
								'/' +
								mes +
								'/' +
								anio +
								', cuenta con un seguro de GMM: ' +
								gmm,
							first_Name: nombre,
							full_Name: nombre + ' ' + apellido,
							phone: '+52' + phone,
							genero: generoApi,
							mobile: '+52' + phone,
							Last_Name: apellido,
							lead_Source: config.leadSource || 'Blog GNP',
							aseguradora_Campana: '',
							Fecha_de_Nacimiento: anio + '-' + mes + '-' + dia,
							GCLID: gclid || '',
						},
					}),
				});
			})
			.then(function (response) {
				return response.json().then(function (result) {
					return {
						ok: response.ok,
						result: result,
					};
				});
			})
			.then(function (payload) {
				if (prospectWasCreated(payload.result)) {
					window.alert(
						i18n.success ||
							'Hemos recibido tus datos y nos pondremos en contacto para darte atención personalizada'
					);
					if (
						payload.result.data &&
						payload.result.data[0] &&
						payload.result.data[0].details &&
						payload.result.data[0].details.id
					) {
						localStorage.setItem('leadidcpy', payload.result.data[0].details.id);
					}
					form.reset();
					setTrackingFromQuery();
					return;
				}

				throw new Error('Prospect creation failed');
			});
	}

	function bindNumericFields() {
		form.querySelectorAll('.soloNumeros').forEach(function (field) {
			field.addEventListener('input', function () {
				this.value = this.value.replace(/[^0-9]/g, '');
			});
		});
	}

	function bindLetterFields() {
		form.querySelectorAll('.soloLetras').forEach(function (field) {
			field.addEventListener('keypress', permitirSoloLetras);
			field.addEventListener('keyup', function () {
				mayus(field);
			});
			field.addEventListener('blur', function () {
				mayus(field);
			});
		});
	}

	function permitirSoloLetras(event) {
		if (!/^[A-Za-z\s]$/.test(event.key)) {
			event.preventDefault();
		}
	}

	function mayus(field) {
		if (field) {
			field.value = field.value.toUpperCase();
		}
	}

	window.permitirSoloLetras = permitirSoloLetras;
	window.mayus = function (field) {
		mayus(field);
	};

	form.addEventListener('submit', function (event) {
		event.preventDefault();

		if (!validateAllFields()) {
			window.alert(i18n.incompleteData || 'Falta ingresar información');
			return;
		}

		var phone = getField('phone1').value.trim();

		validatePhoneNumber(phone).then(function (phoneValid) {
			if (!phoneValid) {
				markInvalid(getField('phone1'));
				window.alert(i18n.invalidPhone || 'Número no válido');
				return;
			}

			loader(true);
			handleSubmit('1')
				.catch(function () {
					window.alert(
						i18n.submitError ||
							'No pudimos enviar tu solicitud. Intenta de nuevo en unos momentos.'
					);
				})
				.finally(function () {
					loader(false);
				});
		});
	});

	if (localStorage.getItem('leadidcpy')) {
		localStorage.removeItem('leadidcpy');
	}

	ensureHiddenTrackingFields();
	setTrackingFromQuery();
	bindNumericFields();
	bindLetterFields();
	obtenerToken().catch(function () {
		// El token se obtiene de nuevo al enviar el formulario.
	});
})();
