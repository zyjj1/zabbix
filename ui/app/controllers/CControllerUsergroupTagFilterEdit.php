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


class CControllerUsergroupTagFilterEdit extends CController {

	protected function init() {
		$this->disableCsrfValidation();
	}

	protected function checkInput() {
		$fields = [
			'edit' =>			'in 1,0',
			'groupid' =>		'db hosts_groups.groupid',
			'name' =>			'string',
			'tag_filters' =>	'array'
		];

		$ret = $this->validateInput($fields);

		if (!$ret) {
			$this->setResponse(
				(new CControllerResponseData(['main_block' => json_encode([
					'error' => [
						'messages' => array_column(get_and_clear_messages(), 'message')
					]
				])]))->disableView()
			);
		}

		return $ret;
	}

	protected function checkPermissions() {
		return $this->checkAccess(CRoleHelper::UI_ADMINISTRATION_USER_GROUPS);
	}

	protected function doAction() {
		$data = [
			'edit' => 0,
			'groupid' => null,
			'name' => '',
			'tag_filters' => []
		];
		$this->getInputs($data, array_keys($data));

		$data += [
			'title' => $data['edit'] == 0 ? _('New tag filter') : _('Tag filter'),
			'user' => [
				'debug_mode' => $this->getDebugMode()
			]
		];

		$data['host_groups_ms'] = [];

		if ($data['groupid'] !== null) {
			$host_groups = API::HostGroup()->get([
				'output' => ['groupid', 'name'],
				'groupids' => $data['groupid'],
				'preservekeys' => true
			]);
			CArrayHelper::sort($host_groups, ['name']);

			$data['host_groups_ms'] = CArrayHelper::renameObjectsKeys($host_groups, ['groupid' => 'id']);
		}

		$this->setResponse(new CControllerResponseData($data));
	}
}
