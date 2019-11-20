/*
 ** Zabbix
 ** Copyright (C) 2001-2019 Zabbix SIA
 **
 ** This program is free software; you can redistribute it and/or modify
 ** it under the terms of the GNU General Public License as published by
 ** the Free Software Foundation; either version 2 of the License, or
 ** (at your option) any later version.
 **
 ** This program is distributed in the hope that it will be useful,
 ** but WITHOUT ANY WARRANTY; without even the implied warranty of
 ** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 ** GNU General Public License for more details.
 **
 ** You should have received a copy of the GNU General Public License
 ** along with this program; if not, write to the Free Software
 ** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 **/


jQuery(function($) {
	'use strict';

	/**
	 * Object that sends ajax request for server status and show/hide warning messages.
	 *
	 * @type {Object}
	 */
	var checker = {
		elem: document.querySelector('.msg-global-footer'),
		delay: 5000, // 5 seconds
		timeout: 10000, // 10 seconds
		is_show: false,
		last_response: null,

		/**
		 * Sends ajax request to get Zabbix server availability and message to show if server is not available.
		 *
		 * @param {boolean} nocache  Add 'nocache' parameter to get result not from cache.
		 */
		call: function(nocache) {
			var params = nocache ? {nocache: true} : {};
			new RPC.Call({
				'method': 'zabbix.status',
				'params': params,
				'onSuccess': $.proxy(this.onSuccess, this)
			});
		},

		/**
		 * Parse ajax responses and show / hide warning message.
		 *
		 * @param {object} response  Ajax response.
		 */
		onSuccess: function(response) {
			if (response.result === this.last_response) {
				return false;
			}

			this.last_response = response.result;

			if (response.result) {
				return this.hide();
			}

			this.setMessage(response.message);
			return this.show();
		},

		/**
		 * Start server status checks with 5 sec delay after page is loaded.
		 */
		init: function() {
			return window.setTimeout(function() {
				// Looping function that check for server status every 10 seconds.
				return window.setInterval(function() {
					checker.call(true);
				}, checker.timeout);
			}, this.delay);
		},

		/**
		 * Set warning message.
		 *
		 * @param {string} message  Warning message.
		 */
		setMessage: function(message) {
			this.elem.innerText = message;
		},

		/**
		 * Show warning message.
		 */
		show: function() {
			if (this.is_show || this.last_response) {
				return false;
			}

			this.is_show = true;

			var value_opacity = 0;

			this.elem.style.opacity = value_opacity;
			this.elem.style.display = 'flex';

			(function anim() {
				if (!checker.is_show) {
					return false;
				}

				checker.elem.style.opacity = (value_opacity += .1);
				if (checker.elem.style.opacity < 1) {
					requestAnimationFrame(anim);
				}
				else {
					checker.elem.style.removeProperty('opacity');
				}
			})();
		},

		/**
		 * Hide warning message.
		 */
		hide: function() {
			if (!this.is_show) {
				return false;
			}

			this.is_show = false;

			var value_opacity = 1;

			this.elem.style.opacity = value_opacity;
			this.elem.style.display = 'flex';

			(function anim() {
				if (checker.is_show) {
					return false;
				}

				checker.elem.style.opacity = (value_opacity -= .1);
				if (checker.elem.style.opacity < 0.1) {
					checker.elem.style.display = 'none';
					checker.elem.style.removeProperty('opacity');
				}
				else {
					requestAnimationFrame(anim);
				}
			})();
		}
	};

	checker.init();

	// Event that hide warning message when mouse hover it.
	$(checker.elem).on('mouseenter', function() {
		var $obj = $(this),
			offset = $obj.offset(),
			x1 = Math.floor(offset.left),
			x2 = x1 + $obj.outerWidth(),
			y1 = Math.floor(offset.top),
			y2 = y1 + $obj.outerHeight();

		checker.hide();

		$(document).on('mousemove.messagehide', function(e) {
			if (e.pageX < x1 || e.pageX > x2 || e.pageY < y1 || e.pageY > y2) {
				checker.show();
				$(document).off('mousemove.messagehide');
				$(document).off('mouseleave.messagehide');
			}
		});
		$(document).on('mouseleave.messagehide', function() {
			checker.show();
			$(document).off('mouseleave.messagehide');
			$(document).off('mousemove.messagehide');
		});
	});
});
