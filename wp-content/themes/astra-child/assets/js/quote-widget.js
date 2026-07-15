(function () {
	'use strict';

	var config = window.astraChildQuoteWidget || {};
	var api = config.api || {};
	var i18n = config.i18n || {};
	var placeholders = config.placeholders || {};
	var requestCount = 0;

	var form = document.getElementById('FormAuto');
	if (!form) {
		return;
	}

	var brandSelect = document.getElementById('slc-marcas');
	var modelSelect = document.getElementById('slc-anio');
	var subbrandSelect = document.getElementById('slc-descripcion');
	var ageSelect = document.getElementById('FNacimiento');
	var utmInput = document.getElementById('utmc');

	function getErrorElement(field) {
		if (!field || !field.name) {
			return null;
		}
		return document.getElementById('error-' + field.name);
	}

	function validInput(field) {
		if (!field) {
			return true;
		}

		var errorEl = getErrorElement(field);
		if (field.value === '') {
			if (errorEl) {
				errorEl.textContent = i18n.required || '*Es necesario llenar este campo';
			}
			field.classList.add('is-invalid');
			return false;
		}

		if (errorEl) {
			errorEl.textContent = '';
		}
		field.classList.remove('is-invalid');
		return true;
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

	function fillSelect(select, placeholder, items, mapItem) {
		if (!select) {
			return;
		}

		select.innerHTML = '';
		var placeholderOption = document.createElement('option');
		placeholderOption.value = '';
		placeholderOption.textContent = placeholder;
		select.appendChild(placeholderOption);

		items.forEach(function (item) {
			var mapped = mapItem(item);
			var option = document.createElement('option');
			option.value = mapped.value;
			option.textContent = mapped.label;
			select.appendChild(option);
		});

		select.disabled = items.length === 0;
	}

	function populateBirthDates() {
		if (!ageSelect) {
			return;
		}

		var options = [];
		var today = new Date();

		for (var age = 19; age <= 80; age++) {
			var dd = String(today.getDate()).padStart(2, '0');
			var mm = String(today.getMonth() + 1).padStart(2, '0');
			var yyyy = today.getFullYear() - age;
			options.push({
				value: yyyy + '-' + mm + '-' + dd,
				label: String(age),
			});
		}

		fillSelect(ageSelect, placeholders.edad || 'Edad', options, function (item) {
			return item;
		});
	}

	function getCatalogToken(forceNew) {
		var storedToken = !forceNew ? localStorage.getItem('tokenMAG') : null;
		if (storedToken) {
			return Promise.resolve(storedToken);
		}

		return fetch(api.catalog_token_api, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({
				usuario: api.catalog_user || '',
				contrasena: api.catalog_pass || '',
			}),
		})
			.then(function (response) {
				if (!response.ok) {
					throw new Error('Catalog token request failed');
				}
				return response.json();
			})
			.then(function (result) {
				var token = result && (result.token || result.Token || result.access_token);
				if (!token) {
					throw new Error('Catalog token unavailable');
				}
				localStorage.setItem('tokenMAG', token);
				return token;
			});
	}

	function fetchCatalog(url, options, retry) {
		return getCatalogToken(false).then(function (token) {
			var requestOptions = Object.assign({}, options || {});
			requestOptions.headers = Object.assign({}, requestOptions.headers || {}, {
				Authorization: 'Bearer ' + token,
			});

			return fetch(url, requestOptions).then(function (response) {
				if (response.status === 401 && retry !== false) {
					localStorage.removeItem('tokenMAG');
					return getCatalogToken(true).then(function () {
						return fetchCatalog(url, options, false);
					});
				}
				return response;
			});
		});
	}

	function loadBrands() {
		if (!brandSelect || !api.brands_api || !api.catalog_token_api) {
			return;
		}

		fetchCatalog(api.brands_api, { method: 'GET' })
			.then(function (response) {
				if (!response.ok) {
					throw new Error('Brands request failed');
				}
				return response.json();
			})
			.then(function (data) {
				var brands = data && Array.isArray(data.response) ? data.response : [];
				fillSelect(
					brandSelect,
					placeholders.marca || 'Marca',
					brands,
					function (item) {
						var name = item.nombre || item.Marca || item.marca || '';
						return { value: name, label: name };
					}
				);
			})
			.catch(function () {
				fillSelect(brandSelect, placeholders.marca || 'Marca', [], function (item) {
					return item;
				});
			});
	}

	function loadModels() {
		if (!brandSelect || !modelSelect || !api.model_api) {
			return;
		}

		var marca = brandSelect.value;
		if (!marca) {
			fillSelect(modelSelect, placeholders.modelo || 'Modelo', [], function (item) {
				return item;
			});
			fillSelect(subbrandSelect, placeholders.submarca || 'Submarca', [], function (item) {
				return item;
			});
			return;
		}

		fetchCatalog(api.model_api, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({
				nombreMarca: marca,
				rango: api.model_range || '2005',
			}),
		})
			.then(function (response) {
				if (!response.ok) {
					throw new Error('Models request failed');
				}
				return response.json();
			})
			.then(function (data) {
				var models = data && data.response && Array.isArray(data.response.anio)
					? data.response.anio
					: [];
				fillSelect(modelSelect, placeholders.modelo || 'Modelo', models, function (item) {
					return { value: item, label: item };
				});
				fillSelect(subbrandSelect, placeholders.submarca || 'Submarca', [], function (item) {
					return item;
				});
				modelSelect.focus();
			})
			.catch(function () {
				fillSelect(modelSelect, placeholders.modelo || 'Modelo', [], function (item) {
					return item;
				});
			});
	}

	function loadSubbrands() {
		if (!brandSelect || !modelSelect || !subbrandSelect || !api.subbrand_api) {
			return;
		}

		var marca = brandSelect.value;
		var modelo = modelSelect.value;

		if (!marca || !modelo) {
			fillSelect(subbrandSelect, placeholders.submarca || 'Submarca', [], function (item) {
				return item;
			});
			return;
		}

		fetchCatalog(api.subbrand_api, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({
				nombreMarca: marca,
				anio: modelo,
			}),
		})
			.then(function (response) {
				if (!response.ok) {
					throw new Error('Subbrands request failed');
				}
				return response.json();
			})
			.then(function (data) {
				var subbrands = data && data.response && Array.isArray(data.response.subMarca)
					? data.response.subMarca
					: [];
				fillSelect(subbrandSelect, placeholders.submarca || 'Submarca', subbrands, function (item) {
					return { value: item, label: item };
				});
				subbrandSelect.focus();
			})
			.catch(function () {
				fillSelect(subbrandSelect, placeholders.submarca || 'Submarca', [], function (item) {
					return item;
				});
			});
	}

	function parametroURL(name) {
		if (!window.location.search) {
			return null;
		}

		var params = new URLSearchParams(window.location.search);
		return params.get(name);
	}

	function setUtmFromQuery() {
		if (!utmInput) {
			return;
		}
		utmInput.value = parametroURL('utm_campaign') || parametroURL('utm') || '';
	}

	function validarNumeroCelular(numero) {
		if (!/^\d{10}$/.test(numero)) {
			return false;
		}
		if (/(\d)\1{3,}/.test(numero)) {
			return false;
		}
		return true;
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
				return false;
			});
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

	function bindNumericFields() {
		form.querySelectorAll('.blog-quote-form__numbers').forEach(function (field) {
			field.addEventListener('input', function () {
				this.value = this.value.replace(/[^0-9]/g, '');
			});
		});
	}

	function bindLetterFields() {
		form.querySelectorAll('.blog-quote-form__letters').forEach(function (field) {
			field.addEventListener('keypress', permitirSoloLetras);
			field.addEventListener('blur', function () {
				validInput(field);
				mayus(field);
			});
			field.addEventListener('keyup', function () {
				mayus(field);
			});
		});
	}

	function bindValidation() {
		form.querySelectorAll('input, select').forEach(function (field) {
			field.addEventListener('blur', function () {
				validInput(field);
			});
		});
	}

	if (brandSelect) {
		brandSelect.addEventListener('change', function () {
			validInput(brandSelect);
			loadModels();
		});
	}

	if (modelSelect) {
		modelSelect.addEventListener('change', function () {
			validInput(modelSelect);
			loadSubbrands();
		});
	}

	form.addEventListener('submit', function (event) {
		event.preventDefault();

		var check = document.getElementById('Check');
		if (check && !check.checked) {
			window.alert(i18n.terms || 'Se tienen que aceptar los términos y condiciones para continuar');
			return;
		}

		var nombrePros = document.getElementById('nombre');
		var apellido = document.getElementById('apellido');
		var cepe = document.getElementById('cepe');
		var mail = document.getElementById('mail');
		var celular = document.getElementById('celular');
		var genero = document.getElementById('genero');

		var fields = [brandSelect, modelSelect, subbrandSelect, ageSelect, genero, cepe, nombrePros, apellido, mail, celular];
		var allValid = fields.every(validInput);

		if (!allValid || !validarNumeroCelular(celular.value)) {
			window.alert(i18n.invalidData || '¡Uno o más datos no son válidos!');
			return;
		}

		validatePhoneNumber(celular.value).then(function (phoneValid) {
			if (!phoneValid) {
				celular.classList.add('is-invalid');
				celular.value = '';
				celular.placeholder = i18n.invalidPhone || 'Dato no válido';
				window.alert(i18n.incompleteData || '¡Aún no ha completado la información correspondiente!');
				return;
			}

			var selectedAge = ageSelect.options[ageSelect.selectedIndex];
			var generoTexto = genero.value === '0' ? 'Masculino' : 'Femenino';

			loader(true);

			obtenerToken()
				.then(function (tokenResult) {
					return fetch(api.prospect_api, {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json',
							Authorization: 'Bearer ' + tokenResult.Token,
						},
						body: JSON.stringify({
							ProspectoZoho: {
								email: mail.value,
								mkT_Campaigns: parametroURL('utm_campaign') || '',
								ramo: 'AUTOMOVILES',
								zip_Code: cepe.value,
								firstPage: window.location.href,
								description:
									'El usuario selecciono un vehiculo con los siguientes datos: ' +
									brandSelect.value +
									' Modelo: ' +
									modelSelect.value +
									', sub marca: ' +
									subbrandSelect.value +
									', Edad: ' +
									selectedAge.text +
									' su codigo postal es: ' +
									cepe.value,
								first_Name: nombrePros.value,
								full_Name: nombrePros.value + ' ' + apellido.value,
								phone: '+52' + celular.value,
								genero: generoTexto,
								mobile: '+52' + celular.value,
								Last_Name: apellido.value,
								lead_Source: config.leadSource || 'Blog',
								aseguradora_Campana: 'COMPARADOR',
								Fecha_de_Nacimiento: ageSelect.value,
								Marca: brandSelect.value,
								Modelo: modelSelect.value,
								GCLID: '',
							},
						}),
					});
				})
				.then(function (response) {
					return response.json();
				})
				.then(function () {
					window.alert(i18n.success || 'Hemos recibido tus datos y nos pondremos en contacto para darte atención personalizada');
					form.reset();
					populateBirthDates();
					loadBrands();
					fillSelect(modelSelect, placeholders.modelo || 'Modelo', [], function (item) {
						return item;
					});
					fillSelect(subbrandSelect, placeholders.submarca || 'Submarca', [], function (item) {
						return item;
					});
				})
				.catch(function () {
					window.alert(i18n.incompleteData || '¡Aún no ha completado la información correspondiente!');
				})
				.finally(function () {
					loader(false);
				});
		});
	});

	setUtmFromQuery();
	populateBirthDates();
	loadBrands();
	bindNumericFields();
	bindLetterFields();
	bindValidation();

	var promo = document.querySelector('.blog-sidebar-promo[data-promo-link]');
	if (promo) {
		promo.addEventListener('click', function () {
			var url = promo.getAttribute('data-promo-link');
			if (url) {
				window.location.href = url;
			}
		});
	}
})();
