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

		var body = new URLSearchParams();
		body.append('action', 'astra_child_load_more_posts');
		body.append('nonce', nonce);
		body.append('offset', String(offset));

		fetch(ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
			},
			body: body.toString()
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (payload) {
				if (!payload || !payload.success || !payload.data) {
					throw new Error('Invalid response');
				}

				if (payload.data.html) {
					list.insertAdjacentHTML('beforeend', payload.data.html);
				}

				if (payload.data.has_more) {
					button.setAttribute('data-offset', String(payload.data.next_offset));
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
