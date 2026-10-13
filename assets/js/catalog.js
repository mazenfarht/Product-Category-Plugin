/**
 * Custom Product Catalog - front end.
 *
 * Plain JavaScript (no jQuery). All handlers are delegated from the document, so they keep
 * working when a page builder such as Nicepage moves or re-renders the markup.
 *
 * - View tabs (Normal / Exploded / Dimensions) switch the product image.
 * - Finish options become selectable (one active at a time).
 * - The magnifier opens the image that is currently shown in a full-size lightbox.
 */
(function () {
	'use strict';

	var lightbox = null;
	var lastFocus = null;

	function each(list, fn) {
		Array.prototype.forEach.call(list, fn);
	}

	function galleryOf(el) {
		return el && el.closest ? el.closest('[data-cpc-gallery]') : null;
	}

	/* ---------- View tabs ---------- */

	function activateView(gallery, tab) {
		if (!gallery || !tab || tab.getAttribute('aria-disabled') === 'true') {
			return;
		}
		var view = tab.getAttribute('data-view');

		each(gallery.querySelectorAll('.cpc-viewtabs__item'), function (item) {
			var on = item === tab;
			item.classList.toggle('is-active', on);
			item.setAttribute('aria-selected', on ? 'true' : 'false');
			item.setAttribute('tabindex', on ? '0' : '-1');
		});

		each(gallery.querySelectorAll('.cpc-view'), function (panel) {
			var on = panel.getAttribute('data-view') === view;
			panel.classList.toggle('is-active', on);
			if (on) {
				panel.removeAttribute('hidden');
			} else {
				panel.setAttribute('hidden', '');
			}
		});

		gallery.setAttribute('data-active-view', view);
	}

	function moveTab(tab, step) {
		var gallery = galleryOf(tab);
		if (!gallery) {
			return;
		}
		var tabs = Array.prototype.filter.call(
			gallery.querySelectorAll('.cpc-viewtabs__item'),
			function (item) {
				return item.getAttribute('aria-disabled') !== 'true';
			}
		);
		var index = tabs.indexOf(tab);
		if (index < 0 || !tabs.length) {
			return;
		}
		var next = tabs[(index + step + tabs.length) % tabs.length];
		activateView(gallery, next);
		next.focus();
	}

	/* ---------- Finish options ---------- */

	function selectFinish(item) {
		var list = item.parentNode;
		var gallery = galleryOf(item);
		each(list.querySelectorAll('.cpc-finishes__item'), function (other) {
			var on = other === item;
			other.classList.toggle('is-active', on);
			other.setAttribute('aria-pressed', on ? 'true' : 'false');
		});
		if (gallery) {
			var finish = (item.textContent || '').trim();
			gallery.setAttribute('data-finish', finish);
			var evt;
			try {
				evt = new CustomEvent('cpc:finish-change', { bubbles: true, detail: { finish: finish } });
			} catch (e) {
				evt = null;
			}
			if (evt) {
				gallery.dispatchEvent(evt);
			}
		}
	}

	/* ---------- Magnifier / lightbox ---------- */

	function closeLightbox() {
		if (!lightbox) {
			return;
		}
		if (lightbox.parentNode) {
			lightbox.parentNode.removeChild(lightbox);
		}
		lightbox = null;
		document.body.classList.remove('cpc-lightbox-open');
		if (lastFocus && lastFocus.focus) {
			lastFocus.focus();
		}
		lastFocus = null;
	}

	function openLightbox(src, alt) {
		if (!src) {
			return;
		}
		closeLightbox();

		lightbox = document.createElement('div');
		lightbox.className = 'cpc-lightbox';
		lightbox.setAttribute('role', 'dialog');
		lightbox.setAttribute('aria-modal', 'true');

		var img = document.createElement('img');
		img.className = 'cpc-lightbox__img';
		img.src = src;
		img.alt = alt || '';

		var close = document.createElement('button');
		close.type = 'button';
		close.className = 'cpc-lightbox__close';
		close.setAttribute('aria-label', 'Close');
		close.innerHTML = '&times;';

		lightbox.appendChild(img);
		lightbox.appendChild(close);
		document.body.appendChild(lightbox);
		document.body.classList.add('cpc-lightbox-open');
		close.focus();
	}

	function enlarge(trigger) {
		var gallery = galleryOf(trigger);
		if (!gallery) {
			return;
		}
		var img = gallery.querySelector('.cpc-view:not([hidden]) img');
		if (!img) {
			return;
		}
		lastFocus = trigger;
		openLightbox(img.getAttribute('data-full') || img.currentSrc || img.src, img.getAttribute('alt'));
		// openLightbox() calls closeLightbox(), which clears lastFocus; set it again.
		lastFocus = trigger;
	}

	/* ---------- Event delegation ---------- */

	document.addEventListener('click', function (e) {
		var target = e.target;
		if (!target || !target.closest) {
			return;
		}

		if (lightbox && (target === lightbox || target.closest('.cpc-lightbox__close'))) {
			e.preventDefault();
			closeLightbox();
			return;
		}

		var tab = target.closest('.cpc-viewtabs__item');
		if (tab) {
			activateView(galleryOf(tab), tab);
			return;
		}

		var finish = target.closest('.cpc-finishes__item');
		if (finish) {
			selectFinish(finish);
			return;
		}

		var magnifier = target.closest('.cpc-magnifier');
		if (magnifier) {
			e.preventDefault();
			enlarge(magnifier);
		}
	});

	document.addEventListener('keydown', function (e) {
		var key = e.key;

		if (key === 'Escape' && lightbox) {
			closeLightbox();
			return;
		}
		if (lightbox) {
			// Keep focus on the close button while the lightbox is open.
			if (key === 'Tab') {
				e.preventDefault();
				var btn = lightbox.querySelector('.cpc-lightbox__close');
				if (btn) {
					btn.focus();
				}
			}
			return;
		}

		var target = e.target;
		if (!target || !target.closest) {
			return;
		}

		var tab = target.closest('.cpc-viewtabs__item');
		if (tab) {
			if (key === 'Enter' || key === ' ' || key === 'Spacebar') {
				e.preventDefault();
				activateView(galleryOf(tab), tab);
			} else if (key === 'ArrowRight' || key === 'ArrowDown') {
				e.preventDefault();
				moveTab(tab, 1);
			} else if (key === 'ArrowLeft' || key === 'ArrowUp') {
				e.preventDefault();
				moveTab(tab, -1);
			}
			return;
		}

		if (key === 'Enter' || key === ' ' || key === 'Spacebar') {
			var finish = target.closest('.cpc-finishes__item');
			if (finish) {
				e.preventDefault();
				selectFinish(finish);
				return;
			}
			var magnifier = target.closest('.cpc-magnifier');
			if (magnifier) {
				e.preventDefault();
				enlarge(magnifier);
			}
		}
	});
})();
