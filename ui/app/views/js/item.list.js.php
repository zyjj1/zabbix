<?php
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


/**
 * @var CView $this
 * @var array $data
 */

?>
<script type="text/x-jquery-tmpl" id="filter-tag-row-tmpl">
	<?= CTagFilterFieldHelper::getTemplate() ?>
</script>

<script>
	const view = new class {

		init({context, confirm_messages, field_switches, form_name, hostids, token}) {
			this.confirm_messages = confirm_messages;
			this.token = token;
			this.context = context;
			this.hostids = hostids;

			this.form = document.forms[form_name];
			this.filter_form = document.querySelector('form[name="zbx_filter"]');

			this.initForm(field_switches);
			this.initEvents();
		}

		initForm(field_switches) {
			new CViewSwitcher('filter_type', 'change', field_switches.for_type);

			$('#filter-tags')
				.dynamicRows({template: '#filter-tag-row-tmpl'})
				.on('afteradd.dynamicRows', function() {
					const rows = this.querySelectorAll('.form_row');

					new CTagFilterItem(rows[rows.length - 1]);
				});

			document.querySelectorAll('#filter-tags .form_row').forEach(row => {
				new CTagFilterItem(row);
			});
		}

		initEvents() {
			this.filter_form.addEventListener('click', e => {
				const target = e.target;

				if (target.matches('.link-action') && target.closest('.subfilter') !== null) {
					const subfilter = target.closest('.subfilter');

					if (subfilter.matches('.subfilter-enabled')) {
						subfilter.querySelector('input[type="hidden"]').remove();
						this.filter_form.submit();
					}
					else {
						const name = target.getAttribute('data-name');
						const value = target.getAttribute('data-value');

						subfilter.classList.add('subfilter-enabled');
						submitFormWithParam('zbx_filter', name, value);
					}
				}
				else if (target.matches('[name="filter_state"]')) {
					const disabled = e.target.getAttribute('value') != -1;

					this.filter_form.querySelectorAll('input[name=filter_status]').forEach(checkbox => {
						checkbox.toggleAttribute('disabled', disabled);
					});
				}
			});
			this.form.addEventListener('click', (e) => {
				const target = e.target;
				const itemids = Object.keys(chkbxRange.getSelectedIds());

				if (target.classList.contains('js-update-item')) {
					this.#edit(target, {itemid: target.dataset.itemid, context: this.context});
				}
				else if (target.classList.contains('js-enable-item')) {
					this.#enable(null, {itemids: [target.dataset.itemid], context: this.context});
				}
				else if (target.classList.contains('js-disable-item')) {
					this.#disable(null, {itemids: [target.dataset.itemid], context: this.context});
				}
				else if (target.classList.contains('js-massenable-item')) {
					this.#enable(target, {itemids: itemids, context: this.context});
				}
				else if (target.classList.contains('js-massdisable-item')) {
					this.#disable(target, {itemids: itemids, context: this.context});
				}
				else if (target.classList.contains('js-massexecute-item')) {
					this.#execute(target, {itemids: itemids, context: this.context});
				}
				else if (target.classList.contains('js-massclearhistory-item')) {
					this.#clear(target, {itemids: itemids, context: this.context});
				}
				else if (target.classList.contains('js-masscopy-item')) {
					this.#copy(target, itemids);
				}
				else if (target.classList.contains('js-massupdate-item')) {
					this.#massupdate(target, {ids: itemids, context: this.context});
				}
				else if (target.classList.contains('js-massdelete-item')) {
					this.#delete(target, {itemids: itemids, context: this.context});
				}
			});
			document.addEventListener('click', e => {
				const target = e.target;

				if (target.classList.contains('js-create-item')) {
					this.#edit(target, target.dataset);
				}
				else if (target.classList.contains('js-trigger-edit')) {
					this.editTrigger(target.dataset);
				}
			});
		}

		editItem(target, data) {
			this.#edit(target, data);
		}

		editTrigger(trigger_data) {
			clearMessages();

			const overlay = PopUp('trigger.edit', trigger_data, {
				dialogueid: 'trigger-edit',
				dialogue_class: 'modal-popup-large',
				prevent_navigation: true
			});

			overlay.$dialogue[0].addEventListener('dialogue.submit', this.elementSuccess.bind(this), {once: true});
		}

		executeNow(target, data) {
			const curl = new Curl('zabbix.php');

			curl.setArgument('action', 'item.execute');
			this.#post(curl, data);
		}

		editHost(e, hostid) {
			e.preventDefault();
			const host_data = {hostid};

			this.openHostPopup(host_data);
		}

		editTemplate(e, templateid) {
			e.preventDefault();
			const template_data = {templateid};

			this.openTemplatePopup(template_data);
		}

		openHostPopup(host_data) {
			let original_url = location.href;
			const overlay = PopUp('popup.host.edit', host_data, {
				dialogueid: 'host_edit',
				dialogue_class: 'modal-popup-large',
				prevent_navigation: true
			});

			overlay.$dialogue[0].addEventListener('dialogue.submit', e => {
				history.replaceState({}, '', original_url);
				this.elementSuccess(e);
			}, {once: true});

			overlay.$dialogue[0].addEventListener('dialogue.close', e => history.replaceState({}, '', original_url));
		}

		openTemplatePopup(template_data) {
			const overlay =  PopUp('template.edit', template_data, {
				dialogueid: 'templates-form',
				dialogue_class: 'modal-popup-large',
				prevent_navigation: true
			});

			overlay.$dialogue[0].addEventListener('dialogue.submit', this.elementSuccess.bind(this), {once: true});
		}

		#edit(target, parameters = {}) {
			const overlay = PopUp('item.edit', parameters, {
				dialogueid: 'item-edit',
				dialogue_class: 'modal-popup-large',
				trigger_element: target
			});

			overlay.$dialogue[0].addEventListener('dialogue.submit', this.elementSuccess.bind(this), {once: true});
		}

		#enable(target, parameters) {
			const curl = new Curl('zabbix.php');
			curl.setArgument('action', 'item.enable');

			if (target !== null) {
				this.#confirmAction(curl, parameters, target);
			}
			else {
				this.#post(curl, parameters);
			}
		}

		#disable(target, parameters) {
			const curl = new Curl('zabbix.php');
			curl.setArgument('action', 'item.disable');

			if (target !== null) {
				this.#confirmAction(curl, parameters, target);
			}
			else {
				this.#post(curl, parameters);
			}
		}

		#execute(target, parameters) {
			const curl = new Curl('zabbix.php');
			curl.setArgument('action', 'item.execute');

			this.#confirmAction(curl, parameters, target);
		}

		#clear(target, parameters) {
			const curl = new Curl('zabbix.php');
			curl.setArgument('action', 'item.clear');

			this.#confirmAction(curl, parameters, target);
		}

		#copy(target, itemids) {
			const overlay = PopUp('copy.edit', {source: 'items', itemids}, {
				dialogueid: 'copy',
				dialogue_class: 'modal-popup-static',
				trigger_element: target
			});

			overlay.$dialogue[0].addEventListener('dialogue.submit', this.elementSuccess.bind(this), {once: true});
		}

		#massupdate(target, parameters) {
			const overlay = PopUp('item.massupdate', {...this.token, ...parameters, prototype: 0}, {
				dialogue_class: 'modal-popup-preprocessing',
				trigger_element: target
			});

			overlay.$dialogue[0].addEventListener('dialogue.submit',
				e => this.elementSuccess('title' in e.detail ? {detail: {success: e.detail}} : e),
				{once: true}
			);
		}

		#delete(target, parameters) {
			const curl = new Curl('zabbix.php');
			curl.setArgument('action', 'item.delete');

			this.#confirmAction(curl, parameters, target);
		}

		#confirmAction(curl, data, target) {
			const confirm = this.confirm_messages[curl.getArgument('action')];
			const message = confirm ? confirm[data.itemids.length > 1 ? 1 : 0] : '';

			if (message != '' && !window.confirm(message)) {
				return;
			}

			target.classList.add('is-loading');
			this.#post(curl, data)
				.finally(() => {
					target.classList.remove('is-loading');
					target.blur();
				});
		}

		#post(curl, data) {
			const action = curl.getArgument('action');

			return fetch(curl.getUrl(), {
				method: 'POST',
				headers: {'Content-Type': 'application/json'},
				body: JSON.stringify({...this.token, ...data})
			})
				.then((response) => response.json())
				.then((response) => this.elementSuccess({detail: {action, ...response}}))
				.catch(() => {
					clearMessages();

					const message_box = makeMessageBox('bad', [t('Unexpected server error.')]);

					addMessage(message_box);
				});
		}

		elementSuccess(e) {
			let new_href = location.href;
			const response = e.detail;

			if ('error' in response) {
				if ('title' in response.error) {
					postMessageError(response.error.title);
				}

				postMessageDetails('error', response.error.messages);
			}
			else if ('success' in response) {
				chkbxRange.clearSelectedOnFilterChange();
				postMessageOk(response.success.title);

				if ('messages' in response.success) {
					postMessageDetails('success', response.success.messages);
				}

				if (response.success.action === 'delete' && response.action !== 'item.delete') {
					// Items template or host were removed, redirect to list of items.
					let list_url = new Curl('zabbix.php');

					list_url.setArgument('action', 'item.list');
					list_url.setArgument('context', this.context);
					list_url.setArgument('filter_set', 1);
					new_href = list_url.getUrl();
				}
			}

			location.href = new_href;
		}
	};
</script>
