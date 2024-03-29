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

use Zabbix\Widgets\Fields\CWidgetFieldColumnsList;

$form = (new CForm())
	->setName('tophosts_column')
	->addStyle('display: none;')
	->addVar('action', $data['action'])
	->addVar('update', 1);

// Enable form submitting on Enter.
$form->addItem((new CSubmitButton())->addClass(ZBX_STYLE_FORM_SUBMIT_HIDDEN));

$form_grid = new CFormGrid();

$scripts = [];

if (array_key_exists('edit', $data)) {
	$form->addVar('edit', 1);
}

// Name.
$form_grid->addItem([
	(new CLabel(_('Name'), 'column_name'))->setAsteriskMark(),
	new CFormField(
		(new CTextBox('name', $data['name'], false))
			->setId('column_name')
			->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
			->setAttribute('autofocus', 'autofocus')
			->setAriaRequired()
	)
]);

// Data.
$form_grid->addItem([
	new CLabel(_('Data'), 'data'),
	new CFormField(
		(new CSelect('data'))
			->setValue($data['data'])
			->addOptions(CSelect::createOptionsFromArray([
				CWidgetFieldColumnsList::DATA_ITEM_VALUE => _('Item value'),
				CWidgetFieldColumnsList::DATA_HOST_NAME => _('Host name'),
				CWidgetFieldColumnsList::DATA_TEXT => _('Text')
			]))
			->setFocusableElementId('data')
	)
]);

// Static text.
$form_grid->addItem([
	(new CLabel(_('Text'), 'text'))->setAsteriskMark(),
	new CFormField(
		(new CTextBox('text', $data['text']))
			->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH)
			->setAttribute('placeholder', _('Text, supports {INVENTORY.*}, {HOST.*} macros'))
	)
]);

// Item.
$parameters = [
	'srctbl' => 'items',
	'srcfld1' => 'itemid',
	'dstfrm' => $form->getName(),
	'dstfld1' => 'item',
	'value_types' => [
		ITEM_VALUE_TYPE_FLOAT,
		ITEM_VALUE_TYPE_STR,
		ITEM_VALUE_TYPE_LOG,
		ITEM_VALUE_TYPE_UINT64,
		ITEM_VALUE_TYPE_TEXT
	]
];

if ($data['templateid'] === '') {
	$parameters['real_hosts'] = 1;
	$parameters['resolve_macros'] = 1;
}
else {
	$parameters += [
		'hostid' => $data['templateid'],
		'hide_host_filter' => true
	];
}

$item_select = (new CPatternSelect([
	'name' => 'item',
	'object_name' => 'items',
	'data' => $data['item'] === '' ? '' : [$data['item']],
	'multiple' => false,
	'popup' => [
		'parameters' => $parameters
	],
	'add_post_js' => false
]))->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH);

$scripts[] = $item_select->getPostJS();

$form_grid->addItem([
	(new CLabel(_('Item'), 'item_ms'))->setAsteriskMark(),
	new CFormField($item_select)
]);

// Display.
$form_grid->addItem([
	new CLabel(
		[
			_('Display'),
			(makeWarningIcon(_('With this setting only numeric data will be displayed.')))
				->setId('tophosts-column-display-warning')
		],
		'display'
	),
	new CFormField(
		(new CRadioButtonList('display', (int) $data['display']))
			->addValue(_('As is'), CWidgetFieldColumnsList::DISPLAY_AS_IS)
			->addValue(_('Bar'), CWidgetFieldColumnsList::DISPLAY_BAR)
			->addValue(_('Indicators'), CWidgetFieldColumnsList::DISPLAY_INDICATORS)
			->setModern()
	)
]);

// Min value.
$form_grid->addItem([
	new CLabel(_('Min'), 'min'),
	new CFormField(
		(new CTextBox('min', $data['min']))
			->setWidth(ZBX_TEXTAREA_FILTER_SMALL_WIDTH)
			->setAttribute('placeholder', _('calculated'))
	)
]);

// Max value.
$form_grid->addItem([
	new CLabel(_('Max'), 'max'),
	new CFormField(
		(new CTextBox('max', $data['max']))
			->setWidth(ZBX_TEXTAREA_FILTER_SMALL_WIDTH)
			->setAttribute('placeholder', _('calculated'))
	)
]);

// Base color.
$form_grid->addItem([
	new CLabel(_('Base color'), 'lbl_base_color'),
	new CFormField(new CColor('base_color', $data['base_color']))
]);

// Thresholds table.
$header_row = [
	'',
	(new CColHeader(_('Threshold')))->setWidth('100%'),
	_('Action')
];

$thresholds = (new CDiv(
	(new CTable())
		->setId('thresholds_table')
		->addClass(ZBX_STYLE_TABLE_FORMS)
		->setHeader($header_row)
		->setFooter(new CRow(
			(new CCol(
				(new CButtonLink(_('Add')))->addClass('element-table-add')
			))->setColSpan(count($header_row))
		))
))
	->addClass('table-forms-separator')
	->setWidth(ZBX_TEXTAREA_STANDARD_WIDTH);

