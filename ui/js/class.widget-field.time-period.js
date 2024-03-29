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

class CWidgetFieldTimePeriod {

	static DATA_SOURCE_DEFAULT = 0;
	static DATA_SOURCE_WIDGET = 1;
	static DATA_SOURCE_DASHBOARD = 2;

	/**
	 * Multiselect jQuery element.
	 *
	 * @type {Object}
	 */
	#reference_multiselect = null;

	/**
	 * @type {HTMLInputElement}
	 */
	#date_from_input;

	/**
	 * @type {HTMLInputElement}
	 */
	#date_to_input;

	/**
	 * @type {string}
	 */
	#field_name;

	/**
	 * @type {Object}
	 */
	#field_value = null;

	/**
	 * Data type accepted from referred data sources.
	 *
	 * @type {string}
	 */
	#in_type;

	/**
	 * @type {number}
	 */
	#data_source;

	/**
	 * @type {boolean}
	 */
	#widget_accepted;

	/**
	 * @type {boolean}
	 */
	#dashboard_accepted;

	/**
	 * @type {boolean}
	 */
	#is_disabled = false;

	/**
	 * @type {boolean}
	 */
	#is_hidden = false;

	constructor({
		field_name,
		field_value = {from: '', to: ''},
		in_type,
		widget_accepted = false,
		dashboard_accepted = false,
		data_source = CWidgetFieldTimePeriod.DATA_SOURCE_DEFAULT
	}) {
		this.#field_name = field_name;
		this.#in_type = in_type;
		this.#data_source = data_source;
		this.#widget_accepted = widget_accepted;
		this.#dashboard_accepted = dashboard_accepted;

		this.#initField();
		this.#registerEvents();

		this.value = field_value;
	}

	get value() {
		return this.#field_value;
	}

	set value(value) {
		this.#field_value = value;

		if (CWidgetBase.FOREIGN_REFERENCE_KEY in value) {
			const {reference} = CWidgetBase.parseTypedReference(value[CWidgetBase.FOREIGN_REFERENCE_KEY]);

			if (reference === CDashboard.REFERENCE_DASHBOARD) {
				this.#data_source = CWidgetFieldTimePeriod.DATA_SOURCE_DASHBOARD;
			}
			else {
				this.#data_source = CWidgetFieldTimePeriod.DATA_SOURCE_WIDGET;
			}

			this.#selectTypedReference(value[CWidgetBase.FOREIGN_REFERENCE_KEY]);
		}
		else {
			this.#data_source = CWidgetFieldTimePeriod.DATA_SOURCE_DEFAULT;
			this.#date_from_input.value = value.from;
			this.#date_to_input.value = value.to;
		}

		this.#updateField();
	}

	get disabled() {
		return this.#is_disabled;
	}

	set disabled(is_disabled) {
		this.#is_disabled = is_disabled;

		this.#updateField();
	}

	get hidden() {
		return this.#is_hidden;
	}

	set hidden(is_hidden) {
		this.#is_hidden = is_hidden;

		this.#updateField();
	}

	#initField() {
		if (this.#widget_accepted) {
			const $multiselect = jQuery(`#${this.#field_name}_reference`);

			$multiselect[0].dataset.params = JSON.stringify({
				name: `${this.#field_name}[${CWidgetBase.FOREIGN_REFERENCE_KEY}]`,
				selectedLimit: 1,
				custom_select: true
			});

			this.#reference_multiselect = $multiselect.multiSelect();

			this.#reference_multiselect
				.multiSelect('setCustomSuggestList', () => this.#getSuggestedList());

			this.#reference_multiselect
				.multiSelect('customSuggestSelectHandler', (entity) => this.#selectTypedReference(entity.id));

			this.#reference_multiselect
				.multiSelect('getSelectButton').addEventListener('click', () => {
					const popup = new ClassWidgetSelectPopup(this.#getWidgets());

					popup.on('dialogue.submit', (e) => {
						this.#selectTypedReference(e.detail.reference);
					});
				});
		}

		this.#date_from_input = document.getElementById(`${this.#field_name}_from`);
		this.#date_to_input = document.getElementById(`${this.#field_name}_to`);
	}

	#registerEvents() {
		for (const radio of document.querySelectorAll(`[name="${this.#field_name}[data_source]"]`)) {
			radio.addEventListener('change', (e) => {
				this.#data_source = e.target.value;
				this.#updateField();
			});
		}
	}

	#updateField() {
		for (const element of document.querySelectorAll(`.js-${this.#field_name}-data-source`)) {
			element.style.display = this.#is_hidden ? 'none' : '';
		}

		for (const element of document.querySelectorAll(`[name="${this.#field_name}[data_source]"]`)) {
			element.checked = element.value == this.#data_source;
			element.disabled = this.#is_hidden || this.#is_disabled;
		}

		const reference_dashboard = document.getElementById(`${this.#field_name}_reference_dashboard`);

		if (reference_dashboard !== null) {
			reference_dashboard.disabled = this.#is_hidden || this.#is_disabled
				|| this.#data_source != CWidgetFieldTimePeriod.DATA_SOURCE_DASHBOARD;
		}

		for (const element of document.querySelectorAll(`.js-${this.#field_name}-reference`)) {
			element.style.display = this.#is_hidden || this.#data_source != CWidgetFieldTimePeriod.DATA_SOURCE_WIDGET
				? 'none'
				: '';
		}

		if (this.#widget_accepted) {
			if (!this.#is_hidden && !this.#is_disabled
					&& this.#data_source == CWidgetFieldTimePeriod.DATA_SOURCE_WIDGET) {
				this.#reference_multiselect.multiSelect('enable');
			}
			else {
				this.#reference_multiselect.multiSelect('disable');
			}
		}

		const date_picker_element_ids = [
			`${this.#field_name}_from`,
			`${this.#field_name}_from_calendar`,
			`${this.#field_name}_to`,
			`${this.#field_name}_to_calendar`
		];

		for (const element_id of date_picker_element_ids) {
			const element = document.getElementById(element_id);

			if (element !== null) {
				element.disabled = this.#is_hidden || this.#is_disabled
					|| this.#data_source != CWidgetFieldTimePeriod.DATA_SOURCE_DEFAULT;
			}
		}

		const date_picker_form_rows = `.js-${this.#field_name}-from, .js-${this.#field_name}-to`;

		for (const element of document.querySelectorAll(date_picker_form_rows)) {
			element.style.display = this.#is_hidden || this.#data_source != CWidgetFieldTimePeriod.DATA_SOURCE_DEFAULT
				? 'none'
				: '';
		}
	}

	#getSuggestedList() {
		const search = this.#reference_multiselect.multiSelect('getSearch');
		const result_entities = new Map();

		for (const widget of this.#getWidgets()) {
			if (widget.name.toLowerCase().includes(search)) {
				result_entities.set(widget.id, widget);
			}
		}

		return result_entities;
	}

	#selectTypedReference(typed_reference) {
		for (const widget of this.#getWidgets()) {
			if (widget.id === typed_reference) {
				this.#reference_multiselect.multiSelect('addData', [widget]);
				break;
			}
		}
	}

	#getWidgets() {
		const widgets = ZABBIX.Dashboard.getReferableWidgets({
			type: this.#in_type,
			widget_context: ZABBIX.Dashboard.getEditingWidgetContext()
		});

		const result = [];

		for (const widget of widgets) {
			result.push({
				id: CWidgetBase.createTypedReference({reference: widget.getFields().reference, type: this.#in_type}),
				name: widget.getHeaderName()
			});
		}

		return result;
	}
}
