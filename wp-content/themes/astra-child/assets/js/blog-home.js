(function () {
	'use strict';

	var button = document.querySelector('[data-blog-load-more]');

	if (!button) {
		return;
	}

	var list = document.querySelector('[data-blog-latest-more]');
	var loadingText = button.getAttribute('data-loading-text') || 'Cargando...';
	var defaultText = button.textContent.trim();
	var isLoading = false;

	button.addEventListener('click', function () {
		if (isLoading || button.disabled) {
			return;
		}

		var offset = parseInt(button.getAttribute('data-offset') || '0', 10);
		var ajaxUrl = button.getAttribute('data-ajax-url');
		var nonce = button.getAttribute('data-nonce');

		if (!ajaxUrl || !nonce || !list) {
			return;
		}

		isLoading = true;
		button.disabled = true;
		button.textContent = loadingText;
		button.classList.add('is-loading');

		var body = JSON.stringify({
			offset: offset,
			nonce: nonce
		});

		fetch(ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/json; charset=UTF-8'
			},
			body: body
		})
			.then(function (response) {
				if (!response.ok) {
					throw new Error('HTTP ' + response.status);
				}

				return response.json();
			})
			.then(function (data) {
				if (!data || typeof data.html === 'undefined') {
					throw new Error('Invalid response');
				}

				if (data.html) {
					list.insertAdjacentHTML('beforeend', data.html);
				}

				if (data.has_more) {
					button.setAttribute('data-offset', String(data.next_offset));
					button.disabled = false;
					button.textContent = defaultText;
				} else {
					button.closest('.blog-qualitas-pagination').remove();
				}
			})
			.catch(function () {
				button.disabled = false;
				button.textContent = defaultText;
			})
			.finally(function () {
				isLoading = false;
				button.classList.remove('is-loading');
			});
	});
})();
