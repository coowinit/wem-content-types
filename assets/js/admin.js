(function () {
	'use strict';

	function updateIconPicker(input) {
		if (!input) {
			return;
		}

		var value = input.value.trim() || 'dashicons-admin-post';
		var picker = input.closest('.wem-ct-icon-field');
		if (!picker) {
			return;
		}

		var preview = picker.querySelector('.wem-ct-icon-preview .dashicons');
		if (preview) {
			preview.className = 'dashicons ' + value;
		}

		picker.querySelectorAll('.wem-ct-icon-choice').forEach(function (button) {
			var active = button.getAttribute('data-icon') === value;
			button.classList.toggle('is-selected', active);
			button.setAttribute('aria-pressed', active ? 'true' : 'false');
		});
	}

	document.addEventListener('click', function (event) {
		var button = event.target.closest('.wem-ct-icon-choice');
		if (!button) {
			return;
		}

		var picker = button.closest('.wem-ct-icon-field');
		var input = picker ? picker.querySelector('input[data-wem-icon-input]') : null;
		if (!input) {
			return;
		}

		input.value = button.getAttribute('data-icon') || '';
		input.dispatchEvent(new Event('input', { bubbles: true }));
	});

	document.addEventListener('input', function (event) {
		if (event.target.matches('input[data-wem-icon-input]')) {
			updateIconPicker(event.target);
		}
	});

	document.querySelectorAll('input[data-wem-icon-input]').forEach(updateIconPicker);
}());

(function () {
	'use strict';

	document.addEventListener('change', function (event) {
		if (!event.target.matches('input[name="wem_ct[public]"]')) {
			return;
		}

		var form = event.target.closest('form');
		if (!form) {
			return;
		}

		var isPublic = event.target.checked;
		var queryable = form.querySelector('input[name="wem_ct[publicly_queryable]"]');
		var excluded = form.querySelector('input[name="wem_ct[exclude_from_search]"]');
		var navMenus = form.querySelector('input[name="wem_ct[show_in_nav_menus]"]');

		if (queryable) {
			queryable.checked = isPublic;
		}
		if (excluded) {
			excluded.checked = !isPublic;
		}
		if (navMenus) {
			navMenus.checked = isPublic;
		}
	});
}());
