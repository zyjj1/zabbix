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
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/


class testWidgets extends CWebTest {
	const HOST_ALL_ITEMS = 'Host for all item value types';
	const TABLE_SELECTOR = 'xpath://form[@name="itemform"]//table';

	/**
	 * Gets widget and widget_field tables to compare hash values, excludes widget_fieldid because it can change.
	 */
	const SQL = 'SELECT wf.widgetid, wf.type, wf.name, wf.value_int, wf.value_str, wf.value_groupid, wf.value_hostid,'.
			' wf.value_itemid, wf.value_graphid, wf.value_sysmapid, w.widgetid, w.dashboard_pageid, w.type, w.name, w.x, w.y,'.
			' w.width, w.height'.
			' FROM widget_field wf'.
			' INNER JOIN widget w'.
			' ON w.widgetid=wf.widgetid'.
			' ORDER BY wf.widgetid, wf.name, wf.value_int, wf.value_str, wf.value_groupid, wf.value_itemid,'.
			' wf.value_graphid, wf.value_hostid';

	/**
	 * Function which checks that only permitted item types are accessible for widgets.
	 *
	 * @param string    $url       url provided which needs to be opened
	 * @param string    $widget    name of widget type
	 */
	public function checkAvailableItems($url, $widget) {
		$this->page->login()->open($url)->waitUntilReady();

		// Open widget form dialog.
		$widget_dialog = CDashboardElement::find()->one()->waitUntilReady()->edit()->addWidget()->asForm();
		$widget_dialog->fill(['Type' => CFormElement::RELOADABLE_FILL($widget)]);

		// Assign the dialog from where the last Select button will be clicked.
		$select_dialog = $widget_dialog;

		// Item types expected in items table. For the most cases theses are all items except of Binary and dependent.
		$item_types = [
			'Character item',
			'Float item',
			'Log item',
			'Text item',
			'Unsigned item',
			'Unsigned_dependent item'
		];

		switch ($widget) {
			case 'Top hosts':
				$widget_dialog->getFieldContainer('Columns')->query('button:Add')->one()->waitUntilClickable()->click();
				$column_dialog = COverlayDialogElement::find()->all()->last()->waitUntilReady();

				// For Top hosts widget final dialog is New column dialog.
				$select_dialog = $column_dialog;
				break;

			case 'Clock':
				$widget_dialog->fill(['Time type' => CFormElement::RELOADABLE_FILL('Host time')]);
				$this->assertTrue($widget_dialog->getField('Item')->isVisible());
				break;

			case 'Graph':
			case 'Gauge':
			case 'Pie chart':
				// For Graph, Gauge and Pie chart only numeric items are available.
				$item_types = ['Float item', 'Unsigned item', 'Unsigned_dependent item'];
				break;

			case 'Graph prototype':
				$widget_dialog->fill(['Source' => 'Simple graph prototype']);
				$this->assertTrue($widget_dialog->getField('Item prototype')->isVisible());

				// For Graph prototype only numeric item prototypes are available.
				$item_types = ['Float item prototype', 'Unsigned item prototype', 'Unsigned_dependent item prototype'];
				break;
		}

		$select_button = ($widget === 'Graph' || $widget === 'Pie chart')
			? 'xpath:(.//button[text()="Select"])[2]'
			: 'button:Select';
		$select_dialog->query($select_button)->one()->waitUntilClickable()->click();

		// Open the dialog where items will be tested.
		$items_dialog = COverlayDialogElement::find()->all()->last()->waitUntilReady();
		$this->assertEquals($widget === 'Graph prototype' ? 'Item prototypes' : 'Items', $items_dialog->getTitle());

		// Find the table where items will be expected.
		$table = $items_dialog->query(self::TABLE_SELECTOR)->asTable()->one()->waitUntilVisible();

		// Fill the host name and check the table.
		$items_dialog->query('class:multiselect-control')->asMultiselect()->one()->fill(self::HOST_ALL_ITEMS);
		$table->waitUntilReloaded();
		$this->assertTableDataColumn($item_types, 'Name', self::TABLE_SELECTOR);
	}

	/**
	 * Replace macro {date} with specified date in YYYY-MM-DD format for specified fields and for item data to be inserted in DB.
	 *
	 * @param array		$data				data provider
	 * @param string	$new_date			dynamic date that is converted into YYYY-MM-DD format and replaces the {date} macro
	 * @param array		$impacted_fields	array of fields that require to replace the {date} macro with a static date
	 *
	 * return array
	 */
	public function replaceDateMacroInData ($data, $new_date, $impacted_fields) {
		$new_date = date('Y-m-d', strtotime($new_date));

		if (array_key_exists('column_fields', $data)) {
			foreach ($data['column_fields'] as &$column) {
				foreach ($impacted_fields as $field) {
					$column[$field] = str_replace('{date}', $new_date, $column[$field]);
				}
			}
		}
		else {
			foreach ($impacted_fields as $field) {
				$data['fields'][$field] = str_replace('{date}', $new_date, $data['fields'][$field]);
			}
		}

		foreach ($data['item_data'] as &$item_value) {
			$item_value['time'] = str_replace('{date}', $new_date, $item_value['time']);
		}
		unset($item_value);

		return $data;
	}
}
