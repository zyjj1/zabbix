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


class CWidgetProblemsBySv extends CWidget {

	static SHOW_GROUPS = 0;
	static SHOW_TOTALS = 1;

	onStart() {
		this._events = {
			...this._events,

			acknowledgeCreated: (e, response) => {
				clearMessages();
				addMessage(makeMessageBox('good', [], response.success.title));

				if (this._state === WIDGET_STATE_ACTIVE) {
					this._startUpdating();
				}
			}
		}
	}

	onActivate() {
		$.subscribe('acknowledge.create', this._events.acknowledgeCreated);
	}

	onDeactivate() {
		$.unsubscribe('acknowledge.create', this._events.acknowledgeCreated);
	}

	hasPadding() {
		return this._view_mode === ZBX_WIDGET_VIEW_MODE_NORMAL
			&& this._fields.show_type !== CWidgetProblemsBySv.SHOW_TOTALS;
	}
}
