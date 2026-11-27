/**
 * Load More Anything — Frontend Runtime Engine
 *
 * Data-driven General (DOM) and Ajax Load More / Infinite Scroll runtime.
 * Preserves public classes (.loadMoreBtn, .ald-ajax-btn, .ald-count) and
 * custom jQuery document events (ald_ajax_content_ready, ald_ajax_content_loaded,
 * ald_ajax_content_success).
 */
(function (window, document) {
	'use strict';

	if (typeof window.ald_params === 'undefined' || typeof window.jQuery === 'undefined') {
		return;
	}

	var $ = window.jQuery;
	var params = window.ald_params;
	var i18n = params.i18n || {};
	var LOADER_HTML = '<div class="lds-ellipsis" aria-hidden="true"><div></div><div></div><div></div><div></div></div>';

	/**
	 * Announce status messages to screen readers via polite live region.
	 *
	 * @param {string} message Status message.
	 */
	function announce(message) {
		if (!message) {
			return;
		}
		var $live = $('.ald-sr-live').first();
		if ($live.length) {
			$live.text('');
			window.setTimeout(function () {
				$live.text(message);
			}, 40);
		}
	}

	/**
	 * Safely query DOM elements for a user-configured CSS selector.
	 *
	 * @param {string} selector CSS selector.
	 * @returns {jQuery}
	 */
	function safeQuery(selector) {
		if (!selector || typeof selector !== 'string') {
			return $();
		}
		try {
			return $(selector);
		} catch (err) {
			return $();
		}
	}

	/**
	 * Find all custom Ajax buttons matching a given trigger selector without CSS injection.
	 *
	 * @param {string} selector Trigger selector string.
	 * @returns {jQuery}
	 */
	function findAjaxButtons(selector) {
		return $('.ald-ajax-btn').filter(function () {
			return $(this).attr('data-alm-click-selector') === selector;
		});
	}

	/**
	 * Re-initialize common WordPress / Elementor / WooCommerce widgets after DOM insertion.
	 *
	 * @param {jQuery} $container Updated container element.
	 */
	function reinitEcosystem($container) {
		$(document.body).trigger('post-load');

		if (
			window.elementorFrontend &&
			window.elementorFrontend.elementsHandler &&
			typeof window.elementorFrontend.elementsHandler.runReadyTrigger === 'function' &&
			$container &&
			$container.length
		) {
			$container.find('.elementor-element').each(function () {
				try {
					window.elementorFrontend.elementsHandler.runReadyTrigger($(this));
				} catch (e) {
					// Ignore third-party widget init errors.
				}
			});
		}
	}

	/**
	 * Parse HTML safely without executing scripts or eagerly fetching images.
	 *
	 * @param {string} html Raw response HTML.
	 * @returns {Document}
	 */
	function parseHtmlDocument(html) {
		var parser = new window.DOMParser();
		return parser.parseFromString(html, 'text/html');
	}

	/**
	 * Reveal items using the configured CSS display type.
	 *
	 * @param {jQuery} $items       Items to reveal.
	 * @param {string} displayType  Configured display type.
	 * @param {boolean} animate     Whether to animate default slideDown.
	 */
	function revealItems($items, displayType, animate) {
		if (!$items.length) {
			return;
		}
		if (!displayType || displayType === 'default') {
			if (animate) {
				$items.slideDown(250);
			} else {
				$items.show();
			}
			return;
		}
		$items.css({ display: displayType });
	}

	/**
	 * Initialize General (client-side DOM) Load More wrappers.
	 */
	function initGeneralWrappers() {
		if (params.isElementorPreview || !Array.isArray(params.general) || !params.general.length) {
			return;
		}

		params.general.forEach(function (wrapper) {
			var $container = safeQuery(wrapper.btn_selector);
			var $allItems = safeQuery(wrapper.load_selector);

			if (!$container.length || !$allItems.length) {
				return;
			}

			var visibleItems = Math.max(0, parseInt(wrapper.visible_items, 10) || 0);
			var loadItems = Math.max(1, parseInt(wrapper.load_items, 10) || 3);
			var displayType = wrapper.display_type || 'default';

			// Hide all items initially, then reveal initial slice.
			$allItems.hide();
			revealItems($allItems.slice(0, visibleItems), displayType, false);

			var hiddenCount = safeQuery(wrapper.load_selector).filter(':hidden').length;

			// Build button safely without string interpolation of user selectors.
			var $btn = $('<a>', {
				href: '#',
				role: 'button',
				'class': wrapper.button_class || 'btn loadMoreBtn'
			}).attr('data-glm-button-selector', wrapper.btn_selector);

			// Retain legacy #loadMore id on first injected button only (avoids duplicate DOM IDs).
			if (!document.getElementById('loadMore')) {
				$btn.attr('id', 'loadMore');
			}

			var $label = $('<span class="loadMoreBtn-label"></span>').html(wrapper.button_label || 'Load More');
			$btn.append($label);
			$container.append($btn);

			var updateCount = function () {
				var remaining = safeQuery(wrapper.load_selector).filter(':hidden').length;
				$container.find('.ald-count').text(remaining);
				return remaining;
			};

			if (updateCount() === 0) {
				$btn.hide();
			}

			$btn.on('click', function (e) {
				e.preventDefault();
				var $hidden = safeQuery(wrapper.load_selector).filter(':hidden');
				var $batch = $hidden.slice(0, loadItems);

				revealItems($batch, displayType, true);

				var remaining = updateCount();
				if (remaining === 0) {
					$btn.fadeOut('slow');
				}

				announce(i18n.loadedItems || 'Additional items loaded.');
				$(document).trigger('ald_general_content_loaded', [$batch, wrapper]);
			});
		});
	}

	/**
	 * Top laser progress bar controller.
	 */
	var laserLoader = {
		start: function () {
			var $bar = $('.ald_loader_progress');
			$('.ald_laser_loader').addClass('show');
			$bar.css({
				'transition-duration': '0ms',
				transform: 'translate3d(-100%, 0, 0)'
			});
			// Force layout frame before animating to -20%.
			window.requestAnimationFrame(function () {
				$bar.css({
					'transition-duration': '2000ms',
					transform: 'translate3d(-20%, 0, 0)'
				});
			});
		},
		finish: function () {
			var $bar = $('.ald_loader_progress');
			$bar.css({
				'transition-duration': '350ms',
				transform: 'translate3d(0%, 0, 0)'
			});
			window.setTimeout(function () {
				$('.ald_laser_loader').removeClass('show');
				$bar.css({
					'transition-duration': '0ms',
					transform: 'translate3d(-100%, 0, 0)'
				});
			}, 350);
		}
	};

	/**
	 * Initialize Ajax-based Load More & Infinite Scroll wrappers.
	 */
	function initAjaxWrappers() {
		if (!Array.isArray(params.ajax) || !params.ajax.length) {
			return;
		}

		params.ajax.forEach(function (wrapper) {
			if (!wrapper.click_selector) {
				return;
			}

			var state = {
				isLoading: false,
				xhr: null,
				observer: null
			};

			// Inject custom Ajax Load More button for Pro custom_button event type.
			if (params.ald_pro === '1' && wrapper.event_type === 'custom_button' && wrapper.custom_button_append) {
				var $appendTarget = safeQuery(wrapper.custom_button_append);
				if ($appendTarget.length) {
					var $ajaxBtn = $('<button>', {
						type: 'button',
						'class': 'ald-ajax-btn button'
					}).attr('data-alm-click-selector', wrapper.click_selector);

					$ajaxBtn.append(LOADER_HTML);
					$ajaxBtn.append($('<span class="ald-btn-label"></span>').text(wrapper.button_label || 'Load More'));
					$appendTarget.after($ajaxBtn);

					if (!safeQuery(wrapper.click_selector).length) {
						$ajaxBtn.hide();
					}
				}
			}

			/**
			 * Build backward-compatible args object passed to custom document events.
			 *
			 * @param {string} targetUrl Destination URL being fetched.
			 * @returns {Object}
			 */
			function buildEventArgs(targetUrl) {
				return {
					event_type: wrapper.event_type,
					custom_button_append: wrapper.custom_button_append,
					button_trigger_selector: wrapper.button_trigger_selector,
					button_label: wrapper.button_label,
					no_more_text: wrapper.no_more_text,
					click_selector: wrapper.click_selector,
					hide_selector_wrapper: wrapper.hide_selector_wrapper,
					wrapper_to_hide: wrapper.wrapper_to_hide,
					update_browser_url: wrapper.update_browser_url,
					update_page_title: wrapper.update_page_title,
					scroll_to_top: wrapper.scroll_to_top,
					data_implement_selectors: JSON.stringify(wrapper.data_implement_selectors || []),
					target_url: targetUrl
				};
			}

			/**
			 * Reset visual & request state after success, failure, or abort.
			 */
			function finishRequest() {
				state.isLoading = false;
				state.xhr = null;
				$('.tf_posts_navigation').removeClass('loading');
				findAjaxButtons(wrapper.click_selector).removeClass('loading').prop('disabled', false);
				laserLoader.finish();
			}

			/**
			 * Perform the Ajax fetch and apply configured Data Implement Selectors.
			 *
			 * @param {string} targetUrl URL to fetch.
			 */
			function pushAjax(targetUrl) {
				if (!targetUrl || state.isLoading) {
					return;
				}

				// Restrict fetches to same-origin URLs for safety.
				try {
					var parsedUrl = new window.URL(targetUrl, window.location.href);
					if (parsedUrl.origin !== window.location.origin) {
						return;
					}
					targetUrl = parsedUrl.href;
				} catch (urlErr) {
					return;
				}

				var args = buildEventArgs(targetUrl);

				if (state.xhr && state.xhr.readyState !== 4) {
					state.xhr.abort();
				}

				state.isLoading = true;
				laserLoader.start();
				announce(i18n.loading || 'Loading more items…');

				state.xhr = $.ajax({
					url: targetUrl,
					async: true,
					dataType: 'html',
					success: function (data) {
						var parsedDoc = parseHtmlDocument(data);

						$(document).trigger('ald_ajax_content_ready', [data, args, parsedDoc]);

						var firstUpdatedEl = null;
						var dis = wrapper.data_implement_selectors || [];

						dis.forEach(function (item) {
							if (!item || !item.data_selector) {
								return;
							}

							var sourceNodes = [];
							try {
								sourceNodes = parsedDoc.querySelectorAll(item.data_selector);
							} catch (selErr) {
								return;
							}

							var $target = safeQuery(item.data_selector);
							if (!$target.length) {
								return;
							}

							if (!sourceNodes.length) {
								if (item.implement_type === 'replace_data') {
									$target.empty();
								}
								return;
							}

							var newHtml = '';
							for (var i = 0; i < sourceNodes.length; i++) {
								newHtml += sourceNodes[i].innerHTML;
							}

							if (item.implement_type === 'insert_before') {
								$target.prepend(newHtml);
							} else if (item.implement_type === 'insert_after') {
								$target.append(newHtml);
							} else {
								$target.html(newHtml);
							}

							if (!firstUpdatedEl) {
								firstUpdatedEl = $target.first();
							}

							reinitEcosystem($target);
						});

						finishRequest();

						if (wrapper.scroll_to_top === 'yes' && firstUpdatedEl && firstUpdatedEl.length) {
							var topOffset = Math.max(0, firstUpdatedEl.offset().top - 40);
							window.scrollTo({ top: topOffset, behavior: 'smooth' });
						}

						announce(i18n.loadedItems || 'Additional items loaded.');
						$(document).trigger('ald_ajax_content_loaded', [data, args]);
						$(document).trigger('ald_ajax_content_success', [args, parsedDoc]);

						if (wrapper.event_type === 'scroll_to_load') {
							bindScrollObserver();
						}
					},
					error: function (jqXHR, textStatus) {
						finishRequest();
						if (textStatus !== 'abort') {
							announce(i18n.loadError || 'Unable to load more items. Please try again.');
							$(document).trigger('ald_ajax_content_error', [jqXHR, textStatus, args]);
						}
					}
				});
			}

			// Delegated click handler for pagination/trigger links.
			$(document).on('click', wrapper.click_selector, function (e) {
				e.preventDefault();
				var $clickedLink = $(e.target).closest('a');
				var targetUrl = $clickedLink.length ? $clickedLink.attr('href') : $(this).attr('href');
				if (!targetUrl) {
					return;
				}
				pushAjax(targetUrl);
			});

			// Infinite Scroll via IntersectionObserver (with graceful fallback).
			function bindScrollObserver() {
				if (state.observer) {
					state.observer.disconnect();
					state.observer = null;
				}

				if (wrapper.event_type !== 'scroll_to_load') {
					return;
				}

				var $targets = safeQuery(wrapper.click_selector);
				if (!$targets.length) {
					return;
				}

				var offset = Math.max(0, parseInt(wrapper.trigger_offset, 10) || 0);

				if ('IntersectionObserver' in window) {
					state.observer = new window.IntersectionObserver(
						function (entries) {
							entries.forEach(function (entry) {
								if (entry.isIntersecting && !state.isLoading) {
									$(entry.target).trigger('click');
								}
							});
						},
						{
							root: null,
							rootMargin: '0px 0px ' + offset + 'px 0px',
							threshold: 0.01
						}
					);

					$targets.each(function () {
						state.observer.observe(this);
					});
				}
			}

			if (wrapper.event_type === 'scroll_to_load') {
				if ('IntersectionObserver' in window) {
					bindScrollObserver();
				} else {
					var scrollTicking = false;
					$(window).on('scroll', function () {
						if (scrollTicking || state.isLoading) {
							return;
						}
						scrollTicking = true;
						window.requestAnimationFrame(function () {
							scrollTicking = false;
							var offset = Math.max(0, parseInt(wrapper.trigger_offset, 10) || 0);
							safeQuery(wrapper.click_selector).each(function (idx, el) {
								if (state.isLoading) {
									return false;
								}
								var isInview = false;
								if (params.ald_pro === '1' && typeof window.lmaIsViewport === 'function') {
									isInview = window.lmaIsViewport(el, offset);
								} else {
									var H = window.innerHeight || document.documentElement.clientHeight;
									var r = el.getBoundingClientRect();
									isInview = r.top <= H + offset;
								}
								if (isInview) {
									$(el).trigger('click');
									return false;
								}
							});
						});
					});
				}
			}
		});

		// Delegated click handler for Pro custom Ajax Load More buttons.
		$(document).on('click', 'button.ald-ajax-btn', function () {
			var $btn = $(this);
			var clickSelector = $btn.attr('data-alm-click-selector');
			if (!clickSelector) {
				return;
			}

			var $trigger = safeQuery(clickSelector).first();
			if ($trigger.length) {
				$btn.addClass('loading').prop('disabled', true);
				$trigger.trigger('click');
			} else {
				var matchedWrapper = null;
				for (var i = 0; i < params.ajax.length; i++) {
					if (params.ajax[i].click_selector === clickSelector) {
						matchedWrapper = params.ajax[i];
						break;
					}
				}
				var noMore = (matchedWrapper && matchedWrapper.no_more_text) || i18n.noMoreData || 'No more items to load';
				$btn.prop('disabled', true).find('.ald-btn-label').text(noMore);
			}
		});
	}

	$(function () {
		initGeneralWrappers();
		initAjaxWrappers();
	});
})(window, document);
