<?php
/*
** Zabbix
** Copyright (C) 2001-2022 Zabbix SIA
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


require_once dirname(__FILE__).'/../common/testFormHost.php';

/**
 * @dataSource DiscoveredHosts, Proxies
 *
 * @backup hosts
 *
 * @onBefore prepareUpdateData
 */
class testFormHostFromMonitoring extends testFormHost {

	public $monitoring = true;
	public $link = 'zabbix.php?action=host.view';

	public function testFormHostFromMonitoring_Layout() {
		$this->checkHostLayout();
	}

	/**
	 * @dataProvider getCreateData
	 */
	public function testFormHostFromMonitoring_Create($data) {
		$this->checkHostCreate($data);
	}

	/**
	 * @dataProvider getValidationUpdateData
	 */
	public function testFormHostFromMonitoring_ValidationUpdate($data) {
		$this->checkHostUpdate($data);
	}

	/**
	 * @backup hosts
	 *
	 * @dataProvider getUpdateData
	 */
	public function testFormHostFromMonitoring_Update($data) {
		$this->checkHostUpdate($data);
	}

	/**
	 * Update the host without any changes and check host and interfaces hashes.
	 */
	public function testFormHostFromMonitoring_SimpleUpdate() {
		$this->checkHostSimpleUpdate();
	}

	/**
	 * @dataProvider getCloneData
	 */
	public function testFormHostFromMonitoring_Clone($data) {
		$this->cloneHost($data, 'Clone');

		// Check that items aren't cloned from original host.
		$this->assertItemsDBCount($data['fields']['Host name'], 0);
	}

	/**
	 * @dataProvider getCloneData
	 */
	public function testFormHostFromMonitoring_FullClone($data) {
		$this->cloneHost($data, 'Full clone');

		// Check that items cloned from original host.
		$this->assertItemsDBCount($data['fields']['Host name'], $data['items']);
	}

	/**
	 * @dataProvider getCancelData
	 */
	public function testFormHostFromMonitoring_Cancel($data) {
		$this->checkCancel($data);
	}

	/**
	 * @dataProvider getDeleteData
	 */
	public function testFormHostFromMonitoring_Delete($data) {
		$this->checkDelete($data);
	}

	public function testFormHostFromMonitoring_DiscoveredHostLayout() {
		$this->checkDiscoveredHostLayout();
	}
}

