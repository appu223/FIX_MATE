/* Small, defensive UI enhancements shared by Fixmate pages. */
(function () {
	'use strict';

	function announceAlerts() {
		document.querySelectorAll('.alert:not([role])').forEach(function (alert) {
			alert.setAttribute('role', alert.classList.contains('alert-danger') ? 'alert' : 'status');
			alert.setAttribute('aria-live', alert.classList.contains('alert-danger') ? 'assertive' : 'polite');
		});
	}

	function initSidebarToggles() {
		document.querySelectorAll('[data-sidebar-toggle]').forEach(function (button) {
			button.addEventListener('click', function () {
				var closedClass = button.dataset.sidebarToggle;
				if (!closedClass) return;

				var isClosed = document.body.classList.toggle(closedClass);
				button.setAttribute('aria-expanded', String(!isClosed));
				button.setAttribute('aria-label', (isClosed ? 'Open' : 'Close') + ' ' + button.getAttribute('aria-label').replace(/^(Open|Close) /, '').toLowerCase());
				var label = button.querySelector('span');
				if (label) label.textContent = isClosed ? 'Open menu' : 'Close menu';
				var icon = button.querySelector('i');
				if (icon) {
					icon.classList.toggle('bi-layout-sidebar-inset', !isClosed);
					icon.classList.toggle('bi-layout-sidebar', isClosed);
				}
			});
		});
	}

	function setFormLoadingStates() {
		document.querySelectorAll('form').forEach(function (form) {
			form.addEventListener('submit', function (event) {
				if (event.defaultPrevented || !form.checkValidity()) return;
				var submitter = event.submitter || form.querySelector('[type="submit"]');
				if (!submitter || submitter.dataset.keepEnabled === 'true') return;

				submitter.dataset.originalLabel = submitter.innerHTML;
				submitter.setAttribute('aria-busy', 'true');
				submitter.classList.add('is-loading');
				if (submitter.tagName === 'BUTTON') submitter.disabled = true;

				window.setTimeout(function () {
					if (!document.documentElement.contains(submitter)) return;
					submitter.disabled = false;
					submitter.removeAttribute('aria-busy');
					submitter.classList.remove('is-loading');
				}, 8000);
			});
		});
	}

	function animatePage() {
		if (!window.gsap || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

		var main = document.querySelector('.fm-main, .customer-main, .main-wrapper > main');
		if (!main) return;

		var groups = Array.prototype.slice.call(main.querySelectorAll(
			':scope > .container-fluid > .row, :scope > .container-fluid > .card-custom, :scope > .container > .row, :scope > .container > .hero-banner, :scope > .container > .mb-5'
		));
		if (!groups.length) groups = Array.prototype.slice.call(main.children).filter(function (node) {
			return node.matches('section, .row, .card-custom, .container-fluid, .container');
		});
		// Never animate a wrapper that contains a modal. A transform/will-change on
		// such an ancestor creates a stacking context, which traps the position:fixed
		// modal underneath the .modal-backdrop Bootstrap appends to <body> — the
		// dialog then renders behind the dim overlay and can never be clicked.
		groups = groups.filter(function (node) {
			return !node.closest('.modal') && !node.querySelector('.modal');
		}).slice(0, 12);
		if (!groups.length) return;

		document.documentElement.classList.add('fm-motion-ready');
		groups.forEach(function (node) { node.classList.add('fm-motion-item'); });
		window.gsap.fromTo(groups,
			{ autoAlpha: 0, y: 12 },
			{ autoAlpha: 1, y: 0, duration: .48, stagger: .055, ease: 'power2.out', clearProps: 'all' }
		);

		if (window.ScrollTrigger && window.innerWidth > 768) {
			window.gsap.utils.toArray('.fm-main .card-custom, .customer-main .card-custom')
				.filter(function (card) { return !card.querySelector('.modal'); })
				.slice(0, 18).forEach(function (card) {
				if (card.getBoundingClientRect().top < window.innerHeight * .88) return;
				window.gsap.fromTo(card,
					{ autoAlpha: 0, y: 14 },
					{ autoAlpha: 1, y: 0, duration: .42, ease: 'power2.out', scrollTrigger: { trigger: card, start: 'top 92%', once: true } }
				);
			});
		}
	}

	function init() {
		announceAlerts();
		initSidebarToggles();
		setFormLoadingStates();
		animatePage();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init, { once: true });
	} else {
		init();
	}
}());
