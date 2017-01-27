/**
 *  Ajax Autocomplete for jQuery, version 1.2.24
 *  (c) 2015 Tomas Kirda
 *
 *  Ajax Autocomplete for jQuery is freely distributable under the terms of an MIT-style license.
 *  For details, see the web site: https://github.com/devbridge/jQuery-Autocomplete
 */

/*jslint  browser: true, white: true, plusplus: true, vars: true */
/*global define, window, document, jQuery, exports, require */

// Expose plugin as an AMD module if AMD loader is present:
(function (factory) {
	'use strict';
	if (typeof define === 'function' && define.amd) {
		// AMD. Register as an anonymous module.
		define(['jquery'], factory);
	} else if (typeof exports === 'object' && typeof require === 'function') {
		// Browserify
		factory(require('jquery'));
	} else {
		// Browser globals
		factory(jQuery);
	}
}(function ($) {
	'use strict';

	var
		utils = (function () {
			return {
				escapeRegExChars: function (value) {
					return value.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
				},
				createNode: function (containerClass) {
					var div = document.createElement('div');
					div.className = containerClass;
					div.style.position = 'absolute';
					div.style.display = 'none';
					return div;
				}
			};
		}()),

		keys = {
			ESC: 27,
			TAB: 9,
			RETURN: 13,
			LEFT: 37,
			UP: 38,
			RIGHT: 39,
			DOWN: 40
		};

	function Autocomplete(el, options) {
		var noop = function () { },
		    that = this,
		    defaults = {
			    ajaxSettings: {},
			    autoSelectFirst: false,
			    appendTo: document.body,
			    serviceUrl: null,
			    lookup: null,
			    onSelect: null,
			    width: 'auto',
			    minChars: 1,
			    maxHeight: 300,
			    deferRequestBy: 0,
			    params: {},
			    formatResult: Autocomplete.formatResult,
			    delimiter: null,
			    zIndex: 9999,
			    type: 'GET',
			    noCache: false,
			    onSearchStart: noop,
			    onSearchComplete: noop,
			    onSearchError: noop,
			    preserveInput: false,
			    containerClass: 'autocomplete-suggestions',
			    tabDisabled: false,
			    dataType: 'text',
			    currentRequest: null,
			    triggerSelectOnValidInput: true,
			    preventBadQueries: true,
			    lookupFilter: function (suggestion, originalQuery, queryLowerCase) {
				    return suggestion.value.toLowerCase().indexOf(queryLowerCase) !== -1;
			    },
			    paramName: 'query',
			    transformResult: function (response) {
				    return typeof response === 'string' ? $.parseJSON(response) : response;
			    },
			    showNoSuggestionNotice: false,
			    noSuggestionNotice: 'No results',
			    orientation: 'bottom',
			    forceFixPosition: false
		    };

		// Shared variables:
		that.element = el;
		that.el = $(el);
		that.suggestions = [];
		that.badQueries = [];
		that.selectedIndex = -1;
		that.currentValue = that.element.value;
		that.intervalId = 0;
		that.cachedResponse = {};
		that.onChangeInterval = null;
		that.onChange = null;
		that.isLocal = false;
		that.suggestionsContainer = null;
		that.noSuggestionsContainer = null;
		that.options = $.extend({}, defaults, options);
		that.classes = {
			selected: 'autocomplete-selected',
			suggestion: 'autocomplete-suggestion'
		};
		that.hint = null;
		that.hintValue = '';
		that.selection = null;

		// Initialize and set options:
		that.initialize();
		that.setOptions(options);
	}

	Autocomplete.utils = utils;

	$.Autocomplete = Autocomplete;

	Autocomplete.formatResult = function (suggestion, currentValue) {
		var pattern = '(' + utils.escapeRegExChars(currentValue) + ')';

		return suggestion.value
			.replace(new RegExp(pattern, 'gi'), '<strong>$1<\/strong>')
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/&lt;(\/?strong)&gt;/g, '<$1>');
	};

	Autocomplete.prototype = {

		killerFn: null,

		initialize: function () {
			var that = this,
			    suggestionSelector = '.' + that.classes.suggestion,
			    selected = that.classes.selected,
			    options = that.options,
			    container;

			// Remove autocomplete attribute to prevent native suggestions:
			that.element.setAttribute('autocomplete', 'off');

			that.killerFn = function (e) {
				if ($(e.target).closest('.' + that.options.containerClass).length === 0) {
					that.killSuggestions();
					that.disableKillerFn();
				}
			};

			// html() deals with many types: htmlString or Element or Array or jQuery
			that.noSuggestionsContainer = $('<div class="autocomplete-no-suggestion"></div>')
				.html(this.options.noSuggestionNotice).get(0);

			that.suggestionsContainer = Autocomplete.utils.createNode(options.containerClass);

			container = $(that.suggestionsContainer);

			container.appendTo(options.appendTo);

			// Only set width if it was provided:
			if (options.width !== 'auto') {
				container.width(options.width);
			}

			// Listen for mouse over event on suggestions list:
			container.on('mouseover.autocomplete', suggestionSelector, function () {
				that.activate($(this).data('index'));
			});

			// Deselect active element when mouse leaves suggestions container:
			container.on('mouseout.autocomplete', function () {
				that.selectedIndex = -1;
				container.children('.' + selected).removeClass(selected);
			});

			// Listen for click event on suggestions list:
			container.on('click.autocomplete', suggestionSelector, function () {
				that.select($(this).data('index'));
			});

			that.fixPositionCapture = function () {
				if (that.visible) {
					that.fixPosition();
				}
			};

			$(window).on('resize.autocomplete', that.fixPositionCapture);

			that.el.on('keydown.autocomplete', function (e) { that.onKeyPress(e); });
			that.el.on('keyup.autocomplete', function (e) { that.onKeyUp(e); });
			that.el.on('blur.autocomplete', function () { that.onBlur(); });
			that.el.on('focus.autocomplete', function () { that.onFocus(); });
			that.el.on('change.autocomplete', function (e) { that.onKeyUp(e); });
			that.el.on('input.autocomplete', function (e) { that.onKeyUp(e); });
		},

		onFocus: function () {
			var that = this;
			that.fixPosition();
			if (that.options.minChars === 0 && that.el.val().length === 0) {
				that.onValueChange();
			}
		},

		onBlur: function () {
			this.enableKillerFn();
		},

		abortAjax: function () {
			var that = this;
			if (that.currentRequest) {
				that.currentRequest.abort();
				that.currentRequest = null;
			}
		},

		setOptions: function (suppliedOptions) {
			var that = this,
			    options = that.options;

			$.extend(options, suppliedOptions);

			that.isLocal = $.isArray(options.lookup);

			if (that.isLocal) {
				options.lookup = that.verifySuggestionsFormat(options.lookup);
			}

			options.orientation = that.validateOrientation(options.orientation, 'bottom');

			// Adjust height, width and z-index:
			$(that.suggestionsContainer).css({
				'max-height': options.maxHeight + 'px',
				'width': options.width + 'px',
				'z-index': options.zIndex
			});
		},


		clearCache: function () {
			this.cachedResponse = {};
			this.badQueries = [];
		},

		clear: function () {
			this.clearCache();
			this.currentValue = '';
			this.suggestions = [];
		},

		disable: function () {
			var that = this;
			that.disabled = true;
			clearInterval(that.onChangeInterval);
			that.abortAjax();
		},

		enable: function () {
			this.disabled = false;
		},

		fixPosition: function () {
			// Use only when container has already its content

			var that = this,
			    $container = $(that.suggestionsContainer),
			    containerParent = $container.parent().get(0);
			// Fix position automatically when appended to body.
			// In other cases force parameter must be given.
			if (containerParent !== document.body && !that.options.forceFixPosition) {
				return;
			}

			// Choose orientation
			var orientation = that.options.orientation,
			    containerHeight = $container.outerHeight(),
			    height = that.el.outerHeight(),
			    offset = that.el.offset(),
			    styles = { 'top': offset.top, 'left': offset.left };

			if (orientation === 'auto') {
				var viewPortHeight = $(window).height(),
				    scrollTop = $(window).scrollTop(),
				    topOverflow = -scrollTop + offset.top - containerHeight,
				    bottomOverflow = scrollTop + viewPortHeight - (offset.top + height + containerHeight);

				orientation = (Math.max(topOverflow, bottomOverflow) === topOverflow) ? 'top' : 'bottom';
			}

			if (orientation === 'top') {
				styles.top += -containerHeight;
			} else {
				styles.top += height;
			}

			// If container is not positioned to body,
			// correct its position using offset parent offset
			if(containerParent !== document.body) {
				var opacity = $container.css('opacity'),
				    parentOffsetDiff;

				if (!that.visible){
					$container.css('opacity', 0).show();
				}

				parentOffsetDiff = $container.offsetParent().offset();
				styles.top -= parentOffsetDiff.top;
				styles.left -= parentOffsetDiff.left;

				if (!that.visible){
					$container.css('opacity', opacity).hide();
				}
			}

			// -2px to account for suggestions border.
			if (that.options.width === 'auto') {
				styles.width = (that.el.outerWidth() - 2) + 'px';
			}

			$container.css(styles);
		},

		enableKillerFn: function () {
			var that = this;
			$(document).on('click.autocomplete', that.killerFn);
		},

		disableKillerFn: function () {
			var that = this;
			$(document).off('click.autocomplete', that.killerFn);
		},

		killSuggestions: function () {
			var that = this;
			that.stopKillSuggestions();
			that.intervalId = window.setInterval(function () {
				if (that.visible) {
					that.el.val(that.currentValue);
					that.hide();
				}

				that.stopKillSuggestions();
			}, 50);
		},

		stopKillSuggestions: function () {
			window.clearInterval(this.intervalId);
		},

		isCursorAtEnd: function () {
			var that = this,
			    valLength = that.el.val().length,
			    selectionStart = that.element.selectionStart,
			    range;

			if (typeof selectionStart === 'number') {
				return selectionStart === valLength;
			}
			if (document.selection) {
				range = document.selection.createRange();
				range.moveStart('character', -valLength);
				return valLength === range.text.length;
			}
			return true;
		},

		onKeyPress: function (e) {
			var that = this;

			// If suggestions are hidden and user presses arrow down, display suggestions:
			if (!that.disabled && !that.visible && e.which === keys.DOWN && that.currentValue) {
				that.suggest();
				return;
			}

			if (that.disabled || !that.visible) {
				return;
			}

			switch (e.which) {
				case keys.ESC:
					that.el.val(that.currentValue);
					that.hide();
					break;
				case keys.RIGHT:
					if (that.hint && that.options.onHint && that.isCursorAtEnd()) {
						that.selectHint();
						break;
					}
					return;
				case keys.TAB:
					if (that.hint && that.options.onHint) {
						that.selectHint();
						return;
					}
					if (that.selectedIndex === -1) {
						that.hide();
						return;
					}
					that.select(that.selectedIndex);
					if (that.options.tabDisabled === false) {
						return;
					}
					break;
				case keys.RETURN:
					if (that.selectedIndex === -1) {
						that.hide();
						return;
					}
					that.select(that.selectedIndex);
					break;
				case keys.UP:
					that.moveUp();
					break;
				case keys.DOWN:
					that.moveDown();
					break;
				default:
					return;
			}

			// Cancel event if function did not return:
			e.stopImmediatePropagation();
			e.preventDefault();
		},

		onKeyUp: function (e) {
			var that = this;

			if (that.disabled) {
				return;
			}

			switch (e.which) {
				case keys.UP:
				case keys.DOWN:
					return;
			}

			clearInterval(that.onChangeInterval);

			if (that.currentValue !== that.el.val()) {
				that.findBestHint();
				if (that.options.deferRequestBy > 0) {
					// Defer lookup in case when value changes very quickly:
					that.onChangeInterval = setInterval(function () {
						that.onValueChange();
					}, that.options.deferRequestBy);
				} else {
					that.onValueChange();
				}
			}
		},

		onValueChange: function () {
			var that = this,
			    options = that.options,
			    value = that.el.val(),
			    query = that.getQuery(value);

			if (that.selection && that.currentValue !== query) {
				that.selection = null;
				(options.onInvalidateSelection || $.noop).call(that.element);
			}

			clearInterval(that.onChangeInterval);
			that.currentValue = value;
			that.selectedIndex = -1;

			// Check existing suggestion for the match before proceeding:
			if (options.triggerSelectOnValidInput && that.isExactMatch(query)) {
				that.select(0);
				return;
			}

			if (query.length < options.minChars) {
				that.hide();
			} else {
				that.getSuggestions(query);
			}
		},

		isExactMatch: function (query) {
			var suggestions = this.suggestions;

			return (suggestions.length === 1 && suggestions[0].value.toLowerCase() === query.toLowerCase());
		},

		getQuery: function (value) {
			var delimiter = this.options.delimiter,
			    parts;

			if (!delimiter) {
				return value;
			}
			parts = value.split(delimiter);
			return $.trim(parts[parts.length - 1]);
		},

		getSuggestionsLocal: function (query) {
			var that = this,
			    options = that.options,
			    queryLowerCase = query.toLowerCase(),
			    filter = options.lookupFilter,
			    limit = parseInt(options.lookupLimit, 10),
			    data;

			data = {
				suggestions: $.grep(options.lookup, function (suggestion) {
					return filter(suggestion, query, queryLowerCase);
				})
			};

			if (limit && data.suggestions.length > limit) {
				data.suggestions = data.suggestions.slice(0, limit);
			}

			return data;
		},

		getSuggestions: function (q) {
			var response,
			    that = this,
			    options = that.options,
			    serviceUrl = options.serviceUrl,
			    params,
			    cacheKey,
			    ajaxSettings;

			options.params[options.paramName] = q;
			params = options.ignoreParams ? null : options.params;

			if (options.onSearchStart.call(that.element, options.params) === false) {
				return;
			}

			if ($.isFunction(options.lookup)){
				options.lookup(q, function (data) {
					that.suggestions = data.suggestions;
					that.suggest();
					options.onSearchComplete.call(that.element, q, data.suggestions);
				});
				return;
			}

			if (that.isLocal) {
				response = that.getSuggestionsLocal(q);
			} else {
				if ($.isFunction(serviceUrl)) {
					serviceUrl = serviceUrl.call(that.element, q);
				}
				cacheKey = serviceUrl + '?' + $.param(params || {});
				response = that.cachedResponse[cacheKey];
			}

			if (response && $.isArray(response.suggestions)) {
				that.suggestions = response.suggestions;
				that.suggest();
				options.onSearchComplete.call(that.element, q, response.suggestions);
			} else if (!that.isBadQuery(q)) {
				that.abortAjax();

				ajaxSettings = {
					url: serviceUrl,
					data: params,
					type: options.type,
					dataType: options.dataType
				};

				$.extend(ajaxSettings, options.ajaxSettings);

				that.currentRequest = $.ajax(ajaxSettings).done(function (data) {
					var result;
					that.currentRequest = null;
					result = options.transformResult(data, q);
					that.processResponse(result, q, cacheKey);
					options.onSearchComplete.call(that.element, q, result.suggestions);
				}).fail(function (jqXHR, textStatus, errorThrown) {
					options.onSearchError.call(that.element, q, jqXHR, textStatus, errorThrown);
				});
			} else {
				options.onSearchComplete.call(that.element, q, []);
			}
		},

		isBadQuery: function (q) {
			if (!this.options.preventBadQueries){
				return false;
			}

			var badQueries = this.badQueries,
			    i = badQueries.length;

			while (i--) {
				if (q.indexOf(badQueries[i]) === 0) {
					return true;
				}
			}

			return false;
		},

		hide: function () {
			var that = this,
			    container = $(that.suggestionsContainer);

			if ($.isFunction(that.options.onHide) && that.visible) {
				that.options.onHide.call(that.element, container);
			}

			that.visible = false;
			that.selectedIndex = -1;
			clearInterval(that.onChangeInterval);
			$(that.suggestionsContainer).hide();
			that.signalHint(null);
		},

		suggest: function () {
			if (this.suggestions.length === 0) {
				if (this.options.showNoSuggestionNotice) {
					this.noSuggestions();
				} else {
					this.hide();
				}
				return;
			}

			var that = this,
			    options = that.options,
			    groupBy = options.groupBy,
			    formatResult = options.formatResult,
			    value = that.getQuery(that.currentValue),
			    className = that.classes.suggestion,
			    classSelected = that.classes.selected,
			    container = $(that.suggestionsContainer),
			    noSuggestionsContainer = $(that.noSuggestionsContainer),
			    beforeRender = options.beforeRender,
			    html = '',
			    category,
			    formatGroup = function (suggestion, index) {
				    var currentCategory = suggestion.data[groupBy];

				    if (category === currentCategory){
					    return '';
				    }

				    category = currentCategory;

				    return '<div class="autocomplete-group"><strong>' + category + '</strong></div>';
			    };

			if (options.triggerSelectOnValidInput && that.isExactMatch(value)) {
				that.select(0);
				return;
			}

			// Build suggestions inner HTML:
			$.each(that.suggestions, function (i, suggestion) {
				if (groupBy){
					html += formatGroup(suggestion, value, i);
				}

				html += '<div class="'+ ( PAKB.category ? 'autocomplete-with-category ' : '' )  + className + '" data-index="' + i + '"><span class="'+ suggestion.icon +'"></span> ' + formatResult(suggestion, value) + '</div>';
			});

			this.adjustContainerWidth();

			noSuggestionsContainer.detach();
			container.html(html);

			if ($.isFunction(beforeRender)) {
				beforeRender.call(that.element, container);
			}

			that.fixPosition();
			container.show();

			// Select first value by default:
			if (options.autoSelectFirst) {
				that.selectedIndex = 0;
				container.scrollTop(0);
				container.children('.' + className).first().addClass(classSelected);
			}

			that.visible = true;
			that.findBestHint();
		},

		noSuggestions: function() {
			var that = this,
			    container = $(that.suggestionsContainer),
			    noSuggestionsContainer = $(that.noSuggestionsContainer);

			this.adjustContainerWidth();

			// Some explicit steps. Be careful here as it easy to get
			// noSuggestionsContainer removed from DOM if not detached properly.
			noSuggestionsContainer.detach();
			container.empty(); // clean suggestions if any
			container.append(noSuggestionsContainer);

			that.fixPosition();

			container.show();
			that.visible = true;
		},

		adjustContainerWidth: function() {
			var that = this,
			    options = that.options,
			    width,
			    container = $(that.suggestionsContainer);

			// If width is auto, adjust width before displaying suggestions,
			// because if instance was created before input had width, it will be zero.
			// Also it adjusts if input width has changed.
			// -2px to account for suggestions border.
			if (options.width === 'auto') {
				width = that.el.outerWidth() - 2;
				container.width(width > 0 ? width : 300);
			}
		},

		findBestHint: function () {
			var that = this,
			    value = that.el.val().toLowerCase(),
			    bestMatch = null;

			if (!value) {
				return;
			}

			$.each(that.suggestions, function (i, suggestion) {
				var foundMatch = suggestion.value.toLowerCase().indexOf(value) === 0;
				if (foundMatch) {
					bestMatch = suggestion;
				}
				return !foundMatch;
			});

			that.signalHint(bestMatch);
		},

		signalHint: function (suggestion) {
			var hintValue = '',
			    that = this;
			if (suggestion) {
				hintValue = that.currentValue + suggestion.value.substr(that.currentValue.length);
			}
			if (that.hintValue !== hintValue) {
				that.hintValue = hintValue;
				that.hint = suggestion;
				(this.options.onHint || $.noop)(hintValue);
			}
		},

		verifySuggestionsFormat: function (suggestions) {
			// If suggestions is string array, convert them to supported format:
			if (suggestions.length && typeof suggestions[0] === 'string') {
				return $.map(suggestions, function (value) {
					return { value: value, data: null };
				});
			}

			return suggestions;
		},

		validateOrientation: function(orientation, fallback) {
			orientation = $.trim(orientation || '').toLowerCase();

			if($.inArray(orientation, ['auto', 'bottom', 'top']) === -1){
				orientation = fallback;
			}

			return orientation;
		},

		processResponse: function (result, originalQuery, cacheKey) {
			var that = this,
			    options = that.options;

			result.suggestions = that.verifySuggestionsFormat(result.suggestions);

			// Cache results if cache is not disabled:
			if (!options.noCache) {
				that.cachedResponse[cacheKey] = result;
				if (options.preventBadQueries && result.suggestions.length === 0) {
					that.badQueries.push(originalQuery);
				}
			}

			// Return if originalQuery is not matching current query:
			if (originalQuery !== that.getQuery(that.currentValue)) {
				return;
			}

			that.suggestions = result.suggestions;
			that.suggest();
		},

		activate: function (index) {
			var that = this,
			    activeItem,
			    selected = that.classes.selected,
			    container = $(that.suggestionsContainer),
			    children = container.find('.' + that.classes.suggestion);

			container.find('.' + selected).removeClass(selected);

			that.selectedIndex = index;

			if (that.selectedIndex !== -1 && children.length > that.selectedIndex) {
				activeItem = children.get(that.selectedIndex);
				$(activeItem).addClass(selected);
				return activeItem;
			}

			return null;
		},

		selectHint: function () {
			var that = this,
			    i = $.inArray(that.hint, that.suggestions);

			that.select(i);
		},

		select: function (i) {
			var that = this;
			that.hide();
			that.onSelect(i);
		},

		moveUp: function () {
			var that = this;

			if (that.selectedIndex === -1) {
				return;
			}

			if (that.selectedIndex === 0) {
				$(that.suggestionsContainer).children().first().removeClass(that.classes.selected);
				that.selectedIndex = -1;
				that.el.val(that.currentValue);
				that.findBestHint();
				return;
			}

			that.adjustScroll(that.selectedIndex - 1);
		},

		moveDown: function () {
			var that = this;

			if (that.selectedIndex === (that.suggestions.length - 1)) {
				return;
			}

			that.adjustScroll(that.selectedIndex + 1);
		},

		adjustScroll: function (index) {
			var that = this,
			    activeItem = that.activate(index);

			if (!activeItem) {
				return;
			}

			var offsetTop,
			    upperBound,
			    lowerBound,
			    heightDelta = $(activeItem).outerHeight();

			offsetTop = activeItem.offsetTop;
			upperBound = $(that.suggestionsContainer).scrollTop();
			lowerBound = upperBound + that.options.maxHeight - heightDelta;

			if (offsetTop < upperBound) {
				$(that.suggestionsContainer).scrollTop(offsetTop);
			} else if (offsetTop > lowerBound) {
				$(that.suggestionsContainer).scrollTop(offsetTop - that.options.maxHeight + heightDelta);
			}

			if (!that.options.preserveInput) {
				that.el.val(that.getValue(that.suggestions[index].value));
			}
			that.signalHint(null);
		},

		onSelect: function (index) {
			var that = this,
			    onSelectCallback = that.options.onSelect,
			    suggestion = that.suggestions[index];

			that.currentValue = that.getValue(suggestion.value);

			if (that.currentValue !== that.el.val() && !that.options.preserveInput) {
				that.el.val(that.currentValue);
			}

			that.signalHint(null);
			that.suggestions = [];
			that.selection = suggestion;

			if ($.isFunction(onSelectCallback)) {
				onSelectCallback.call(that.element, suggestion);
			}
		},

		getValue: function (value) {
			var that = this,
			    delimiter = that.options.delimiter,
			    currentValue,
			    parts;

			if (!delimiter) {
				return value;
			}

			currentValue = that.currentValue;
			parts = currentValue.split(delimiter);

			if (parts.length === 1) {
				return value;
			}

			return currentValue.substr(0, currentValue.length - parts[parts.length - 1].length) + value;
		},

		dispose: function () {
			var that = this;
			that.el.off('.autocomplete').removeData('autocomplete');
			that.disableKillerFn();
			$(window).off('resize.autocomplete', that.fixPositionCapture);
			$(that.suggestionsContainer).remove();
		}
	};

	// Create chainable jQuery plugin:
	$.fn.autocomplete = $.fn.devbridgeAutocomplete = function (options, args) {
		var dataKey = 'autocomplete';
		// If function invoked without argument return
		// instance of the first matched element:
		if (arguments.length === 0) {
			return this.first().data(dataKey);
		}

		return this.each(function () {
			var inputElement = $(this),
			    instance = inputElement.data(dataKey);

			if (typeof options === 'string') {
				if (instance && typeof instance[options] === 'function') {
					instance[options](args);
				}
			} else {
				// If instance already exists, destroy it:
				if (instance && instance.dispose) {
					instance.dispose();
				}
				instance = new Autocomplete(this, options);
				inputElement.data(dataKey, instance);
			}
		});
	};
}));
/*

Tooltipster 3.3.0 | 2014-11-08
A rockin' custom tooltip jQuery plugin

Developed by Caleb Jacob under the MIT license http://opensource.org/licenses/MIT

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/

;(function ($, window, document) {

	var pluginName = "tooltipster",
		defaults = {
			animation: 'fade',
			arrow: true,
			arrowColor: '',
			autoClose: true,
			content: null,
			contentAsHTML: false,
			contentCloning: true,
			debug: true,
			delay: 200,
			minWidth: 0,
			maxWidth: null,
			functionInit: function(origin, content) {},
			functionBefore: function(origin, continueTooltip) {
				continueTooltip();
			},
			functionReady: function(origin, tooltip) {},
			functionAfter: function(origin) {},
			hideOnClick: false,
			icon: '(?)',
			iconCloning: true,
			iconDesktop: false,
			iconTouch: false,
			iconTheme: 'tooltipster-icon',
			interactive: false,
			interactiveTolerance: 350,
			multiple: false,
			offsetX: 0,
			offsetY: 0,
			onlyOne: false,
			position: 'top',
			positionTracker: false,
			positionTrackerCallback: function(origin){
				// the default tracker callback will close the tooltip when the trigger is
				// 'hover' (see https://github.com/iamceege/tooltipster/pull/253)
				if(this.option('trigger') == 'hover' && this.option('autoClose')) {
					this.hide();
				}
			},
			restoration: 'current',
			speed: 350,
			timer: 0,
			theme: 'tooltipster-default',
			touchDevices: true,
			trigger: 'hover',
			updateAnimation: true
		};
	
	function Plugin(element, options) {
		
		// list of instance variables
		
		this.bodyOverflowX;
		// stack of custom callbacks provided as parameters to API methods
		this.callbacks = {
			hide: [],
			show: []
		};
		this.checkInterval = null;
		// this will be the user content shown in the tooltip. A capital "C" is used because there is also a method called content()
		this.Content;
		// this is the original element which is being applied the tooltipster plugin
		this.$el = $(element);
		// this will be the element which triggers the appearance of the tooltip on hover/click/custom events.
		// it will be the same as this.$el if icons are not used (see in the options), otherwise it will correspond to the created icon
		this.$elProxy;
		this.elProxyPosition;
		this.enabled = true;
		this.options = $.extend({}, defaults, options);
		this.mouseIsOverProxy = false;
		// a unique namespace per instance, for easy selective unbinding
		this.namespace = 'tooltipster-'+ Math.round(Math.random()*100000);
		// Status (capital S) can be either : appearing, shown, disappearing, hidden
		this.Status = 'hidden';
		this.timerHide = null;
		this.timerShow = null;
		// this will be the tooltip element (jQuery wrapped HTML element)
		this.$tooltip;
		
		// for backward compatibility
		this.options.iconTheme = this.options.iconTheme.replace('.', '');
		this.options.theme = this.options.theme.replace('.', '');
		
		// launch
		
		this._init();
	}
	
	Plugin.prototype = {
		
		_init: function() {
			
			var self = this;
			
			// disable the plugin on old browsers (including IE7 and lower)
			if (document.querySelector) {
				
				// note : the content is null (empty) by default and can stay that way if the plugin remains initialized but not fed any content. The tooltip will just not appear.
				
				// let's save the initial value of the title attribute for later restoration if need be.
				var initialTitle = null;
				// it will already have been saved in case of multiple tooltips
				if (self.$el.data('tooltipster-initialTitle') === undefined) {
					
					initialTitle = self.$el.attr('title');
					
					// we do not want initialTitle to have the value "undefined" because of how jQuery's .data() method works
					if (initialTitle === undefined) initialTitle = null;
					
					self.$el.data('tooltipster-initialTitle', initialTitle);
				}
				
				// if content is provided in the options, its has precedence over the title attribute.
				// Note : an empty string is considered content, only 'null' represents the absence of content.
				// Also, an existing title="" attribute will result in an empty string content
				if (self.options.content !== null){
					self._content_set(self.options.content);
				}
				else {
					self._content_set(initialTitle);
				}
				
				var c = self.options.functionInit.call(self.$el, self.$el, self.Content);
				if(typeof c !== 'undefined') self._content_set(c);
				
				self.$el
					// strip the title off of the element to prevent the default tooltips from popping up
					.removeAttr('title')
					// to be able to find all instances on the page later (upon window events in particular)
					.addClass('tooltipstered');

				// detect if we're changing the tooltip origin to an icon
				// note about this condition : if the device has touch capability and self.options.iconTouch is false, you'll have no icons event though you may consider your device as a desktop if it also has a mouse. Not sure why someone would have this use case though.
				if ((!deviceHasTouchCapability && self.options.iconDesktop) || (deviceHasTouchCapability && self.options.iconTouch)) {
					
					// TODO : the tooltip should be automatically be given an absolute position to be near the origin. Otherwise, when the origin is floating or what, it's going to be nowhere near it and disturb the position flow of the page elements. It will imply that the icon also detects when its origin moves, to follow it : not trivial.
					// Until it's done, the icon feature does not really make sense since the user still has most of the work to do by himself
					
					// if the icon provided is in the form of a string
					if(typeof self.options.icon === 'string'){
						// wrap it in a span with the icon class
						self.$elProxy = $('<span class="'+ self.options.iconTheme +'"></span>');
						self.$elProxy.text(self.options.icon);
					}
					// if it is an object (sensible choice)
					else {
						// (deep) clone the object if iconCloning == true, to make sure every instance has its own proxy. We use the icon without wrapping, no need to. We do not give it a class either, as the user will undoubtedly style the object on his own and since our css properties may conflict with his own
						if (self.options.iconCloning) self.$elProxy = self.options.icon.clone(true);
						else self.$elProxy = self.options.icon;
					}
					
					self.$elProxy.insertAfter(self.$el);
				}
				else {
					self.$elProxy = self.$el;
				}
				
				// for 'click' and 'hover' triggers : bind on events to open the tooltip. Closing is now handled in _showNow() because of its bindings.
				// Notes about touch events :
					// - mouseenter, mouseleave and clicks happen even on pure touch devices because they are emulated. deviceIsPureTouch() is a simple attempt to detect them.
					// - on hybrid devices, we do not prevent touch gesture from opening tooltips. It would be too complex to differentiate real mouse events from emulated ones.
					// - we check deviceIsPureTouch() at each event rather than prior to binding because the situation may change during browsing
				if (self.options.trigger == 'hover') {
					
					// these binding are for mouse interaction only
					self.$elProxy
						.on('mouseenter.'+ self.namespace, function() {
							if (!deviceIsPureTouch() || self.options.touchDevices) {
								self.mouseIsOverProxy = true;
								self._show();
							}
						})
						.on('mouseleave.'+ self.namespace, function() {
							if (!deviceIsPureTouch() || self.options.touchDevices) {
								self.mouseIsOverProxy = false;
							}
						});
					
					// for touch interaction only
					if (deviceHasTouchCapability && self.options.touchDevices) {
						
						// for touch devices, we immediately display the tooltip because we cannot rely on mouseleave to handle the delay
						self.$elProxy.on('touchstart.'+ self.namespace, function() {
							self._showNow();
						});
					}
				}
				else if (self.options.trigger == 'click') {
					
					// note : for touch devices, we do not bind on touchstart, we only rely on the emulated clicks (triggered by taps)
					self.$elProxy.on('click.'+ self.namespace, function() {
						if (!deviceIsPureTouch() || self.options.touchDevices) {
							self._show();
						}
					});
				}
			}
		},
		
		// this function will schedule the opening of the tooltip after the delay, if there is one
		_show: function() {
			
			var self = this;
			
			if (self.Status != 'shown' && self.Status != 'appearing') {
				
				if (self.options.delay) {
					self.timerShow = setTimeout(function(){
						
						// for hover trigger, we check if the mouse is still over the proxy, otherwise we do not show anything
						if (self.options.trigger == 'click' || (self.options.trigger == 'hover' && self.mouseIsOverProxy)) {
							self._showNow();
						}
					}, self.options.delay);
				}
				else self._showNow();
			}
		},
		
		// this function will open the tooltip right away
		_showNow: function(callback) {
			
			var self = this;
			
			// call our constructor custom function before continuing
			self.options.functionBefore.call(self.$el, self.$el, function() {
				
				// continue only if the tooltip is enabled and has any content
				if (self.enabled && self.Content !== null) {
				
					// save the method callback and cancel hide method callbacks
					if (callback) self.callbacks.show.push(callback);
					self.callbacks.hide = [];
					
					//get rid of any appearance timer
					clearTimeout(self.timerShow);
					self.timerShow = null;
					clearTimeout(self.timerHide);
					self.timerHide = null;
					
					// if we only want one tooltip open at a time, close all auto-closing tooltips currently open and not already disappearing
					if (self.options.onlyOne) {
						$('.tooltipstered').not(self.$el).each(function(i,el) {
							
							var $el = $(el),
								nss = $el.data('tooltipster-ns');
							
							// iterate on all tooltips of the element
							$.each(nss, function(i, ns){
								var instance = $el.data(ns),
									// we have to use the public methods here
									s = instance.status(),
									ac = instance.option('autoClose');
								
								if (s !== 'hidden' && s !== 'disappearing' && ac) {
									instance.hide();
								}
							});
						});
					}
					
					var finish = function() {
						self.Status = 'shown';
						
						// trigger any show method custom callbacks and reset them
						$.each(self.callbacks.show, function(i,c) { c.call(self.$el); });
						self.callbacks.show = [];
					};
					
					// if this origin already has its tooltip open
					if (self.Status !== 'hidden') {
						
						// the timer (if any) will start (or restart) right now
						var extraTime = 0;
						
						// if it was disappearing, cancel that
						if (self.Status === 'disappearing') {
							
							self.Status = 'appearing';
							
							if (supportsTransitions()) {
								
								self.$tooltip
									.clearQueue()
									.removeClass('tooltipster-dying')
									.addClass('tooltipster-'+ self.options.animation +'-show');
								
								if (self.options.speed > 0) self.$tooltip.delay(self.options.speed);
								
								self.$tooltip.queue(finish);
							}
							else {
								// in case the tooltip was currently fading out, bring it back to life
								self.$tooltip
									.stop()
									.fadeIn(finish);
							}
						}
						// if the tooltip is already open, we still need to trigger the method custom callback
						else if(self.Status === 'shown') {
							finish();
						}
					}
					// if the tooltip isn't already open, open that sucker up!
					else {
						
						self.Status = 'appearing';
						
						// the timer (if any) will start when the tooltip has fully appeared after its transition
						var extraTime = self.options.speed;
						
						// disable horizontal scrollbar to keep overflowing tooltips from jacking with it and then restore it to its previous value
						self.bodyOverflowX = $('body').css('overflow-x');
						$('body').css('overflow-x', 'hidden');
						
						// get some other settings related to building the tooltip
						var animation = 'tooltipster-' + self.options.animation,
							animationSpeed = '-webkit-transition-duration: '+ self.options.speed +'ms; -webkit-animation-duration: '+ self.options.speed +'ms; -moz-transition-duration: '+ self.options.speed +'ms; -moz-animation-duration: '+ self.options.speed +'ms; -o-transition-duration: '+ self.options.speed +'ms; -o-animation-duration: '+ self.options.speed +'ms; -ms-transition-duration: '+ self.options.speed +'ms; -ms-animation-duration: '+ self.options.speed +'ms; transition-duration: '+ self.options.speed +'ms; animation-duration: '+ self.options.speed +'ms;',
							minWidth = self.options.minWidth ? 'min-width:'+ Math.round(self.options.minWidth) +'px;' : '',
							maxWidth = self.options.maxWidth ? 'max-width:'+ Math.round(self.options.maxWidth) +'px;' : '',
							pointerEvents = self.options.interactive ? 'pointer-events: auto;' : '';
						
						// build the base of our tooltip
						self.$tooltip = $('<div class="tooltipster-base '+ self.options.theme +'" style="'+ minWidth +' '+ maxWidth +' '+ pointerEvents +' '+ animationSpeed +'"><div class="tooltipster-content"></div></div>');
						
						// only add the animation class if the user has a browser that supports animations
						if (supportsTransitions()) self.$tooltip.addClass(animation);
						
						// insert the content
						self._content_insert();
						
						// attach
						self.$tooltip.appendTo('body');
						
						// do all the crazy calculations and positioning
						self.reposition();
						
						// call our custom callback since the content of the tooltip is now part of the DOM
						self.options.functionReady.call(self.$el, self.$el, self.$tooltip);
						
						// animate in the tooltip
						if (supportsTransitions()) {
							
							self.$tooltip.addClass(animation + '-show');
							
							if(self.options.speed > 0) self.$tooltip.delay(self.options.speed);
							
							self.$tooltip.queue(finish);
						}
						else {
							self.$tooltip.css('display', 'none').fadeIn(self.options.speed, finish);
						}
						
						// will check if our tooltip origin is removed while the tooltip is shown
						self._interval_set();
						
						// reposition on scroll (otherwise position:fixed element's tooltips will move away form their origin) and on resize (in case position can/has to be changed)
						$(window).on('scroll.'+ self.namespace +' resize.'+ self.namespace, function() {
							self.reposition();
						});
						
						// auto-close bindings
						if (self.options.autoClose) {
							
							// in case a listener is already bound for autoclosing (mouse or touch, hover or click), unbind it first
							$('body').off('.'+ self.namespace);
							
							// here we'll have to set different sets of bindings for both touch and mouse
							if (self.options.trigger == 'hover') {
								
								// if the user touches the body, hide
								if (deviceHasTouchCapability) {
									// timeout 0 : explanation below in click section
									setTimeout(function() {
										// we don't want to bind on click here because the initial touchstart event has not yet triggered its click event, which is thus about to happen
										$('body').on('touchstart.'+ self.namespace, function() {
											self.hide();
										});
									}, 0);
								}
								
								// if we have to allow interaction
								if (self.options.interactive) {
									
									// touch events inside the tooltip must not close it
									if (deviceHasTouchCapability) {
										self.$tooltip.on('touchstart.'+ self.namespace, function(event) {
											event.stopPropagation();
										});
									}
									
									// as for mouse interaction, we get rid of the tooltip only after the mouse has spent some time out of it
									var tolerance = null;
									
									self.$elProxy.add(self.$tooltip)
										// hide after some time out of the proxy and the tooltip
										.on('mouseleave.'+ self.namespace + '-autoClose', function() {
											clearTimeout(tolerance);
											tolerance = setTimeout(function(){
												self.hide();
											}, self.options.interactiveTolerance);
										})
										// suspend timeout when the mouse is over the proxy or the tooltip
										.on('mouseenter.'+ self.namespace + '-autoClose', function() {
											clearTimeout(tolerance);
										});
								}
								// if this is a non-interactive tooltip, get rid of it if the mouse leaves
								else {
									self.$elProxy.on('mouseleave.'+ self.namespace + '-autoClose', function() {
										self.hide();
									});
								}
								
								// close the tooltip when the proxy gets a click (common behavior of native tooltips)
								if (self.options.hideOnClick) {
									
									self.$elProxy.on('click.'+ self.namespace + '-autoClose', function() {
										self.hide();
									});
								}
							}
							// here we'll set the same bindings for both clicks and touch on the body to hide the tooltip
							else if(self.options.trigger == 'click'){
								
								// use a timeout to prevent immediate closing if the method was called on a click event and if options.delay == 0 (because of bubbling)
								setTimeout(function() {
									$('body').on('click.'+ self.namespace +' touchstart.'+ self.namespace, function() {
										self.hide();
									});
								}, 0);
								
								// if interactive, we'll stop the events that were emitted from inside the tooltip to stop autoClosing
								if (self.options.interactive) {
									
									// note : the touch events will just not be used if the plugin is not enabled on touch devices
									self.$tooltip.on('click.'+ self.namespace +' touchstart.'+ self.namespace, function(event) {
										event.stopPropagation();
									});
								}
							}
						}
					}
					
					// if we have a timer set, let the countdown begin
					if (self.options.timer > 0) {
						
						self.timerHide = setTimeout(function() {
							self.timerHide = null;
							self.hide();
						}, self.options.timer + extraTime);
					}
				}
			});
		},
		
		_interval_set: function() {
			
			var self = this;
			
			self.checkInterval = setInterval(function() {
				
				// if the tooltip and/or its interval should be stopped
				if (
						// if the origin has been removed
						$('body').find(self.$el).length === 0
						// if the elProxy has been removed
					||	$('body').find(self.$elProxy).length === 0
						// if the tooltip has been closed
					||	self.Status == 'hidden'
						// if the tooltip has somehow been removed
					||	$('body').find(self.$tooltip).length === 0
				) {
					// remove the tooltip if it's still here
					if (self.Status == 'shown' || self.Status == 'appearing') self.hide();
					
					// clear this interval as it is no longer necessary
					self._interval_cancel();
				}
				// if everything is alright
				else {
					// compare the former and current positions of the elProxy to reposition the tooltip if need be
					if(self.options.positionTracker){
						
						var p = self._repositionInfo(self.$elProxy),
							identical = false;
						
						// compare size first (a change requires repositioning too)
						if(areEqual(p.dimension, self.elProxyPosition.dimension)){
							
							// for elements with a fixed position, we track the top and left properties (relative to window)
							if(self.$elProxy.css('position') === 'fixed'){
								if(areEqual(p.position, self.elProxyPosition.position)) identical = true;
							}
							// otherwise, track total offset (relative to document)
							else {
								if(areEqual(p.offset, self.elProxyPosition.offset)) identical = true;
							}
						}
						
						if(!identical){
							self.reposition();
							self.options.positionTrackerCallback.call(self, self.$el);
						}
					}
				}
			}, 200);
		},
		
		_interval_cancel: function() {
			clearInterval(this.checkInterval);
			// clean delete
			this.checkInterval = null;
		},
		
		_content_set: function(content) {
			// clone if asked. Cloning the object makes sure that each instance has its own version of the content (in case a same object were provided for several instances)
			// reminder : typeof null === object
			if (typeof content === 'object' && content !== null && this.options.contentCloning) {
				content = content.clone(true);
			}
			this.Content = content;
		},
		
		_content_insert: function() {
			
			var self = this,
				$d = this.$tooltip.find('.tooltipster-content');
			
			if (typeof self.Content === 'string' && !self.options.contentAsHTML) {
				$d.text(self.Content);
			}
			else {
				$d
					.empty()
					.append(self.Content);
			}
		},
		
		_update: function(content) {
			
			var self = this;
			
			// change the content
			self._content_set(content);
			
			if (self.Content !== null) {
				
				// update the tooltip if it is open
				if (self.Status !== 'hidden') {
					
					// reset the content in the tooltip
					self._content_insert();
					
					// reposition and resize the tooltip
					self.reposition();
					
					// if we want to play a little animation showing the content changed
					if (self.options.updateAnimation) {
						
						if (supportsTransitions()) {
							
							self.$tooltip.css({
								'width': '',
								'-webkit-transition': 'all ' + self.options.speed + 'ms, width 0ms, height 0ms, left 0ms, top 0ms',
								'-moz-transition': 'all ' + self.options.speed + 'ms, width 0ms, height 0ms, left 0ms, top 0ms',
								'-o-transition': 'all ' + self.options.speed + 'ms, width 0ms, height 0ms, left 0ms, top 0ms',
								'-ms-transition': 'all ' + self.options.speed + 'ms, width 0ms, height 0ms, left 0ms, top 0ms',
								'transition': 'all ' + self.options.speed + 'ms, width 0ms, height 0ms, left 0ms, top 0ms'
							}).addClass('tooltipster-content-changing');
							
							// reset the CSS transitions and finish the change animation
							setTimeout(function() {
								
								if(self.Status != 'hidden'){
									
									self.$tooltip.removeClass('tooltipster-content-changing');
									
									// after the changing animation has completed, reset the CSS transitions
									setTimeout(function() {
										
										if(self.Status !== 'hidden'){
											self.$tooltip.css({
												'-webkit-transition': self.options.speed + 'ms',
												'-moz-transition': self.options.speed + 'ms',
												'-o-transition': self.options.speed + 'ms',
												'-ms-transition': self.options.speed + 'ms',
												'transition': self.options.speed + 'ms'
											});
										}
									}, self.options.speed);
								}
							}, self.options.speed);
						}
						else {
							self.$tooltip.fadeTo(self.options.speed, 0.5, function() {
								if(self.Status != 'hidden'){
									self.$tooltip.fadeTo(self.options.speed, 1);
								}
							});
						}
					}
				}
			}
			else {
				self.hide();
			}
		},
		
		_repositionInfo: function($el) {
			return {
				dimension: {
					height: $el.outerHeight(false),
					width: $el.outerWidth(false)
				},
				offset: $el.offset(),
				position: {
					left: parseInt($el.css('left')),
					top: parseInt($el.css('top'))
				}
			};
		},
		
		hide: function(callback) {
			
			var self = this;
			
			// save the method custom callback and cancel any show method custom callbacks
			if (callback) self.callbacks.hide.push(callback);
			self.callbacks.show = [];
			
			// get rid of any appearance timeout
			clearTimeout(self.timerShow);
			self.timerShow = null;
			clearTimeout(self.timerHide);
			self.timerHide = null;
			
			var finishCallbacks = function() {
				// trigger any hide method custom callbacks and reset them
				$.each(self.callbacks.hide, function(i,c) { c.call(self.$el); });
				self.callbacks.hide = [];
			};
			
			// hide
			if (self.Status == 'shown' || self.Status == 'appearing') {
				
				self.Status = 'disappearing';
				
				var finish = function() {
					
					self.Status = 'hidden';
					
					// detach our content object first, so the next jQuery's remove() call does not unbind its event handlers
					if (typeof self.Content == 'object' && self.Content !== null) {
						self.Content.detach();
					}
					
					self.$tooltip.remove();
					self.$tooltip = null;
					
					// unbind orientationchange, scroll and resize listeners
					$(window).off('.'+ self.namespace);
					
					$('body')
						// unbind any auto-closing click/touch listeners
						.off('.'+ self.namespace)
						.css('overflow-x', self.bodyOverflowX);
					
					// unbind any auto-closing click/touch listeners
					$('body').off('.'+ self.namespace);
					
					// unbind any auto-closing hover listeners
					self.$elProxy.off('.'+ self.namespace + '-autoClose');
					
					// call our constructor custom callback function
					self.options.functionAfter.call(self.$el, self.$el);
					
					// call our method custom callbacks functions
					finishCallbacks();
				};
				
				if (supportsTransitions()) {
					
					self.$tooltip
						.clearQueue()
						.removeClass('tooltipster-' + self.options.animation + '-show')
						// for transitions only
						.addClass('tooltipster-dying');
					
					if(self.options.speed > 0) self.$tooltip.delay(self.options.speed);
					
					self.$tooltip.queue(finish);
				}
				else {
					self.$tooltip
						.stop()
						.fadeOut(self.options.speed, finish);
				}
			}
			// if the tooltip is already hidden, we still need to trigger the method custom callback
			else if(self.Status == 'hidden') {
				finishCallbacks();
			}
			
			return self;
		},
		
		// the public show() method is actually an alias for the private showNow() method
		show: function(callback) {
			this._showNow(callback);
			return this;
		},
		
		// 'update' is deprecated in favor of 'content' but is kept for backward compatibility
		update: function(c) {
			return this.content(c);
		},
		content: function(c) {
			// getter method
			if(typeof c === 'undefined'){
				return this.Content;
			}
			// setter method
			else {
				this._update(c);
				return this;
			}
		},
		
		reposition: function() {
			
			var self = this;
			
			// in case the tooltip has been removed from DOM manually
			if ($('body').find(self.$tooltip).length !== 0) {
				
				// reset width
				self.$tooltip.css('width', '');
				
				// find variables to determine placement
				self.elProxyPosition = self._repositionInfo(self.$elProxy);
				var arrowReposition = null,
					windowWidth = $(window).width(),
					// shorthand
					proxy = self.elProxyPosition,
					tooltipWidth = self.$tooltip.outerWidth(false),
					tooltipInnerWidth = self.$tooltip.innerWidth() + 1, // this +1 stops FireFox from sometimes forcing an additional text line
					tooltipHeight = self.$tooltip.outerHeight(false);
				
				// if this is an <area> tag inside a <map>, all hell breaks loose. Recalculate all the measurements based on coordinates
				if (self.$elProxy.is('area')) {
					var areaShape = self.$elProxy.attr('shape'),
						mapName = self.$elProxy.parent().attr('name'),
						map = $('img[usemap="#'+ mapName +'"]'),
						mapOffsetLeft = map.offset().left,
						mapOffsetTop = map.offset().top,
						areaMeasurements = self.$elProxy.attr('coords') !== undefined ? self.$elProxy.attr('coords').split(',') : undefined;
					
					if (areaShape == 'circle') {
						var areaLeft = parseInt(areaMeasurements[0]),
							areaTop = parseInt(areaMeasurements[1]),
							areaWidth = parseInt(areaMeasurements[2]);
						proxy.dimension.height = areaWidth * 2;
						proxy.dimension.width = areaWidth * 2;
						proxy.offset.top = mapOffsetTop + areaTop - areaWidth;
						proxy.offset.left = mapOffsetLeft + areaLeft - areaWidth;
					}
					else if (areaShape == 'rect') {
						var areaLeft = parseInt(areaMeasurements[0]),
							areaTop = parseInt(areaMeasurements[1]),
							areaRight = parseInt(areaMeasurements[2]),
							areaBottom = parseInt(areaMeasurements[3]);
						proxy.dimension.height = areaBottom - areaTop;
						proxy.dimension.width = areaRight - areaLeft;
						proxy.offset.top = mapOffsetTop + areaTop;
						proxy.offset.left = mapOffsetLeft + areaLeft;
					}
					else if (areaShape == 'poly') {
						var areaXs = [],
							areaYs = [],
							areaSmallestX = 0,
							areaSmallestY = 0,
							areaGreatestX = 0,
							areaGreatestY = 0,
							arrayAlternate = 'even';
						
						for (var i = 0; i < areaMeasurements.length; i++) {
							var areaNumber = parseInt(areaMeasurements[i]);
							
							if (arrayAlternate == 'even') {
								if (areaNumber > areaGreatestX) {
									areaGreatestX = areaNumber;
									if (i === 0) {
										areaSmallestX = areaGreatestX;
									}
								}
								
								if (areaNumber < areaSmallestX) {
									areaSmallestX = areaNumber;
								}
								
								arrayAlternate = 'odd';
							}
							else {
								if (areaNumber > areaGreatestY) {
									areaGreatestY = areaNumber;
									if (i == 1) {
										areaSmallestY = areaGreatestY;
									}
								}
								
								if (areaNumber < areaSmallestY) {
									areaSmallestY = areaNumber;
								}
								
								arrayAlternate = 'even';
							}
						}
					
						proxy.dimension.height = areaGreatestY - areaSmallestY;
						proxy.dimension.width = areaGreatestX - areaSmallestX;
						proxy.offset.top = mapOffsetTop + areaSmallestY;
						proxy.offset.left = mapOffsetLeft + areaSmallestX;
					}
					else {
						proxy.dimension.height = map.outerHeight(false);
						proxy.dimension.width = map.outerWidth(false);
						proxy.offset.top = mapOffsetTop;
						proxy.offset.left = mapOffsetLeft;
					}
				}
				
				// our function and global vars for positioning our tooltip
				var myLeft = 0,
					myLeftMirror = 0,
					myTop = 0,
					offsetY = parseInt(self.options.offsetY),
					offsetX = parseInt(self.options.offsetX),
					// this is the arrow position that will eventually be used. It may differ from the position option if the tooltip cannot be displayed in this position
					practicalPosition = self.options.position;
				
				// a function to detect if the tooltip is going off the screen horizontally. If so, reposition the crap out of it!
				function dontGoOffScreenX() {
				
					var windowLeft = $(window).scrollLeft();
					
					// if the tooltip goes off the left side of the screen, line it up with the left side of the window
					if((myLeft - windowLeft) < 0) {
						arrowReposition = myLeft - windowLeft;
						myLeft = windowLeft;
					}
					
					// if the tooltip goes off the right of the screen, line it up with the right side of the window
					if (((myLeft + tooltipWidth) - windowLeft) > windowWidth) {
						arrowReposition = myLeft - ((windowWidth + windowLeft) - tooltipWidth);
						myLeft = (windowWidth + windowLeft) - tooltipWidth;
					}
				}
				
				// a function to detect if the tooltip is going off the screen vertically. If so, switch to the opposite!
				function dontGoOffScreenY(switchTo, switchFrom) {
					// if it goes off the top off the page
					if(((proxy.offset.top - $(window).scrollTop() - tooltipHeight - offsetY - 12) < 0) && (switchFrom.indexOf('top') > -1)) {
						practicalPosition = switchTo;
					}
					
					// if it goes off the bottom of the page
					if (((proxy.offset.top + proxy.dimension.height + tooltipHeight + 12 + offsetY) > ($(window).scrollTop() + $(window).height())) && (switchFrom.indexOf('bottom') > -1)) {
						practicalPosition = switchTo;
						myTop = (proxy.offset.top - tooltipHeight) - offsetY - 12;
					}
				}
				
				if(practicalPosition == 'top') {
					var leftDifference = (proxy.offset.left + tooltipWidth) - (proxy.offset.left + proxy.dimension.width);
					myLeft = (proxy.offset.left + offsetX) - (leftDifference / 2);
					myTop = (proxy.offset.top - tooltipHeight) - offsetY - 12;
					dontGoOffScreenX();
					dontGoOffScreenY('bottom', 'top');
				}
				
				if(practicalPosition == 'top-left') {
					myLeft = proxy.offset.left + offsetX;
					myTop = (proxy.offset.top - tooltipHeight) - offsetY - 12;
					dontGoOffScreenX();
					dontGoOffScreenY('bottom-left', 'top-left');
				}
				
				if(practicalPosition == 'top-right') {
					myLeft = (proxy.offset.left + proxy.dimension.width + offsetX) - tooltipWidth;
					myTop = (proxy.offset.top - tooltipHeight) - offsetY - 12;
					dontGoOffScreenX();
					dontGoOffScreenY('bottom-right', 'top-right');
				}
				
				if(practicalPosition == 'bottom') {
					var leftDifference = (proxy.offset.left + tooltipWidth) - (proxy.offset.left + proxy.dimension.width);
					myLeft = proxy.offset.left - (leftDifference / 2) + offsetX;
					myTop = (proxy.offset.top + proxy.dimension.height) + offsetY + 12;
					dontGoOffScreenX();
					dontGoOffScreenY('top', 'bottom');
				}
				
				if(practicalPosition == 'bottom-left') {
					myLeft = proxy.offset.left + offsetX;
					myTop = (proxy.offset.top + proxy.dimension.height) + offsetY + 12;
					dontGoOffScreenX();
					dontGoOffScreenY('top-left', 'bottom-left');
				}
				
				if(practicalPosition == 'bottom-right') {
					myLeft = (proxy.offset.left + proxy.dimension.width + offsetX) - tooltipWidth;
					myTop = (proxy.offset.top + proxy.dimension.height) + offsetY + 12;
					dontGoOffScreenX();
					dontGoOffScreenY('top-right', 'bottom-right');
				}
				
				if(practicalPosition == 'left') {
					myLeft = proxy.offset.left - offsetX - tooltipWidth - 12;
					myLeftMirror = proxy.offset.left + offsetX + proxy.dimension.width + 12;
					var topDifference = (proxy.offset.top + tooltipHeight) - (proxy.offset.top + proxy.dimension.height);
					myTop = proxy.offset.top - (topDifference / 2) - offsetY;
					
					// if the tooltip goes off boths sides of the page
					if((myLeft < 0) && ((myLeftMirror + tooltipWidth) > windowWidth)) {
						var borderWidth = parseFloat(self.$tooltip.css('border-width')) * 2,
							newWidth = (tooltipWidth + myLeft) - borderWidth;
						self.$tooltip.css('width', newWidth + 'px');
						
						tooltipHeight = self.$tooltip.outerHeight(false);
						myLeft = proxy.offset.left - offsetX - newWidth - 12 - borderWidth;
						topDifference = (proxy.offset.top + tooltipHeight) - (proxy.offset.top + proxy.dimension.height);
						myTop = proxy.offset.top - (topDifference / 2) - offsetY;
					}
					
					// if it only goes off one side, flip it to the other side
					else if(myLeft < 0) {
						myLeft = proxy.offset.left + offsetX + proxy.dimension.width + 12;
						arrowReposition = 'left';
					}
				}
				
				if(practicalPosition == 'right') {
					myLeft = proxy.offset.left + offsetX + proxy.dimension.width + 12;
					myLeftMirror = proxy.offset.left - offsetX - tooltipWidth - 12;
					var topDifference = (proxy.offset.top + tooltipHeight) - (proxy.offset.top + proxy.dimension.height);
					myTop = proxy.offset.top - (topDifference / 2) - offsetY;
					
					// if the tooltip goes off boths sides of the page
					if(((myLeft + tooltipWidth) > windowWidth) && (myLeftMirror < 0)) {
						var borderWidth = parseFloat(self.$tooltip.css('border-width')) * 2,
							newWidth = (windowWidth - myLeft) - borderWidth;
						self.$tooltip.css('width', newWidth + 'px');
						
						tooltipHeight = self.$tooltip.outerHeight(false);
						topDifference = (proxy.offset.top + tooltipHeight) - (proxy.offset.top + proxy.dimension.height);
						myTop = proxy.offset.top - (topDifference / 2) - offsetY;
					}
						
					// if it only goes off one side, flip it to the other side
					else if((myLeft + tooltipWidth) > windowWidth) {
						myLeft = proxy.offset.left - offsetX - tooltipWidth - 12;
						arrowReposition = 'right';
					}
				}
				
				// if arrow is set true, style it and append it
				if (self.options.arrow) {
	
					var arrowClass = 'tooltipster-arrow-' + practicalPosition;
					
					// set color of the arrow
					if(self.options.arrowColor.length < 1) {
						var arrowColor = self.$tooltip.css('background-color');
					}
					else {
						var arrowColor = self.options.arrowColor;
					}
					
					// if the tooltip was going off the page and had to re-adjust, we need to update the arrow's position
					if (!arrowReposition) {
						arrowReposition = '';
					}
					else if (arrowReposition == 'left') {
						arrowClass = 'tooltipster-arrow-right';
						arrowReposition = '';
					}
					else if (arrowReposition == 'right') {
						arrowClass = 'tooltipster-arrow-left';
						arrowReposition = '';
					}
					else {
						arrowReposition = 'left:'+ Math.round(arrowReposition) +'px;';
					}
					
					// building the logic to create the border around the arrow of the tooltip
					if ((practicalPosition == 'top') || (practicalPosition == 'top-left') || (practicalPosition == 'top-right')) {
						var tooltipBorderWidth = parseFloat(self.$tooltip.css('border-bottom-width')),
							tooltipBorderColor = self.$tooltip.css('border-bottom-color');
					}
					else if ((practicalPosition == 'bottom') || (practicalPosition == 'bottom-left') || (practicalPosition == 'bottom-right')) {
						var tooltipBorderWidth = parseFloat(self.$tooltip.css('border-top-width')),
							tooltipBorderColor = self.$tooltip.css('border-top-color');
					}
					else if (practicalPosition == 'left') {
						var tooltipBorderWidth = parseFloat(self.$tooltip.css('border-right-width')),
							tooltipBorderColor = self.$tooltip.css('border-right-color');
					}
					else if (practicalPosition == 'right') {
						var tooltipBorderWidth = parseFloat(self.$tooltip.css('border-left-width')),
							tooltipBorderColor = self.$tooltip.css('border-left-color');
					}
					else {
						var tooltipBorderWidth = parseFloat(self.$tooltip.css('border-bottom-width')),
							tooltipBorderColor = self.$tooltip.css('border-bottom-color');
					}
					
					if (tooltipBorderWidth > 1) {
						tooltipBorderWidth++;
					}
					
					var arrowBorder = '';
					if (tooltipBorderWidth !== 0) {
						var arrowBorderSize = '',
							arrowBorderColor = 'border-color: '+ tooltipBorderColor +';';
						if (arrowClass.indexOf('bottom') !== -1) {
							arrowBorderSize = 'margin-top: -'+ Math.round(tooltipBorderWidth) +'px;';
						}
						else if (arrowClass.indexOf('top') !== -1) {
							arrowBorderSize = 'margin-bottom: -'+ Math.round(tooltipBorderWidth) +'px;';
						}
						else if (arrowClass.indexOf('left') !== -1) {
							arrowBorderSize = 'margin-right: -'+ Math.round(tooltipBorderWidth) +'px;';
						}
						else if (arrowClass.indexOf('right') !== -1) {
							arrowBorderSize = 'margin-left: -'+ Math.round(tooltipBorderWidth) +'px;';
						}
						arrowBorder = '<span class="tooltipster-arrow-border" style="'+ arrowBorderSize +' '+ arrowBorderColor +';"></span>';
					}
					
					// if the arrow already exists, remove and replace it
					self.$tooltip.find('.tooltipster-arrow').remove();
					
					// build out the arrow and append it		
					var arrowConstruct = '<div class="'+ arrowClass +' tooltipster-arrow" style="'+ arrowReposition +'">'+ arrowBorder +'<span style="border-color:'+ arrowColor +';"></span></div>';
					self.$tooltip.append(arrowConstruct);
				}
				
				// position the tooltip
				self.$tooltip.css({'top': Math.round(myTop) + 'px', 'left': Math.round(myLeft) + 'px'});
			}
			
			return self;
		},
		
		enable: function() {
			this.enabled = true;
			return this;
		},
		
		disable: function() {
			// hide first, in case the tooltip would not disappear on its own (autoClose false)
			this.hide();
			this.enabled = false;
			return this;
		},
		
		destroy: function() {
			
			var self = this;
			
			self.hide();
			
			// remove the icon, if any
			if (self.$el[0] !== self.$elProxy[0]) {
				self.$elProxy.remove();
			}
			
			self.$el
				.removeData(self.namespace)
				.off('.'+ self.namespace);
			
			var ns = self.$el.data('tooltipster-ns');
			
			// if there are no more tooltips on this element
			if(ns.length === 1){
				
				// optional restoration of a title attribute
				var title = null;
				if (self.options.restoration === 'previous'){
					title = self.$el.data('tooltipster-initialTitle');
				}
				else if(self.options.restoration === 'current'){
					
					// old school technique to stringify when outerHTML is not supported
					title =
						(typeof self.Content === 'string') ?
						self.Content :
						$('<div></div>').append(self.Content).html();
				}
				
				if (title) {
					self.$el.attr('title', title);
				}
				
				// final cleaning
				self.$el
					.removeClass('tooltipstered')
					.removeData('tooltipster-ns')
					.removeData('tooltipster-initialTitle');
			}
			else {
				// remove the instance namespace from the list of namespaces of tooltips present on the element
				ns = $.grep(ns, function(el, i){
					return el !== self.namespace;
				});
				self.$el.data('tooltipster-ns', ns);
			}
			
			return self;
		},
		
		elementIcon: function() {
			return (this.$el[0] !== this.$elProxy[0]) ? this.$elProxy[0] : undefined;
		},
		
		elementTooltip: function() {
			return this.$tooltip ? this.$tooltip[0] : undefined;
		},
		
		// public methods but for internal use only
		// getter if val is ommitted, setter otherwise
		option: function(o, val) {
			if (typeof val == 'undefined') return this.options[o];
			else {
				this.options[o] = val;
				return this;
			}
		},
		status: function() {
			return this.Status;
		}
	};
	
	$.fn[pluginName] = function () {
		
		// for using in closures
		var args = arguments;
		
		// if we are not in the context of jQuery wrapped HTML element(s) :
		// this happens when calling static methods in the form $.fn.tooltipster('methodName'), or when calling $(sel).tooltipster('methodName or options') where $(sel) does not match anything
		if (this.length === 0) {
			
			// if the first argument is a method name
			if (typeof args[0] === 'string') {
				
				var methodIsStatic = true;
				
				// list static methods here (usable by calling $.fn.tooltipster('methodName');)
				switch (args[0]) {
					
					case 'setDefaults':
						// change default options for all future instances
						$.extend(defaults, args[1]);
						break;
					
					default:
						methodIsStatic = false;
						break;
				}
				
				// $.fn.tooltipster('methodName') calls will return true
				if (methodIsStatic) return true;
				// $(sel).tooltipster('methodName') calls will return the list of objects event though it's empty because chaining should work on empty lists
				else return this;
			}
			// the first argument is undefined or an object of options : we are initalizing but there is no element matched by selector
			else {
				// still chainable : same as above
				return this;
			}
		}
		// this happens when calling $(sel).tooltipster('methodName or options') where $(sel) matches one or more elements
		else {
			
			// method calls
			if (typeof args[0] === 'string') {
				
				var v = '#*$~&';
				
				this.each(function() {
					
					// retrieve the namepaces of the tooltip(s) that exist on that element. We will interact with the first tooltip only.
					var ns = $(this).data('tooltipster-ns'),
						// self represents the instance of the first tooltipster plugin associated to the current HTML object of the loop
						self = ns ? $(this).data(ns[0]) : null;
					
					// if the current element holds a tooltipster instance
					if (self) {
						
						if (typeof self[args[0]] === 'function') {
							// note : args[1] and args[2] may not be defined
							var resp = self[args[0]](args[1], args[2]);
						}
						else {
							throw new Error('Unknown method .tooltipster("' + args[0] + '")');
						}
						
						// if the function returned anything other than the instance itself (which implies chaining)
						if (resp !== self){
							v = resp;
							// return false to stop .each iteration on the first element matched by the selector
							return false;
						}
					}
					else {
						throw new Error('You called Tooltipster\'s "' + args[0] + '" method on an uninitialized element');
					}
				});
				
				return (v !== '#*$~&') ? v : this;
			}
			// first argument is undefined or an object : the tooltip is initializing
			else {
				
				var instances = [],
					// is there a defined value for the multiple option in the options object ?
					multipleIsSet = args[0] && typeof args[0].multiple !== 'undefined',
					// if the multiple option is set to true, or if it's not defined but set to true in the defaults
					multiple = (multipleIsSet && args[0].multiple) || (!multipleIsSet && defaults.multiple),
					// same for debug
					debugIsSet = args[0] && typeof args[0].debug !== 'undefined',
					debug = (debugIsSet && args[0].debug) || (!debugIsSet && defaults.debug);
				
				// initialize a tooltipster instance for each element if it doesn't already have one or if the multiple option is set, and attach the object to it
				this.each(function () {
					
					var go = false,
						ns = $(this).data('tooltipster-ns'),
						instance = null;
					
					if (!ns) {
						go = true;
					}
					else if (multiple) {
						go = true;
					}
					else if (debug) {
						console.log('Tooltipster: one or more tooltips are already attached to this element: ignoring. Use the "multiple" option to attach more tooltips.');
					}
					
					if (go) {
						instance = new Plugin(this, args[0]);
						
						// save the reference of the new instance
						if (!ns) ns = [];
						ns.push(instance.namespace);
						$(this).data('tooltipster-ns', ns)
						
						// save the instance itself
						$(this).data(instance.namespace, instance);
					}
					
					instances.push(instance);
				});
				
				if (multiple) return instances;
				else return this;
			}
		}
	};
	
	// quick & dirty compare function (not bijective nor multidimensional)
	function areEqual(a,b) {
		var same = true;
		$.each(a, function(i, el){
			if(typeof b[i] === 'undefined' || a[i] !== b[i]){
				same = false;
				return false;
			}
		});
		return same;
	}
	
	// detect if this device can trigger touch events
	var deviceHasTouchCapability = !!('ontouchstart' in window);
	
	// we'll assume the device has no mouse until we detect any mouse movement
	var deviceHasMouse = false;
	$('body').one('mousemove', function() {
		deviceHasMouse = true;
	});
	
	function deviceIsPureTouch() {
		return (!deviceHasMouse && deviceHasTouchCapability);
	}
	
	// detecting support for CSS transitions
	function supportsTransitions() {
		var b = document.body || document.documentElement,
			s = b.style,
			p = 'transition';
		
		if(typeof s[p] == 'string') {return true; }

		v = ['Moz', 'Webkit', 'Khtml', 'O', 'ms'],
		p = p.charAt(0).toUpperCase() + p.substr(1);
		for(var i=0; i<v.length; i++) {
			if(typeof s[v[i] + p] == 'string') { return true; }
		}
		return false;
	}
})( jQuery, window, document );

(function( $ ) {
	'use strict';
	var Obj = {
		// All pages
		'common' : {
			init : function() {
				// JavaScript to be fired on all pages
				$( function() {
					Obj.common.voting();
				} );
			},
			finalize : function() {
				// JavaScript to be fired on all pages, after page specific JS is fired
			},

			voting : function() {
				// Like
				$('a.pakb-like-btn').click(function(){
					var response_div = $(this).parent();
					$.ajax({
						url         : PAKB.base_url,
						data        : {'pakb_vote_like':$(this).attr('post_id')},
						beforeSend  : function(){
						},
						success     : function(data){
							response_div.html(data).fadeIn(900);
						},
						complete    : function(){

						}
					});
				});

				// Dislike
				$('a.pakb-dislike-btn').click(function(){
					var response_div = $(this).parent();
					$.ajax({
						url         : PAKB.base_url,
						data        : {'pakb_vote_dislike':$(this).attr('post_id')},
						beforeSend  : function(){

						},
						success     : function(data){
							response_div.html(data).fadeIn(900);
						},
						complete    : function(){

						}
					});
				});

				// Autosuggest
				//will check if display category is enabled through localize script
				if ( PAKB.category ) {
					$('#kb-s.autosuggest').autocomplete({
						serviceUrl  : PAKB.ajaxurl,
						params      : {'action':'search_title'},
						minChars    : 1,
						maxHeight   : 450,
						groupBy     : 'category',
						preventBadQueries : false,
						onSelect    : function(suggestion) {
							window.location = suggestion.url;
						}
					});
				} else {
					$('#kb-s.autosuggest').autocomplete({
						serviceUrl  : PAKB.ajaxurl,
						params      : {'action':'search_title'},
						minChars    : 1,
						maxHeight   : 450,
						preventBadQueries : false,
						onSelect    : function(suggestion) {
							window.location = suggestion.url;
						}
					});
				}

				$('.pakb-tooltip').tooltipster();

				// Break header out of container
				/*
				var scroll_resize = function(){

					var headerPos = $('.pakb-header').offset(); // returns position
					var windowWidth = jQuery(window).width()
					var widthHeader = parseInt($(".pakb-header").css("width"));
					//var paddingRight = windowWidth - headerPos.left - widthHeader;

					//if ( 767 < windowWidth ) {
						$(".pakb-header-wrapper").css( {"width": windowWidth, "margin-left": - headerPos.left, "padding-left": + headerPos.left, "padding-right": + headerPos.left} );
					//}
					return;
				};

				$(document).ready(scroll_resize);
				$(window).resize(scroll_resize);
				*/

			} // end of voting

		}

	};

	// The routing fires all common scripts, followed by the page specific scripts.
	// Add additional events for more control over timing e.g. a finalize event
	var UTIL = {
		fire : function( func, funcname, args ) {
			var fire;
			var namespace = Obj;
			funcname      = (funcname === undefined) ? 'init' : funcname;
			fire          = func !== '';
			fire          = fire && namespace[ func ];
			fire          = fire && typeof namespace[ func ][ funcname ] === 'function';

			if ( fire ) {
				namespace[ func ][ funcname ]( args );
			}
		},
		loadEvents : function() {
			// Fire common init JS
			UTIL.fire( 'common' );

			// Fire page-specific init JS, and then finalize JS
			$.each( document.body.className.replace( /-/g, '_' ).split( /\s+/ ), function( i, classnm ) {
				UTIL.fire( classnm );
				UTIL.fire( classnm, 'finalize' );
			} );

			// Fire common finalize JS
			UTIL.fire( 'common', 'finalize' );
		}
	};

	// Load Events
	$( document ).ready( UTIL.loadEvents );

})( jQuery ); // Fully reference jQuery after this point.
