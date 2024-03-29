<?php declare(strict_types = 0);
/*
** Zabbix
** Copyright (C) 2001-2024 Zabbix SIA
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


use Widgets\Item\Widget;

?>

window.widget_item_form = new class {

	#is_item_numeric = false;

	init({thresholds_colors}) {
		this._form = document.getElementById('widget-dialogue-form');

		this._show_description = document.getElementById(`show_${<?= Widget::SHOW_DESCRIPTION ?>}`);
		this._show_value = document.getElementById(`show_${<?= Widget::SHOW_VALUE ?>}`);
		this._show_time = document.getElementById(`show_${<?= Widget::SHOW_TIME ?>}`);
		this._show_change_indicator = document.getElementById(`show_${<?= Widget::SHOW_CHANGE_INDICATOR ?>}`);

		this._units_show = document.getElementById('units_show');

		jQuery('#itemid').on('change', () => {
			this.#promiseGetItemType()
				.then((type) => {
					if (this._form.isConnected) {
						this.#is_item_numeric = type !== null && this.#isItemValueTypeNumeric(type);
						this.updateForm();
					}
				});
		});

		for (const colorpicker of this._form.querySelectorAll('.<?= ZBX_STYLE_COLOR_PICKER ?> input')) {
			$(colorpicker).colorpicker({
				appendTo: ".overlay-dialogue-body",
				use_default: !colorpicker.name.includes('thresholds'),
				onUpdate: ['up_color', 'down_color', 'updown_color'].includes(colorpicker.name)
					? (color) => this.setIndicatorColor(colorpicker.name, color)
					: null
			});
		}

		const show = [this._show_description, this._show_value, this._show_time, this._show_change_indicator];

		for (const checkbox of show) {
			checkbox.addEventListener('change', () => this.updateForm());
		}

		document.getElementById('units_show').addEventListener('change', () => this.updateForm());
		document.getElementById('aggregate_function').addEventListener('change', () => this.updateForm());
		document.getElementById('history').addEventListener('change', () => this.updateForm());

		colorPalette.setThemeColors(thresholds_colors);

		this.updateForm();

		this.#promiseGetItemType()
			.then((type) => {
				if (this._form.isConnected) {
					this.#is_item_numeric = type !== null && this.#isItemValueTypeNumeric(type);
					this.updateForm();
				}
			});
	}

	updateForm() {
		const aggregate_function = document.getElementById('aggregate_function');

		for (const element of this._form.querySelectorAll('.fields-group-description')) {
			element.style.display = this._show_description.checked ? '' : 'none';

			for (const input of element.querySelectorAll('input, textarea')) {
				input.disabled = !this._show_description.checked;
			}
		}

		for (const element of this._form.querySelectorAll('.fields-group-value')) {
			element.style.display = this._show_value.checked ? '' : 'none';

			for (const input of element.querySelectorAll('input')) {
				input.disabled = !this._show_value.checked;
			}
		}

		for (const element of document.querySelectorAll('#units, #units_pos, #units_size, #units_bold, #units_color')) {
			element.disabled = !this._show_value.checked || !document.getElementById('units_show').checked;
		}

		for (const element of this._form.querySelectorAll('.fields-group-time')) {
			element.style.display = this._show_time.checked ? '' : 'none';

			for (const input of element.querySelectorAll('input')) {
				input.disabled = !this._show_time.checked;
			}
		}

		for (const element of this._form.querySelectorAll('.fields-group-change-indicator')) {
			element.style.display = this._show_change_indicator.checked ? '' : 'none';

			for (const input of element.querySelectorAll('input')) {
				input.disabled = !this._show_change_indicator.checked;
			}
		}

		this._form.fields.time_period.hidden = aggregate_function.value == <?= AGGREGATE_NONE ?>;

		const aggregate_warning_functions = [<?= AGGREGATE_AVG ?>, <?= AGGREGATE_MIN ?>, <?= AGGREGATE_MAX ?>,
			<?= AGGREGATE_SUM ?>
		];

		const history_data_trends = document.querySelector('#history input[name="history"]:checked')
			.value == <?= Widget::HISTORY_DATA_TRENDS ?>;

		document.getElementById('item-history-data-warning').style.display =
				history_data_trends && !this.#is_item_numeric
			? ''
			: 'none';

		document.getElementById('item-aggregate-function-warning').style.display =
				aggregate_warning_functions.includes(parseInt(aggregate_function.value)) && !this.#is_item_numeric
			? ''
			: 'none';

		document.getElementById('item-thresholds-warning').style.display = this.#is_item_numeric ? 'none' : '';
	}

	/**
	 * Fetch type of currently selected item.
	 *
	 * @return {Promise<any>}  Resolved promise will contain item type, or null in case of error or if no item is
	 *                         currently selected.
	 */
	#promiseGetItemType() {
		const ms_item_data = $('#itemid').multiSelect('getData');

		if (ms_item_data.length == 0) {
			return Promise.resolve(null);
		}

		const curl = new Curl('jsrpc.php');

		curl.setArgument('method', 'item_value_type.get');
		curl.setArgument('type', <?= PAGE_TYPE_TEXT_RETURN_JSON ?>);
		curl.setArgument('itemid', ms_item_data[0].id);

		return fetch(curl.getUrl())
			.then((response) => response.json())
			.then((response) => {
				if ('error' in response) {
					throw {error: response.error};
				}

				return parseInt(response.result);
			})
			.catch((exception) => {
				console.log('Could not get item type', exception);

				return null;
			});
	}

	/**
	 * Check if item value type is numeric.
	 *
	 * @param {int} type  Item value type.
	 */
	#isItemValueTypeNumeric(type) {
		return type == <?= ITEM_VALUE_TYPE_FLOAT ?> || type == <?= ITEM_VALUE_TYPE_UINT64 ?>;
	}

	/**
	 * Set color of the specified indicator.
	 *
	 * @param {string} name   Indicator name.
	 * @param {string} color  Color number.
	 */
	setIndicatorColor(name, color) {
		const indicator_ids = {
			up_color: 'change-indicator-up',
			down_color: 'change-indicator-down',
			updown_color: 'change-indicator-updown'
		};

		document.getElementById(indicator_ids[name])
			.querySelector("polygon").style.fill = (color !== '') ? `#${color}` : '';
	}
};