$thresholds->addItem(
	(new CTemplateTag('thresholds-row-tmpl'))
		->addItem((new CRow([
			(new CColor('thresholds[#{rowNum}][color]', '#{color}'))->appendColorPickerJs(false),
			(new CTextBox('thresholds[#{rowNum}][threshold]', '#{threshold}', false))
				->setWidth(ZBX_TEXTAREA_TINY_WIDTH)
				->setAriaRequired(),
			(new CButton('thresholds[#{rowNum}][remove]', _('Remove')))
				->addClass(ZBX_STYLE_BTN_LINK)
				->addClass('element-table-remove')
		]))->addClass('form_row'))
);

$form_grid->addItem([
	new CLabel([
		_('Thresholds'),
		makeWarningIcon(_('This setting applies only to numeric data.'))
	], 'thresholds_table'),
	new CFormField($thresholds)
]);

// Decimal places.
$form_grid->addItem([
	new CLabel(_('Decimal places'), 'decimal_places'),
	(new CFormField(
		(new CNumericBox('decimal_places', $data['decimal_places'], 2))->setWidth(ZBX_TEXTAREA_NUMERIC_STANDARD_WIDTH)
	))
]);

// Aggregation function.
$form_grid->addItem([
	new CLabel(
		[
			_('Aggregation function'),
			(makeWarningIcon(_('With this setting only numeric items will be displayed.')))
				->setId('tophosts-column-aggregate-function-warning')
		],
		'column_aggregate_function'
	),
	new CFormField(
		(new CSelect('aggregate_function'))
			->setId('aggregate_function')
			->setValue($data['aggregate_function'])
			->addOptions(CSelect::createOptionsFromArray([
				AGGREGATE_NONE => CItemHelper::getAggregateFunctionName(AGGREGATE_NONE),
				AGGREGATE_MIN => CItemHelper::getAggregateFunctionName(AGGREGATE_MIN),
				AGGREGATE_MAX => CItemHelper::getAggregateFunctionName(AGGREGATE_MAX),
				AGGREGATE_AVG => CItemHelper::getAggregateFunctionName(AGGREGATE_AVG),
				AGGREGATE_COUNT => CItemHelper::getAggregateFunctionName(AGGREGATE_COUNT),
				AGGREGATE_SUM => CItemHelper::getAggregateFunctionName(AGGREGATE_SUM),
				AGGREGATE_FIRST => CItemHelper::getAggregateFunctionName(AGGREGATE_FIRST),
				AGGREGATE_LAST => CItemHelper::getAggregateFunctionName(AGGREGATE_LAST)
			]))
			->setFocusableElementId('column_aggregate_function')
	)
]);

$time_period_field_view = (new CWidgetFieldTimePeriodView($data['time_period_field']))
	->setDateFormat(ZBX_FULL_DATE_TIME)
	->setFromPlaceholder(_('YYYY-MM-DD hh:mm:ss'))
	->setToPlaceholder(_('YYYY-MM-DD hh:mm:ss'))
	->setFormName('tophosts_column')
	->addClass('js-time-period');

foreach ($time_period_field_view->getViewCollection() as ['label' => $label, 'view' => $view, 'class' => $class]) {
	$form_grid->addItem([
		$label,
		(new CFormField($view))->addClass($class)
	]);
}

$form_grid->addItem(new CScriptTag([
	'document.forms.tophosts_column.fields = {};',
	$time_period_field_view->getJavaScript()
]));

// History data.
$form_grid->addItem([
	new CLabel(
		[
			_('History data'),
			(makeWarningIcon(
				_('This setting applies only to numeric data. Non-numeric data will always be taken from history.')
			))->setId('tophosts-column-history-data-warning')
		],
		'history'
	),
	new CFormField(
		(new CRadioButtonList('history', (int) $data['history']))
			->addValue(_('Auto'), CWidgetFieldColumnsList::HISTORY_DATA_AUTO)
			->addValue(_('History'), CWidgetFieldColumnsList::HISTORY_DATA_HISTORY)
			->addValue(_('Trends'), CWidgetFieldColumnsList::HISTORY_DATA_TRENDS)
			->setModern()
	)
]);

$form
	->addItem($form_grid)
	->addItem(
		(new CScriptTag('
			tophosts_column_edit_form.init('.json_encode([
				'form_name' => $form->getName(),
				'thresholds' => $data['thresholds'],
				'thresholds_colors' => $data['thresholds_colors']
			], JSON_THROW_ON_ERROR).');
		'))->setOnDocumentReady()
	);

$output = [
	'header'		=> array_key_exists('edit', $data) ? _('Update column') : _('New column'),
	'script_inline'	=> implode('', $scripts).$this->readJsFile('column.edit.js.php', null, ''),
	'body'			=> $form->toString(),
	'buttons'		=> [
		[
			'title'		=> array_key_exists('edit', $data) ? _('Update') : _('Add'),
			'keepOpen'	=> true,
			'isSubmit'	=> true,
			'action'	=> '$(document.forms.tophosts_column).trigger("process.form", [overlay])'
		]
	]
];

if ($data['user']['debug_mode'] == GROUP_DEBUG_MODE_ENABLED) {
	CProfiler::getInstance()->stop();
	$output['debug'] = CProfiler::getInstance()->make()->toString();
}

echo json_encode($output, JSON_THROW_ON_ERROR);
