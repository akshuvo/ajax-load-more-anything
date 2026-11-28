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

	var initialized = false;
	var LOADER_HTML = '<div class="lds-ellipsis" aria-hidden="true"><div></div><div></div><div></div><div></div></div>';

	function bootstrap() {
		if (initialized || typeof window.ald_params === 'undefined' || typeof window.jQuery === 'undefined') {
			return;
		}
		initialized = true;

		var $ = window.jQuery;
		var params = window.ald_params;
		var i18n = params.i18n || {};

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

		function warnMissingSelector(wrapper, field, selector, $matches) {
			if (params.diagnostics === '1' && selector && !$matches.length) {
				window.console.warn('[Load More Anything] Wrapper "' + wrapper.key + '" matched 0 elements for ' + field + ':', selector);
			}
		}

		/**
		 * Apply innerHTML from parsed source node(s) onto target element(s) without duplicating
		 * content when multiple matching bars (e.g. top + bottom pagination) exist.
		 *
		 * @param {jQuery}   $target       Target elements in live DOM.
		 * @param {NodeList} sourceNodes   Matching elements in fetched Document.
		 * @param {string}   implementType 'replace_data' | 'insert_after' | 'insert_before' | 'insert_after_elements'
		 */
		function applyHtmlToTarget($target, sourceNodes, implementType) {
			if (implementType === 'insert_after_elements') {
				$target.last().after(Array.prototype.map.call(sourceNodes, function (node) {
					return node.outerHTML;
				}).join(''));
				return;
			}

			var applySingle = function ($el, html) {
				if (implementType === 'insert_before') {
					$el.prepend(html);
				} else if (implementType === 'insert_after') {
					$el.append(html);
				} else {
					$el.html(html);
				}
			};

			if ($target.length > 1 && sourceNodes.length === $target.length) {
				$target.each(function (idx) {
					applySingle($(this), sourceNodes[idx].innerHTML);
				});
				return;
			}

			applySingle($target, sourceNodes[0].innerHTML);
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
				warnMissingSelector(wrapper, 'btn_selector', wrapper.btn_selector, $container);
				warnMissingSelector(wrapper, 'load_selector', wrapper.load_selector, $allItems);

				if (!$container.length || !$allItems.length) {
					return;
				}

				var visibleItems = Math.max(0, parseInt(wrapper.visible_items, 10) || 0);
				var loadItems = Math.max(1, parseInt(wrapper.load_items, 10) || 3);
				var displayType = wrapper.display_type || 'default';

				function reveal($items) {
					if (displayType !== 'default') {
						$items.css('display', displayType);
					}
					$items.removeClass('ald-item-hidden');
				}

				var $buttons = $();
				$container.each(function () {
					var $button = $('<a>', {
						href: '#',
						role: 'button',
						'class': wrapper.button_class || 'btn loadMoreBtn'
					}).attr('data-glm-button-selector', wrapper.btn_selector);

					if (!document.getElementById('loadMore')) {
						$button.attr('id', 'loadMore');
					}

					$button.append($('<span class="loadMoreBtn-label"></span>').html(wrapper.button_label || 'Load More'));
					if (wrapper.button_placement === 'after') {
						$(this).after($('<div class="ald-btn-wrap"></div>').append($button));
					} else {
						$(this).append($button);
					}
					$buttons = $buttons.add($button);
				});

				$allItems.slice(visibleItems).addClass('ald-item-hidden');
				reveal($allItems.slice(0, visibleItems));

				function updateCount() {
					var remaining = $allItems.filter('.ald-item-hidden').length;
					$buttons.find('.ald-count').text(remaining);
					return remaining;
				}

				if (updateCount() === 0) {
					$buttons.hide();
				}

				$buttons.on('click', function (e) {
					e.preventDefault();
					var $batch = $allItems.filter('.ald-item-hidden').slice(0, loadItems);
					reveal($batch);

					if (updateCount() === 0) {
						$buttons.fadeOut('slow');
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
					observer: null,
					$sentinel: $()
				};
				var $links = safeQuery(wrapper.click_selector);
				warnMissingSelector(wrapper, 'click_selector', wrapper.click_selector, $links);
				if (params.diagnostics === '1') {
					(wrapper.data_implement_selectors || []).forEach(function (row) {
						warnMissingSelector(wrapper, 'data_selector', row.data_selector, safeQuery(row.data_selector));
					});
				}

				var $pagination = $();
				if (wrapper.event_type !== 'selectors_click' && wrapper.hide_selector_wrapper === 'yes') {
					$pagination = safeQuery(wrapper.wrapper_to_hide);
					warnMissingSelector(wrapper, 'wrapper_to_hide', wrapper.wrapper_to_hide, $pagination);
				}
				if (wrapper.event_type === 'scroll_to_load' && $links.length) {
					$pagination.addClass('ald-sr-only');
				}

				// Inject custom Ajax Load More button for Pro custom_button event type.
				if (params.ald_pro === '1' && wrapper.event_type === 'custom_button' && wrapper.custom_button_append) {
					var $appendTarget = safeQuery(wrapper.custom_button_append);
					warnMissingSelector(wrapper, 'custom_button_append', wrapper.custom_button_append, $appendTarget);
					if ($appendTarget.length) {
						var $ajaxBtn = $('<button>', {
							type: 'button',
							'class': 'ald-ajax-btn button'
						}).attr('data-alm-click-selector', wrapper.click_selector);

						$ajaxBtn.append(LOADER_HTML);
						$ajaxBtn.append($('<span class="ald-btn-label"></span>').text(wrapper.button_label || 'Load More'));
						$appendTarget.after($ajaxBtn);

						if (!$links.length) {
							$ajaxBtn.hide();
						} else {
							$pagination.addClass('ald-sr-only');
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

					var trimmedUrl = $.trim(String(targetUrl));
					if (!trimmedUrl || trimmedUrl.charAt(0) === '#' || /^javascript:/i.test(trimmedUrl)) {
						return;
					}

					// Restrict fetches to same-origin URLs for safety.
					try {
						var parsedUrl = new window.URL(trimmedUrl, window.location.href);
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

								applyHtmlToTarget($target, sourceNodes, item.implement_type);

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
					var targetUrl = $(this).attr('href') || $(e.target).closest('a').attr('href') || $(this).closest('a').attr('href');
					if (!targetUrl) {
						return;
					}
					pushAjax(targetUrl);
				});

				// Infinite Scroll via IntersectionObserver (observes bottom-most link to avoid top-bar auto-fetching).
				function bindScrollObserver() {
					if (state.observer) {
						state.observer.disconnect();
						state.observer = null;
					}

					state.$sentinel.remove();
					state.$sentinel = $();

					var $targetEl = safeQuery(wrapper.click_selector).last();
					if (!$targetEl.length) {
						return;
					}

					// A clipped pagination link has no visible intersection area. Observe a
					// one-pixel marker in its original flow position, outside the clipped box.
					var $hiddenPagination = $targetEl.parents('.ald-sr-only').last();
					if (!$hiddenPagination.length) {
						$hiddenPagination = $targetEl.filter('.ald-sr-only');
					}
					if ($hiddenPagination.length) {
						state.$sentinel = $('<span class="ald-pagination-sentinel" aria-hidden="true"></span>');
						$hiddenPagination.after(state.$sentinel);
					}

					var offset = Math.max(0, parseInt(wrapper.trigger_offset, 10) || 0);

					if ('IntersectionObserver' in window) {
						state.observer = new window.IntersectionObserver(
							function (entries) {
								entries.forEach(function (entry) {
									if (entry.isIntersecting && !state.isLoading) {
										$targetEl.trigger('click');
									}
								});
							},
							{
								root: null,
								rootMargin: '0px 0px ' + offset + 'px 0px',
								threshold: 0.01
							}
						);

						state.observer.observe((state.$sentinel.length ? state.$sentinel : $targetEl).get(0));
					}
				}

				if (wrapper.event_type === 'scroll_to_load') {
					bindScrollObserver();
					if (!('IntersectionObserver' in window)) {
						var scrollTicking = false;
						$(window).on('scroll', function () {
							if (scrollTicking || state.isLoading) {
								return;
							}
							scrollTicking = true;
							window.requestAnimationFrame(function () {
								scrollTicking = false;
								var offset = Math.max(0, parseInt(wrapper.trigger_offset, 10) || 0);
								var $targetEl = safeQuery(wrapper.click_selector).last();
								if (!$targetEl.length) {
									return;
								}
								var el = (state.$sentinel.length ? state.$sentinel : $targetEl).get(0);
								var isInview = false;
								if (params.ald_pro === '1' && typeof window.lmaIsViewport === 'function') {
									isInview = window.lmaIsViewport(el, offset);
								} else {
									var H = window.innerHeight || document.documentElement.clientHeight;
									var r = el.getBoundingClientRect();
									isInview = r.top <= H + offset;
								}
								if (isInview) {
									$targetEl.trigger('click');
								}
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

				var $trigger = safeQuery(clickSelector).last();
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
	}

	// Support standard jQuery dependency as well as deferred/late jQuery when disable_jquery_dep is enabled.
	if (typeof window.jQuery !== 'undefined') {
		bootstrap();
	} else {
		document.addEventListener('DOMContentLoaded', bootstrap);
		window.addEventListener('load', bootstrap);
	}
})(window, document);
