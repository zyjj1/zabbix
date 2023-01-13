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


require_once dirname(__FILE__).'/../include/CAPITest.php';
require_once dirname(__FILE__).'/../../include/triggers.inc.php';
require_once dirname(__FILE__).'/../../include/translateDefines.inc.php';

/**
 * @backup ids
 * @onBefore prepareScriptsData
 * @onAfter clearData
 */
class testScripts extends CAPITest {

	private static $data = [
		'groupids' => [
			'rw' => null,
			'r' => null,
			'd' => null,

			// Parent group with read-write permissions, child group with read permissions etc.
			'inherit_a_rw' => null,
			'inherit_b_r' => null,
			'inherit_c_rw' => null,
			'inherit_d_rw' => null
		],
		'hostids' => [
			'plain_rw' => null,
			'plain_r' => null,
			'plain_d' => null,
			'macros_rw_1' => null,
			'macros_r_2' => null,
			'macros_rw_3' => null,
			'interface_rw_1' => null,
			'interface_rw_2' => null,
			'inventory_rw_1' => null,
			'inventory_rw_2' => null,

			// Each host belongs to one of the inherited host groups.
			'inherit_a_rw' => null,
			'inherit_b_r' => null,
			'inherit_c_rw' => null,
			'inherit_d_rw' => null,
			'cause_d' => null,
			'symptom_rw' => null
		],

		// One item per host with same index. Inherited hosts do not need items. They are only for permission checks.
		'itemids' => [
			'plain_rw' => null,
			'plain_r' => null,
			'plain_d' => null,
			'macros_rw_1' => null,
			'macros_r_2' => null,
			'macros_rw_3' => null,
			'interface_rw_1' => null,
			'interface_rw_2' => null,
			'inventory_rw_1' => null,
			'inventory_rw_2' => null,
			'macros_d_cause' => null,
			'macros_rw_symptom' => null
		],

		// Some triggers will have multiple items.
		'triggerids' => [
			'plain_rw_single_d' => null,
			'plain_r_single_d' => null,
			'plain_d_single_d' => null,
			'plain_rw_r_dual_d' => null,
			'macros_rw_single_1_h' => null,
			'macros_rw_r_dual_1_2_h' => null,
			'macros_rw_dual_1_3_h' => null,
			'interface_rw_dual_a' => null,
			'inventory_rw_dual_a' => null,
			'macros_d_cause' => null,
			'macros_rw_symptom' => null
		],

		// Each trigger will generate one event. Index equal to triggers.
		'eventids' => [
			'plain_rw_single_d' => null,
			'plain_r_single_d' => null,
			'plain_d_single_d' => null,
			'plain_rw_r_dual_d' => null,
			'macros_rw_single_1_h' => null,
			'macros_rw_r_dual_1_2_h' => null,
			'macros_rw_dual_1_3_h' => null,
			'interface_rw_dual_a' => null,
			'inventory_rw_dual_a' => null,
			'macros_d_cause' => null,
			'macros_rw_symptom' => null
		],

		// One global macro.
		'usermacroid' => null,
		'usrgrpids' => [
			'admin' => null,
			'user' => null
		],
		'userids' => [
			'admin' => null,
			'user' => null
		],
		'scriptids' => [
			'get_custom_defaults' => null,
			'get_ipmi_defaults' => null,
			'get_webhook_filter' => null,
			'get_url' => null,
			'get_inherit_a_r' => null,
			'get_inherit_b_rw' => null,
			'get_inherit_d_r' => null,
			'exec_usrgrp_admin' => null,
			'exec_usrgrp_user' => null,
			'exec_hstgrp' => null,
			'exec_url' => null,
			'delete_single' => null,
			'delete_multi_1' => null,
			'delete_multi_2' => null,
			'delete_action' => null,
			'update_ipmi' => null,
			'update_ssh_pwd' => null,
			'update_ssh_key' => null,
			'update_telnet' => null,
			'update_webhook' => null,
			'update_webhook_params' => null,
			'update_custom' => null,
			'update_url' => null,
			'get_hosts_url' => null,
			'get_hosts_ipmi' => null,
			'get_hosts_webhook' => null,
			'get_events_url' => null,
			'get_events_ipmi' => null,
			'get_events_webhook' => null,
			'get_events_ssh' => null,
			'get_events_url_cause' => null
		],
		'actionids' => [
			'update' => null,
			'delete' => null
		],

		// Created scripts during script.create test (deleted at the end).
		'created' => []
	];

	/**
	 * Prepare data for tests. Create host groups, hosts, items, triggers, events, user groups, users, global macros,
	 * scripts and actions.
	 */
	public function prepareScriptsData() {
		// Create host groups.
		$hostgroups_data = [
			[
				'name' => 'API test host group, read-write'
			],
			[
				'name' => 'API test host group, read'
			],
			[
				'name' => 'API test host group, deny'
			],
			[
				'name' => 'API test host group inherit, A, read-write'
			],
			[
				'name' => 'API test host group inherit, A, read-write/API test host group inherit, B, read'
			],
			[
				'name' => 'API test host group inherit, A, read-write/API test host group inherit, B, read/API test host group inherit, C, read-write'
			],
			[
				'name' => 'API test host group inherit, A, read-write/API test host group inherit, B, read/API test host group inherit, C, read-write/API test host group inherit, D, read-write'
			]
		];
		$hostgroups = CDataHelper::call('hostgroup.create', $hostgroups_data);
		$this->assertArrayHasKey('groupids', $hostgroups, 'prepareScriptsData() failed: Could not create host groups.');
		self::$data['groupids'] = [
			'rw' => $hostgroups['groupids'][0],
			'r' => $hostgroups['groupids'][1],
			'd' => $hostgroups['groupids'][2],
			'inherit_a_rw' => $hostgroups['groupids'][3],
			'inherit_b_r' => $hostgroups['groupids'][4],
			'inherit_c_rw' => $hostgroups['groupids'][5],
			'inherit_d_rw' => $hostgroups['groupids'][6]
		];

		// Create hosts.
		$hosts_data = [
			// Host with no macro, no interface and no inventory. User will have read-write permissions.
			[
				'host' => 'api_test_host_plain_rw',
				'name' => 'API test host - plain, read-write',
				'groups' => [
					[
						'groupid' => self::$data['groupids']['rw']
					]
				]
			],

			// Host with no macro, no interface and no inventory. User will have read permissions.
			[
				'host' => 'api_test_host_plain_r',
				'name' => 'API test host - plain, read',
				'groups' => [
					[
						'groupid' => self::$data['groupids']['r']
					]
				]
			],

			// Host with no macro, no interface and no inventory. User will have deny permissions.
			[
				'host' => 'api_test_host_plain_d',
				'name' => 'API test host - plain, deny',
				'groups' => [
					[
						'groupid' => self::$data['groupids']['d']
					]
				]
			],

			// Hosts with macros.
			[
				'host' => 'api_test_host_macros_rw_1',
				'name' => 'API test host - macros 1, read-write',
				'groups' => [
					[
						'groupid' => self::$data['groupids']['rw']
					]
				],
				'macros' => [
					[
						'macro' => '{$HOST_MACRO}',
						'value' => 'host macro value - 1'
					]
				]
			],
			[
				'host' => 'api_test_host_macros_r_2',
				'name' => 'API test host - macros 2, read',
				'groups' => [
					[
						'groupid' => self::$data['groupids']['r']
					]
				],
				'macros' => [
					[
						'macro' => '{$HOST_MACRO}',
						'value' => 'host macro value - 2'
					]
				]
			],
			[
				'host' => 'api_test_host_macros_rw_3',
				'name' => 'API test host - macros 3, read-write',
				'groups' => [
					[
						'groupid' => self::$data['groupids']['rw']
					]
				],
				'macros' => [
					[
						'macro' => '{$HOST_MACRO_OTHER}',
						'value' => 'host macro other value'
					]
				]
			],

			// Hosts with interfaces to test indexed macro resolving {HOST.IP1}, {HOST.DNS2} etc.
			[
				'host' => 'api_test_host_interface_rw_1',
				'name' => 'API test host - interface (read-write) 1',
				'groups' => [
					[
						'groupid' => self::$data['groupids']['rw']
					]
				],
				'interfaces' => [
					[
						'type' => INTERFACE_TYPE_AGENT,
						'main' => INTERFACE_PRIMARY,
						'useip' => INTERFACE_USE_IP,
						'ip' => '1.1.1.1',
						'dns' => '',
						'port' => '11111'
					]
				]
			],
			[
				'host' => 'api_test_host_interface_rw_2',
				'name' => 'API test host - interface (read-write) 2',
				'groups' => [
					[
						'groupid' => self::$data['groupids']['rw']
					]
				],
				'interfaces' => [
					[
						'type' => INTERFACE_TYPE_AGENT,
						'main' => INTERFACE_PRIMARY,
						'useip' => INTERFACE_USE_DNS,
						'ip' => '',
						'dns' => 'dns_name',
						'port' => '22222'
					]
				]
			],

			// Hosts with inventory to test indexed macro resolving {INVENTORY.OS1}, {INVENTORY.ALIAS2} etc.
			[
				'host' => 'api_test_host_inventory_rw_1',
				'name' => 'API test host - inventory (read-write) 1',
				'groups' => [
					[
						'groupid' => self::$data['groupids']['rw']
					]
				],
				'inventory_mode' => HOST_INVENTORY_MANUAL,
				'inventory' => [
					'os' => 'Windows'
				]
			],
			[
				'host' => 'api_test_host_inventory_rw_2',
				'name' => 'API test host - inventory (read-write) 2',
				'groups' => [
					[
						'groupid' => self::$data['groupids']['rw']
					]
				],
				'inventory_mode' => HOST_INVENTORY_MANUAL,
				'inventory' => [
					'alias' => 'Inventory Alias'
				]
			],

			// Hosts that belong to inherited groups. Each host belongs to a deeper level of host group.
			[
				'host' => 'api_test_host_inherit_a_rw',
				'name' => 'API test host - inherit, A, read-write',
				'groups' => [
					[
						'groupid' => self::$data['groupids']['inherit_a_rw']
					]
				]
			],
			[
				'host' => 'api_test_host_inherit_b_r',
				'name' => 'API test host - inherit, B, read',
				'groups' => [
					[
						'groupid' => self::$data['groupids']['inherit_b_r']
					]
				]
			],
			[
				'host' => 'api_test_host_inherit_c_rw',
				'name' => 'API test host - inherit, C, read-write',
				'groups' => [
					[
						'groupid' => self::$data['groupids']['inherit_c_rw']
					]
				]
			],
			[
				'host' => 'api_test_host_inherit_d_rw',
				'name' => 'API test host - inherit, D, read-write',
				'groups' => [
					[
						'groupid' => self::$data['groupids']['inherit_d_rw']
					]
				]
			],

			// Hosts for cause and symptoms there symptoms is read write, but cause is denied for other users.
			[
				'host' => 'api_test_host_cause_d',
				'name' => 'API test host - cause, deny',
				'groups' => [
					[
						'groupid' => self::$data['groupids']['d']
					]
				]
			],
			[
				'host' => 'api_test_host_symptom_rw',
				'name' => 'API test host - symptom, read-write',
				'groups' => [
					[
						'groupid' => self::$data['groupids']['rw']
					]
				]
			]
		];
		$hosts = CDataHelper::call('host.create', $hosts_data);
		$this->assertArrayHasKey('hostids', $hosts, 'prepareScriptsData() failed: Could not create hosts.');
		self::$data['hostids'] = [
			'plain_rw' => $hosts['hostids'][0],
			'plain_r' => $hosts['hostids'][1],
			'plain_d' => $hosts['hostids'][2],
			'macros_rw_1' => $hosts['hostids'][3],
			'macros_r_2' => $hosts['hostids'][4],
			'macros_rw_3' => $hosts['hostids'][5],
			'interface_rw_1' => $hosts['hostids'][6],
			'interface_rw_2' => $hosts['hostids'][7],
			'inventory_rw_1' => $hosts['hostids'][8],
			'inventory_rw_2' => $hosts['hostids'][9],
			'inherit_a_rw' => $hosts['hostids'][10],
			'inherit_b_r' => $hosts['hostids'][11],
			'inherit_c_rw' => $hosts['hostids'][12],
			'inherit_d_rw' => $hosts['hostids'][13],
			'cause_d' => $hosts['hostids'][14],
			'symptom_rw' => $hosts['hostids'][15]
		];

		// Create an item on each host.
		$items_data = [
			[
				'hostid' => self::$data['hostids']['plain_rw'],
				'name' => 'API test item - plain, read-write',
				'key_' => 'api_test_item_plain_rw',
				'type' => ITEM_TYPE_TRAPPER,
				'value_type' => ITEM_VALUE_TYPE_FLOAT
			],
			[
				'hostid' => self::$data['hostids']['plain_r'],
				'name' => 'API test item - plain, read',
				'key_' => 'api_test_item_plain_r',
				'type' => ITEM_TYPE_TRAPPER,
				'value_type' => ITEM_VALUE_TYPE_FLOAT
			],
			[
				'hostid' => self::$data['hostids']['plain_d'],
				'name' => 'API test item - plain, deny',
				'key_' => 'api_test_item_plain_d',
				'type' => ITEM_TYPE_TRAPPER,
				'value_type' => ITEM_VALUE_TYPE_FLOAT
			],
			[
				'hostid' => self::$data['hostids']['macros_rw_1'],
				'name' => 'API test item - macros 1, read-write',
				'key_' => 'api_test_item_macros_rw_1',
				'type' => ITEM_TYPE_TRAPPER,
				'value_type' => ITEM_VALUE_TYPE_FLOAT
			],
			[
				'hostid' => self::$data['hostids']['macros_r_2'],
				'name' => 'API test item - macros 2, read',
				'key_' => 'api_test_item_macros_r_2',
				'type' => ITEM_TYPE_TRAPPER,
				'value_type' => ITEM_VALUE_TYPE_FLOAT
			],
			[
				'hostid' => self::$data['hostids']['macros_rw_3'],
				'name' => 'API test item - macros 3, read-write',
				'key_' => 'api_test_item_macros_rw_3',
				'type' => ITEM_TYPE_TRAPPER,
				'value_type' => ITEM_VALUE_TYPE_FLOAT
			],
			[
				'hostid' => self::$data['hostids']['interface_rw_1'],
				'name' => 'API test item - interface 1, read-write',
				'key_' => 'api_test_item_interface_rw_1',
				'type' => ITEM_TYPE_TRAPPER,
				'value_type' => ITEM_VALUE_TYPE_FLOAT
			],
			[
				'hostid' => self::$data['hostids']['interface_rw_2'],
				'name' => 'API test item - interface 2, read-write',
				'key_' => 'api_test_item_interface_rw_2',
				'type' => ITEM_TYPE_TRAPPER,
				'value_type' => ITEM_VALUE_TYPE_FLOAT
			],
			[
				'hostid' => self::$data['hostids']['inventory_rw_1'],
				'name' => 'API test item - inventory 1, read-write',
				'key_' => 'api_test_item_inventory_rw_1',
				'type' => ITEM_TYPE_TRAPPER,
				'value_type' => ITEM_VALUE_TYPE_FLOAT
			],
			[
				'hostid' => self::$data['hostids']['inventory_rw_2'],
				'name' => 'API test item - inventory 2, read-write',
				'key_' => 'api_test_item_inventory_rw_2',
				'type' => ITEM_TYPE_TRAPPER,
				'value_type' => ITEM_VALUE_TYPE_FLOAT
			],
			[
				'hostid' => self::$data['hostids']['cause_d'],
				'name' => 'API test item - macros cause, deny',
				'key_' => 'api_test_item_macros_cause_d',
				'type' => ITEM_TYPE_TRAPPER,
				'value_type' => ITEM_VALUE_TYPE_FLOAT
			],
			[
				'hostid' => self::$data['hostids']['symptom_rw'],
				'name' => 'API test item - macros symptom, read-write',
				'key_' => 'api_test_item_macros_symptom_rw',
				'type' => ITEM_TYPE_TRAPPER,
				'value_type' => ITEM_VALUE_TYPE_FLOAT
			]
		];
		$items = CDataHelper::call('item.create', $items_data);
		$this->assertArrayHasKey('itemids', $items, 'prepareScriptsData() failed: Could not create items.');
		self::$data['itemids'] = [
			'plain_rw' => $items['itemids'][0],
			'plain_r' => $items['itemids'][1],
			'plain_d' => $items['itemids'][2],
			'macros_rw_1' => $items['itemids'][3],
			'macros_r_2' => $items['itemids'][4],
			'macros_rw_3' => $items['itemids'][5],
			'interface_rw_1' => $items['itemids'][6],
			'interface_rw_2' => $items['itemids'][7],
			'inventory_rw_1' => $items['itemids'][8],
			'inventory_rw_2' => $items['itemids'][9],
			'macros_d_cause' => $items['itemids'][10],
			'macros_rw_symptom' => $items['itemids'][11]
		];

		// Create triggers. We already know the host names and item keys. Some belong to multiple hosts.
		$triggers_data = [
			[
				'description' => 'API test trigger - plain, single, read-write, disaster',
				'expression' => 'last(/api_test_host_plain_rw/api_test_item_plain_rw)<>0',
				'priority' => TRIGGER_SEVERITY_DISASTER
			],
			[
				'description' => 'API test trigger - plain, single, read, disaster',
				'expression' => 'last(/api_test_host_plain_r/api_test_item_plain_r)<>0',
				'priority' => TRIGGER_SEVERITY_DISASTER
			],
			[
				'description' => 'API test trigger - plain, single, deny, disaster',
				'expression' => 'last(/api_test_host_plain_d/api_test_item_plain_d)<>0',
				'priority' => TRIGGER_SEVERITY_DISASTER
			],

			// Trigger belongs to multiple hosts.
			[
				'description' => 'API test trigger - plain, dual, read-write & read, disaster',
				'expression' => 'last(/api_test_host_plain_rw/api_test_item_plain_rw)<>0'.
					' and last(/api_test_host_plain_r/api_test_item_plain_r)<>0',
				'priority' => TRIGGER_SEVERITY_DISASTER
			],
			[
				'description' => 'API test trigger - macros, single, read-write, high',
				'expression' => 'last(/api_test_host_macros_rw_1/api_test_item_macros_rw_1)<>0',
				'priority' => TRIGGER_SEVERITY_HIGH
			],

			// Both hosts have same macro name.
			[
				'description' => 'API test trigger - macros, dual, read-write & read, (1 & 2), high',
				'expression' => 'last(/api_test_host_macros_rw_1/api_test_item_macros_rw_1)<>0'.
					' and last(/api_test_host_macros_r_2/api_test_item_macros_r_2)<>0',
				'priority' => TRIGGER_SEVERITY_HIGH
			],

			// Both hosts have different macro names.
			[
				'description' => 'API test trigger - macros, dual, read-write, (1 & 3), high',
				'expression' => 'last(/api_test_host_macros_rw_1/api_test_item_macros_rw_1)<>0'.
					' and last(/api_test_host_macros_rw_3/api_test_item_macros_rw_3)<>0',
				'priority' => TRIGGER_SEVERITY_HIGH
			],

			// Hosts contain interfaces.
			[
				'description' => 'API test trigger - interface, dual, average',
				'expression' => 'last(/api_test_host_interface_rw_1/api_test_item_interface_rw_1)<>0'.
					' and last(/api_test_host_interface_rw_2/api_test_item_interface_rw_2)<>0',
				'priority' => TRIGGER_SEVERITY_AVERAGE
			],

			// Hosts contain inventory.
			[
				'description' => 'API test trigger - inventory, dual, average',
				'expression' => 'last(/api_test_host_inventory_rw_1/api_test_item_inventory_rw_1)<>0'.
					' and last(/api_test_host_inventory_rw_2/api_test_item_inventory_rw_2)<>0',
				'priority' => TRIGGER_SEVERITY_AVERAGE
			],

			// Cause and symptom triggers for different hosts.
			[
				'description' => 'API test trigger - macros, cause, disaster',
				'expression' => 'last(/api_test_host_cause_d/api_test_item_macros_cause_d)<>0',
				'priority' => TRIGGER_SEVERITY_DISASTER
			],
			[
				'description' => 'API test trigger - macros, symptom, high',
				'expression' => 'last(/api_test_host_symptom_rw/api_test_item_macros_symptom_rw)<>0',
				'priority' => TRIGGER_SEVERITY_HIGH
			]
		];
		$triggers = CDataHelper::call('trigger.create', $triggers_data);
		$this->assertArrayHasKey('triggerids', $triggers, 'prepareScriptsData() failed: Could not create triggers.');
		self::$data['triggerids'] = [
			'plain_rw_single_d' => $triggers['triggerids'][0],
			'plain_r_single_d' => $triggers['triggerids'][1],
			'plain_d_single_d' => $triggers['triggerids'][2],
			'plain_rw_r_dual_d' => $triggers['triggerids'][3],
			'macros_rw_single_1_h' => $triggers['triggerids'][4],
			'macros_rw_r_dual_1_2_h' => $triggers['triggerids'][5],
			'macros_rw_dual_1_3_h' => $triggers['triggerids'][6],
			'interface_rw_dual_a' => $triggers['triggerids'][7],
			'inventory_rw_dual_a' => $triggers['triggerids'][8],
			'macros_d_cause' => $triggers['triggerids'][9],
			'macros_rw_symptom' => $triggers['triggerids'][10]
		];

		// Generate events for all triggers. History is not used. Problems table is also not required.
		$nextid = CDBHelper::getAll(
			'SELECT i.nextid'.
			' FROM ids i'.
			' WHERE i.table_name='.zbx_dbstr('events').
				' AND i.field_name='.zbx_dbstr('eventid').
			' FOR UPDATE'
		);

		if ($nextid) {
			$nextid = bcadd($nextid[0]['nextid'], 1, 0);
		}
		else {
			DB::refreshIds('events', 0);

			$nextid = CDBHelper::getAll(
				'SELECT i.nextid'.
				' FROM ids i'.
				' WHERE i.table_name='.zbx_dbstr('events').
					' AND i.field_name='.zbx_dbstr('eventid').
				' FOR UPDATE'
			);

			$nextid = bcadd($nextid[0]['nextid'], 1, 0);
		}

		$events_data = [];
		$num = 0;
		foreach (self::$data['triggerids'] as $triggerid) {
			$events_data[] = [
				'source' => EVENT_SOURCE_TRIGGERS,
				'object' => EVENT_OBJECT_TRIGGER,
				'objectid' => $triggerid,
				'clock' => time(),
				'value' => TRIGGER_VALUE_TRUE,
				'acknowledged' => EVENT_NOT_ACKNOWLEDGED,
				'ns' => 0,
				'name' => $triggers_data[$num]['description'],
				'severity' => $triggers_data[$num]['priority']
			];
			$num++;
		}
		$eventids = DB::insertBatch('events', $events_data);
		$newids = array_fill($nextid, count($events_data), true);
		$this->assertEquals(array_keys($newids), $eventids, 'prepareScriptsData() failed: Could not create events.');
		self::$data['eventids'] = [
			'plain_rw_single_d' => $eventids[0],
			'plain_r_single_d' => $eventids[1],
			'plain_d_single_d' => $eventids[2],
			'plain_rw_r_dual_d' => $eventids[3],
			'macros_rw_single_1_h' => $eventids[4],
			'macros_rw_r_dual_1_2_h' => $eventids[5],
			'macros_rw_dual_1_3_h' => $eventids[6],
			'interface_rw_dual_a' => $eventids[7],
			'inventory_rw_dual_a' => $eventids[8],
			'macros_d_cause' => $eventids[9],
			'macros_rw_symptom' => $eventids[10]
		];

		/*
		 * Simulate server creating hierarchy of cause and symptoms. Skip making acknowledges, since those are not
		 * required for scripts, only the link. And the user who changed the event rank is also unimportant.
		 */
		$event_symptom_data = [[
			'eventid' => self::$data['eventids']['macros_rw_symptom'],
			'cause_eventid' => self::$data['eventids']['macros_d_cause']
		]];
		DB::insertBatch('event_symptom', $event_symptom_data, false);

		// Create global macro to later use it in scripts.
		$usermacros_data = [
			[
				'macro' => '{$GLOBAL_MACRO}',
				'value' => 'Global Macro Value'
			]
		];
		$usermacros = CDataHelper::call('usermacro.createglobal', $usermacros_data);
		$this->assertArrayHasKey('globalmacroids', $usermacros,
			'prepareScriptsData() failed: Could not create global macros.'
		);
		self::$data['usermacroid'] = $usermacros['globalmacroids'][0];

		/*
		 * Create user group to later check permissions on scripts. Most scripts will be checked using superadmin, but
		 * some will use regular admin.
		 */
		$usergroups_data = [
			[
				'name' => 'API test user group - admins',
				'hostgroup_rights' => [
					[
						'permission' => PERM_READ_WRITE,
						'id' => self::$data['groupids']['rw']
					],
					[
						'permission' => PERM_READ,
						'id' => self::$data['groupids']['r']
					],
					[
						'permission' => PERM_DENY,
						'id' => self::$data['groupids']['d']
					],
					[
						'permission' => PERM_READ_WRITE,
						'id' => self::$data['groupids']['inherit_a_rw']
					],
					[
						'permission' => PERM_READ,
						'id' => self::$data['groupids']['inherit_b_r']
					],
					[
						'permission' => PERM_READ_WRITE,
						'id' => self::$data['groupids']['inherit_c_rw']
					],
					[
						'permission' => PERM_READ_WRITE,
						'id' => self::$data['groupids']['inherit_d_rw']
					]
				]
			],
			[
				'name' => 'API test user group - users',
				'hostgroup_rights' => [
					[
						'permission' => PERM_READ_WRITE,
						'id' => self::$data['groupids']['rw']
					],
					[
						'permission' => PERM_READ,
						'id' => self::$data['groupids']['r']
					],
					[
						'permission' => PERM_DENY,
						'id' => self::$data['groupids']['d']
					],
					[
						'permission' => PERM_READ_WRITE,
						'id' => self::$data['groupids']['inherit_a_rw']
					],
					[
						'permission' => PERM_READ,
						'id' => self::$data['groupids']['inherit_b_r']
					],
					[
						'permission' => PERM_READ_WRITE,
						'id' => self::$data['groupids']['inherit_c_rw']
					],
					[
						'permission' => PERM_READ_WRITE,
						'id' => self::$data['groupids']['inherit_d_rw']
					]
				]
			]
		];
		$usergroups = CDataHelper::call('usergroup.create', $usergroups_data);
		$this->assertArrayHasKey('usrgrpids', $usergroups,
			'prepareScriptsData() failed: Could not create user groups.'
		);
		self::$data['usrgrpids']['admin'] = $usergroups['usrgrpids'][0];
		self::$data['usrgrpids']['user'] = $usergroups['usrgrpids'][1];

		// Get first available role one for each type that should exist on new install and create users.
		$admin_roleid = CDBHelper::getAll(
			'SELECT r.roleid'.
			' FROM role r'.
			' WHERE '.dbConditionInt('r.type', [USER_TYPE_ZABBIX_ADMIN])
		)[0]['roleid'];

		$user_roleid = CDBHelper::getAll(
			'SELECT r.roleid'.
			' FROM role r'.
			' WHERE '.dbConditionInt('r.type', [USER_TYPE_ZABBIX_USER])
		)[0]['roleid'];

		$users_data = [
			[
				'username' => 'api_test_admin',
				'name' => 'API One',
				'surname' => 'Tester One',
				'passwd' => '4P1T3$tEr',
				'roleid' => $admin_roleid,
				'usrgrps' => [
					[
						'usrgrpid' => self::$data['usrgrpids']['admin']
					]
				]
			],
			[
				'username' => 'api_test_user',
				'name' => 'API Two',
				'surname' => 'Tester Two',
				'passwd' => '4P1T3$tEr',
				'roleid' => $user_roleid,
				'usrgrps' => [
					[
						'usrgrpid' => self::$data['usrgrpids']['user']
					]
				]
			]
		];
		$users = CDataHelper::call('user.create', $users_data);
		$this->assertArrayHasKey('userids', $users, 'prepareScriptsData() failed: Could not create users.');
		self::$data['userids']['admin'] = $users['userids'][0];
		self::$data['userids']['user'] = $users['userids'][1];

		// Create scripts.
		$scripts_data = [
			// script.get
			[
				// Custom script with defaults.
				'name' => 'API test script.get custom script',
				'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
				'scope' => ZBX_SCRIPT_SCOPE_HOST,
				'command' => 'reboot server'
			],
			[
				// IPMI type script with defaults.
				'name' => 'API test script.get for filter IPMI',
				'type' => ZBX_SCRIPT_TYPE_IPMI,
				'scope' => ZBX_SCRIPT_SCOPE_HOST,
				'command' => 'reboot server'
			],
			[
				// WEBHOOK type script with custom parameters.
				'name' => 'API test script.get for filter webhooks and parameters',
				'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
				'scope' => ZBX_SCRIPT_SCOPE_HOST,
				'command' => 'reboot server',
				'timeout' => '5s',
				'menu_path' => 'Webhooks',
				'description' => 'Webhook script to test get() method',
				'parameters' => [
					[
						'name' => 'parameter one',
						'value' => ''
					],
					[
						'name' => 'parameter two',
						'value' => 'value 2'
					],
					[
						'name' => 'parameter three',
						'value' => 'value 3'
					]
				]
			],
			[
				// URL type script with non-default values.
				'name' => 'API test script.get URL',
				'type' => ZBX_SCRIPT_TYPE_URL,
				'scope' => ZBX_SCRIPT_SCOPE_HOST,
				'url' => 'http://zabbix/ui/zabbix.php?action=host.edit&hostid={HOST.ID}',
				'new_window' => ZBX_SCRIPT_URL_NEW_WINDOW_NO,
				'confirmation' => 'Confirmation macros: {$HOST_MACRO}, {$GLOBAL_MACRO}, {$DOESNOTEXIST}'
			],
			[
				// User has read-write permissions to top level host group, but requirement is read.
				'name' => 'API test script.get with inherited group, A, required host access - read',
				'type' => ZBX_SCRIPT_TYPE_IPMI,
				'scope' => ZBX_SCRIPT_SCOPE_HOST,
				'command' => 'reboot server',
				'groupid' => self::$data['groupids']['inherit_a_rw'],
				'host_access' => PERM_READ
			],
			[
				// User has read permissions to second level host group, but requirement is read-write.
				'name' => 'API test script.get with inherited group, B, required host access - read-write',
				'type' => ZBX_SCRIPT_TYPE_IPMI,
				'scope' => ZBX_SCRIPT_SCOPE_HOST,
				'command' => 'reboot server',
				'groupid' => self::$data['groupids']['inherit_b_r'],
				'host_access' => PERM_READ_WRITE
			],
			[
				// User has read-write permissions to last level host group, but requirement is read. "C" is skipped.
				'name' => 'API test script.get with inherited group, D, required host access - read',
				'type' => ZBX_SCRIPT_TYPE_IPMI,
				'scope' => ZBX_SCRIPT_SCOPE_HOST,
				'command' => 'reboot server',
				'groupid' => self::$data['groupids']['inherit_d_rw'],
				'host_access' => PERM_READ
			],

			// script.execute
			[
				// Only this user group has permissions, the other one does not.
				'name' => 'API test script.execute with user group (admin)',
				'type' => ZBX_SCRIPT_TYPE_IPMI,
				'scope' => ZBX_SCRIPT_SCOPE_HOST,
				'command' => 'reboot server',
				'usrgrpid' => self::$data['usrgrpids']['admin']
			],
			[
				// Only this user group has permissions, the other one does not.
				'name' => 'API test script.execute with user group (user)',
				'type' => ZBX_SCRIPT_TYPE_IPMI,
				'scope' => ZBX_SCRIPT_SCOPE_HOST,
				'command' => 'reboot server',
				'usrgrpid' => self::$data['usrgrpids']['user']
			],
			[
				// Execute allowed for specific host group.
				'name' => 'API test script.execute with host group',
				'type' => ZBX_SCRIPT_TYPE_IPMI,
				'scope' => ZBX_SCRIPT_SCOPE_HOST,
				'command' => 'reboot server',
				'groupid' => self::$data['groupids']['rw']
			],
			[
				// Execute will not work for URL type scripts.
				'name' => 'API test script.execute with type URL',
				'type' => ZBX_SCRIPT_TYPE_URL,
				'scope' => ZBX_SCRIPT_SCOPE_HOST,
				'url' => 'http://localhost/'
			],

			// script.delete
			[
				// IPMI type script with action scope that does not have action.
				'name' => 'API test script.delete - single allowed',
				'type' => ZBX_SCRIPT_TYPE_IPMI,
				'scope' => ZBX_SCRIPT_SCOPE_ACTION,
				'command' => 'reboot server'
			],
			[
				'name' => 'API test script.delete - multiple allowed 1',
				'type' => ZBX_SCRIPT_TYPE_IPMI,
				'scope' => ZBX_SCRIPT_SCOPE_HOST,
				'command' => 'reboot server'
			],
			[
				'name' => 'API test script.delete - multiple allowed 2',
				'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
				'scope' => ZBX_SCRIPT_SCOPE_EVENT,
				'command' => 'reboot server',
				'parameters' => [
					[
						'name' => 'parameter one',
						'value' => ''
					],
					[
						'name' => 'parameter two',
						'value' => 'value 2'
					],
					[
						'name' => 'parameter three',
						'value' => 'value 3'
					]
				]
			],
			[
				// IPMI type script with action scope that has action attached.
				'name' => 'API test script.delete - not allowed due to action',
				'type' => ZBX_SCRIPT_TYPE_IPMI,
				'scope' => ZBX_SCRIPT_SCOPE_ACTION,
				'command' => 'reboot server'
			],

			// script.update to test type change, scope change, name and params change.
			[
				'name' => 'API test script.update - IPMI',
				'type' => ZBX_SCRIPT_TYPE_IPMI,
				'scope' => ZBX_SCRIPT_SCOPE_ACTION,
				'command' => 'reboot server'
			],
			[
				'name' => 'API test script.update - IPMI (host)',
				'type' => ZBX_SCRIPT_TYPE_IPMI,
				'scope' => ZBX_SCRIPT_SCOPE_HOST,
				'command' => 'reboot server'
			],
			[
				'name' => 'API test script.update - SSH password',
				'type' => ZBX_SCRIPT_TYPE_SSH,
				'scope' => ZBX_SCRIPT_SCOPE_ACTION,
				'command' => 'reboot server',
				'username' => 'John'
			],
			[
				'name' => 'API test script.update - SSH public key',
				'type' => ZBX_SCRIPT_TYPE_SSH,
				'scope' => ZBX_SCRIPT_SCOPE_ACTION,
				'command' => 'reboot server',
				'authtype' => ITEM_AUTHTYPE_PUBLICKEY,
				'username' => 'John',
				'publickey' => 'pub-k',
				'privatekey' => 'priv-k'
			],
			[
				'name' => 'API test script.update - Telnet',
				'type' => ZBX_SCRIPT_TYPE_TELNET,
				'scope' => ZBX_SCRIPT_SCOPE_ACTION,
				'command' => 'reboot server',
				'username' => 'Jill'
			],
			[
				'name' => 'API test script.update - Webhook no params',
				'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
				'scope' => ZBX_SCRIPT_SCOPE_ACTION,
				'command' => 'reboot server'
			],
			[
				'name' => 'API test script.update - Webhook with params',
				'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
				'scope' => ZBX_SCRIPT_SCOPE_ACTION,
				'command' => 'reboot server',
				'parameters' => [
					[
						'name' => 'parameter one',
						'value' => ''
					],
					[
						'name' => 'parameter two',
						'value' => 'value 2'
					],
					[
						'name' => 'parameter three',
						'value' => 'value 3'
					]
				]
			],
			[
				'name' => 'API test script.update - custom script',
				'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
				'scope' => ZBX_SCRIPT_SCOPE_ACTION,
				'command' => 'reboot server'
			],
			[
				'name' => 'API test script.update - URL',
				'type' => ZBX_SCRIPT_TYPE_URL,
				'scope' => ZBX_SCRIPT_SCOPE_HOST,
				'url' => 'http://localhost/'
			],
			[
				'name' => 'API test script.update action',
				'type' => ZBX_SCRIPT_TYPE_IPMI,
				'scope' => ZBX_SCRIPT_SCOPE_ACTION,
				'command' => 'reboot server'
			],

			// script.getScriptsByHosts
			[
				'name' => 'API test script.getScriptsByHosts - URL',
				'type' => ZBX_SCRIPT_TYPE_URL,
				'scope' => ZBX_SCRIPT_SCOPE_HOST,
				'url' => 'http://zabbix/ui/zabbix.php?action=host.edit&hostid={HOST.ID}',
				'confirmation' => 'Confirmation macros: {$HOST_MACRO}, {$HOST_MACRO_OTHER}, {$GLOBAL_MACRO},'.
					' {$DOESNOTEXIST}, {HOST.ID}, {HOST.HOST}, {HOST.NAME}, {HOST.CONN}, {HOST.DNS}, {HOST.PORT},'.
					' {HOST.NAME1}, {HOST.NAME2}, {EVENT.ID}, {EVENT.NAME}, {EVENT.NSEVERITY}, {EVENT.SEVERITY}'
			],
			[
				'name' => 'API test script.getScriptsByHosts - IPMI',
				'type' => ZBX_SCRIPT_TYPE_IPMI,
				'scope' => ZBX_SCRIPT_SCOPE_HOST,
				'command' => 'reboot server',
				'confirmation' => 'Confirmation macros: {USER.FULLNAME}, {USER.NAME}, {USER.SURNAME}, {USER.USERNAME},'.
					' {INVENTORY.ALIAS}, {INVENTORY.OS}, {INVENTORY.TYPE}, {INVENTORY.CONTACT}, {INVENTORY.OS1},'.
					' {INVENTORY.OS2}, {HOSTGROUP.ID}',
				'usrgrpid' => self::$data['usrgrpids']['admin']
			],
			[
				'name' => 'API test script.getScriptsByHosts - Webhook',
				'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
				'scope' => ZBX_SCRIPT_SCOPE_HOST,
				'command' => 'reboot server',
				'confirmation' => 'Confirmation macros: {$GLOBAL_MACRO}, {HOST.HOST}, {USER.FULLNAME}, {HOST.CONN},'.
					' {HOST.IP}, {HOST.DNS}, {HOST.PORT}',
				'usrgrpid' => self::$data['usrgrpids']['user']
			],
			[
				'name' => 'API test script.getScriptsByHosts - SSH password',
				'type' => ZBX_SCRIPT_TYPE_SSH,
				'scope' => ZBX_SCRIPT_SCOPE_HOST,
				'command' => 'reboot server',
				'username' => 'user',
				'host_access' => PERM_READ_WRITE,
				'confirmation' => 'Confirmation macros: {$GLOBAL_MACRO}, {HOST.HOST}, {USER.FULLNAME}, {HOST.CONN},'.
					' {HOST.IP}, {HOST.DNS}, {HOST.PORT}, {INVENTORY.ALIAS}, {INVENTORY.OS}, {INVENTORY.TYPE}'
			],

			// script.getScriptsByEvents
			[
				'name' => 'API test script.getScriptsByEvents - URL',
				'type' => ZBX_SCRIPT_TYPE_URL,
				'scope' => ZBX_SCRIPT_SCOPE_EVENT,
				'url' => 'http://zabbix/ui/zabbix.php?action=host.edit&hostid={HOST.ID}',
				'confirmation' => 'Confirmation macros: {$HOST_MACRO}, {$HOST_MACRO_OTHER}, {$GLOBAL_MACRO},'.
					' {$DOESNOTEXIST}, {HOST.ID}, {HOST.HOST}, {HOST.NAME}, {HOST.CONN}, {HOST.DNS}, {HOST.PORT},'.
					' {HOST.NAME1}, {HOST.NAME2}, {EVENT.ID}, {EVENT.NAME}, {EVENT.NSEVERITY}, {EVENT.SEVERITY}'
			],
			[
				'name' => 'API test script.getScriptsByEvents - IPMI',
				'type' => ZBX_SCRIPT_TYPE_IPMI,
				'scope' => ZBX_SCRIPT_SCOPE_EVENT,
				'command' => 'reboot server',
				'confirmation' => 'Confirmation macros: {USER.FULLNAME}, {USER.NAME}, {USER.SURNAME}, {USER.USERNAME},'.
					' {INVENTORY.ALIAS}, {INVENTORY.OS}, {INVENTORY.TYPE}, {INVENTORY.CONTACT}, {INVENTORY.OS1},'.
					' {INVENTORY.OS2}, {EVENT.STATUS}, {EVENT.VALUE}, {HOSTGROUP.ID}',
				'usrgrpid' => self::$data['usrgrpids']['admin']
			],
			[
				'name' => 'API test script.getScriptsByEvents - Webhook',
				'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
				'scope' => ZBX_SCRIPT_SCOPE_EVENT,
				'command' => 'reboot server',
				'confirmation' => 'Confirmation macros: {$GLOBAL_MACRO}, {HOST.HOST}, {USER.FULLNAME}, {HOST.CONN},'.
					' {HOST.IP}, {HOST.DNS}, {HOST.PORT}',
				'usrgrpid' => self::$data['usrgrpids']['user']
			],
			[
				'name' => 'API test script.getScriptsByEvents - SSH password',
				'type' => ZBX_SCRIPT_TYPE_SSH,
				'scope' => ZBX_SCRIPT_SCOPE_EVENT,
				'command' => 'reboot server',
				'host_access' => PERM_READ_WRITE,
				'username' => 'user',
				'confirmation' => 'Confirmation macros: {$GLOBAL_MACRO}, {HOST.HOST}, {USER.FULLNAME}, {HOST.CONN},'.
					' {HOST.IP}, {HOST.DNS}, {HOST.PORT}, {INVENTORY.ALIAS}, {INVENTORY.OS}, {INVENTORY.TYPE}'
			],
			[
				'name' => 'API test script.getScriptsByEvents - URL cause',
				'type' => ZBX_SCRIPT_TYPE_URL,
				'scope' => ZBX_SCRIPT_SCOPE_EVENT,
				'url' => 'http://zabbix/ui/tr_events.php?eventid={EVENT.ID}',
				'confirmation' => 'Confirmation macros: {EVENT.CAUSE.ID}, {EVENT.CAUSE.NAME}, {EVENT.CAUSE.NSEVERITY},'.
					' {EVENT.CAUSE.SEVERITY}, {EVENT.CAUSE.STATUS}, {EVENT.CAUSE.VALUE}'
			]
		];
		$scripts = CDataHelper::call('script.create', $scripts_data);
		$this->assertArrayHasKey('scriptids', $scripts, 'prepareScriptsData() failed: Could not create scripts.');
		self::$data['scriptids']['get_custom_defaults'] = $scripts['scriptids'][0];
		self::$data['scriptids']['get_ipmi_defaults'] = $scripts['scriptids'][1];
		self::$data['scriptids']['get_webhook_filter'] = $scripts['scriptids'][2];
		self::$data['scriptids']['get_url'] = $scripts['scriptids'][3];
		self::$data['scriptids']['get_inherit_a_r'] = $scripts['scriptids'][4];
		self::$data['scriptids']['get_inherit_b_rw'] = $scripts['scriptids'][5];
		self::$data['scriptids']['get_inherit_d_r'] = $scripts['scriptids'][6];
		self::$data['scriptids']['exec_usrgrp_admin'] = $scripts['scriptids'][7];
		self::$data['scriptids']['exec_usrgrp_user'] = $scripts['scriptids'][8];
		self::$data['scriptids']['exec_hstgrp'] = $scripts['scriptids'][9];
		self::$data['scriptids']['exec_url'] = $scripts['scriptids'][10];
		self::$data['scriptids']['delete_single'] = $scripts['scriptids'][11];
		self::$data['scriptids']['delete_multi_1'] = $scripts['scriptids'][12];
		self::$data['scriptids']['delete_multi_2'] = $scripts['scriptids'][13];
		self::$data['scriptids']['delete_action'] = $scripts['scriptids'][14];
		self::$data['scriptids']['update_ipmi'] = $scripts['scriptids'][15];
		self::$data['scriptids']['update_ipmi_host'] = $scripts['scriptids'][16];
		self::$data['scriptids']['update_ssh_pwd'] = $scripts['scriptids'][17];
		self::$data['scriptids']['update_ssh_key'] = $scripts['scriptids'][18];
		self::$data['scriptids']['update_telnet'] = $scripts['scriptids'][19];
		self::$data['scriptids']['update_webhook'] = $scripts['scriptids'][20];
		self::$data['scriptids']['update_webhook_params'] = $scripts['scriptids'][21];
		self::$data['scriptids']['update_custom'] = $scripts['scriptids'][22];
		self::$data['scriptids']['update_url'] = $scripts['scriptids'][23];
		self::$data['scriptids']['update_action'] = $scripts['scriptids'][24];
		self::$data['scriptids']['get_hosts_url'] = $scripts['scriptids'][25];
		self::$data['scriptids']['get_hosts_ipmi'] = $scripts['scriptids'][26];
		self::$data['scriptids']['get_hosts_webhook'] = $scripts['scriptids'][27];
		self::$data['scriptids']['get_hosts_ssh'] = $scripts['scriptids'][28];
		self::$data['scriptids']['get_events_url'] = $scripts['scriptids'][29];
		self::$data['scriptids']['get_events_ipmi'] = $scripts['scriptids'][30];
		self::$data['scriptids']['get_events_webhook'] = $scripts['scriptids'][31];
		self::$data['scriptids']['get_events_ssh'] = $scripts['scriptids'][32];
		self::$data['scriptids']['get_events_url_cause'] = $scripts['scriptids'][33];

		// Create actions that use scripts to test script.delete.
		$actions_data = [
			[
				'name' => 'API test script.delete action',
				'eventsource' => EVENT_SOURCE_TRIGGERS,
				'operations' => [
					[
						'operationtype' => OPERATION_TYPE_COMMAND,
						'esc_period' => '0s',
						'esc_step_from' => 1,
						'esc_step_to' => 2,
						'evaltype' => CONDITION_EVAL_TYPE_AND_OR,
						'opcommand_grp' => [
							[
								'groupid' => self::$data['groupids']['rw']
							]
						],
						'opcommand' => [
							'scriptid' => self::$data['scriptids']['delete_action']
						]
					]
				]
			],
			[
				'name' => 'API test script.update action',
				'eventsource' => EVENT_SOURCE_TRIGGERS,
				'operations' => [
					[
						'operationtype' => OPERATION_TYPE_COMMAND,
						'esc_period' => '0s',
						'esc_step_from' => 1,
						'esc_step_to' => 2,
						'evaltype' => CONDITION_EVAL_TYPE_AND_OR,
						'opcommand_grp' => [
							[
								'groupid' => self::$data['groupids']['rw']
							]
						],
						'opcommand' => [
							'scriptid' => self::$data['scriptids']['update_action']
						]
					]
				]
			]
		];
		$actions = CDataHelper::call('action.create', $actions_data);
		$this->assertArrayHasKey('actionids', $actions, 'prepareScriptsData() failed: Could not create actions.');
		self::$data['actionids']['update'] = $actions['actionids'][0];
		self::$data['actionids']['delete'] = $actions['actionids'][1];
	}

	/**
	 * Data provider for script.create. Array contains invalid scripts.
	 *
	 * @return array
	 */
	public static function getScriptCreateDataInvalid() {
		return [
			'Test script.create missing fields' => [
				'script' => [],
				'expected_error' => 'Invalid parameter "/": cannot be empty.'
			],

			// Check script type.
			'Test script.create missing type' => [
				'script' => [
					'name' => 'API create script'
				],
				'expected_error' => 'Invalid parameter "/1": the parameter "type" is missing.'
			],
			'Test script.create invalid type (empty string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ''
				],
				'expected_error' => 'Invalid parameter "/1/type": an integer is expected.'
			],
			'Test script.create invalid type (string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => 'abc'
				],
				'expected_error' => 'Invalid parameter "/1/type": an integer is expected.'
			],
			'Test script.create invalid type' => [
				'script' => [
					'name' => 'API create script',
					'type' => 999999
				],
				'expected_error' => 'Invalid parameter "/1/type": value must be one of '.
					implode(', ', [ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT, ZBX_SCRIPT_TYPE_IPMI, ZBX_SCRIPT_TYPE_SSH,
						ZBX_SCRIPT_TYPE_TELNET, ZBX_SCRIPT_TYPE_WEBHOOK, ZBX_SCRIPT_TYPE_URL
					]).'.'
			],

			// Check scope.
			'Test script.create missing scope' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT
				],
				'expected_error' => 'Invalid parameter "/1": the parameter "scope" is missing.'
			],
			'Test script.create invalid scope (empty string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ''
				],
				'expected_error' => 'Invalid parameter "/1/scope": an integer is expected.'
			],
			'Test script.create invalid scope (string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => 'abc'
				],
				'expected_error' => 'Invalid parameter "/1/scope": an integer is expected.'
			],
			'Test script.create invalid scope' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => 999999
				],
				'expected_error' => 'Invalid parameter "/1/scope": value must be one of '.
					implode(', ', [ZBX_SCRIPT_SCOPE_ACTION, ZBX_SCRIPT_SCOPE_HOST, ZBX_SCRIPT_SCOPE_EVENT]).'.'
			],
			'Test script.create invalid scope for URL type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_URL,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION
				],
				'expected_error' => 'Invalid parameter "/1/scope": value must be one of '.
					implode(', ', [ZBX_SCRIPT_SCOPE_HOST, ZBX_SCRIPT_SCOPE_EVENT]).'.'
			],

			// Check script command.
			'Test script.create missing command for custom type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION
				],
				'expected_error' => 'Invalid parameter "/1": the parameter "command" is missing.'
			],
			'Test script.create empty command for custom type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => ''
				],
				'expected_error' => 'Invalid parameter "/1/command": cannot be empty.'
			],
			'Test script.create missing command for IPMI type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_IPMI,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION
				],
				'expected_error' => 'Invalid parameter "/1": the parameter "command" is missing.'
			],
			'Test script.create empty command for IPMI type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_IPMI,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => ''
				],
				'expected_error' => 'Invalid parameter "/1/command": cannot be empty.'
			],
			'Test script.create missing command for SSH type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_SSH,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION
				],
				'expected_error' => 'Invalid parameter "/1": the parameter "command" is missing.'
			],
			'Test script.create empty command for SSH type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_SSH,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => ''
				],
				'expected_error' => 'Invalid parameter "/1/command": cannot be empty.'
			],
			'Test script.create missing command for Telnet type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_TELNET,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION
				],
				'expected_error' => 'Invalid parameter "/1": the parameter "command" is missing.'
			],
			'Test script.create empty command for Telnet type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_TELNET,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => ''
				],
				'expected_error' => 'Invalid parameter "/1/command": cannot be empty.'
			],
			'Test script.create missing command for Webhook type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION
				],
				'expected_error' => 'Invalid parameter "/1": the parameter "command" is missing.'
			],
			'Test script.create empty command for Webhook type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => ''
				],
				'expected_error' => 'Invalid parameter "/1/command": cannot be empty.'
			],
			'Test script.create unexpected command for URL type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_URL,
					'scope' => ZBX_SCRIPT_SCOPE_HOST,
					'url' => 'http://localhost/',
					'command' => 'reboot server'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "command".'
			],

			// Check "url".
			'Test script.create missing "url" for URL type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_URL,
					'scope' => ZBX_SCRIPT_SCOPE_HOST
				],
				'expected_error' => 'Invalid parameter "/1": the parameter "url" is missing.'
			],
			'Test script.create empty "url" for URL type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_URL,
					'scope' => ZBX_SCRIPT_SCOPE_HOST,
					'url' => ''
				],
				'expected_error' => 'Invalid parameter "/1/url": cannot be empty.'
			],
			'Test script.create invalid URL' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_URL,
					'scope' => ZBX_SCRIPT_SCOPE_HOST,
					'url' => 'htp:/d'
				],
				'expected_error' => 'Invalid parameter "/1/url": unacceptable URL.'
			],
			'Test script.create unexpected URL for custom type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_HOST,
					'command' => 'reboot server',
					'url' => 'http://localhost/'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "url".'
			],
			'Test script.create unexpected URL for IPMI type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_IPMI,
					'scope' => ZBX_SCRIPT_SCOPE_HOST,
					'command' => 'reboot server',
					'url' => 'http://localhost/'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "url".'
			],
			'Test script.create unexpected URL for SSH type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_SSH,
					'scope' => ZBX_SCRIPT_SCOPE_HOST,
					'command' => 'reboot server',
					'url' => 'http://localhost/'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "url".'
			],
			'Test script.create unexpected URL for Telnet type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_TELNET,
					'scope' => ZBX_SCRIPT_SCOPE_HOST,
					'command' => 'reboot server',
					'url' => 'http://localhost/'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "url".'
			],
			'Test script.create unexpected URL for Webhook type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
					'scope' => ZBX_SCRIPT_SCOPE_HOST,
					'command' => 'reboot server',
					'url' => 'http://localhost/'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "url".'
			],

			// Check script name.
			'Test script.create missing name' => [
				'script' => [
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server'
				],
				'expected_error' => 'Invalid parameter "/1": the parameter "name" is missing.'
			],
			'Test script.create empty name' => [
				'script' => [
					'name' => '',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server'
				],
				'expected_error' => 'Invalid parameter "/1/name": cannot be empty.'
			],
			'Test script.create existing name' => [
				'script' => [
					'name' => 'Ping',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server'
				],
				'expected_error' => 'Script "Ping" already exists.'
			],
			'Test script.create duplicate name' => [
				'script' => [
					[
						'name' => 'Scripts with the same name',
						'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
						'scope' => ZBX_SCRIPT_SCOPE_ACTION,
						'command' => 'reboot server'
					],
					[
						'name' => 'Scripts with the same name',
						'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
						'scope' => ZBX_SCRIPT_SCOPE_ACTION,
						'command' => 'reboot server'
					]
				],
				'expected_error' => 'Invalid parameter "/2": value (name)=(Scripts with the same name) already exists.'
			],

			// Check script menu path.
			'Test script.create invalid "menu_path" field for host scope' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_HOST,
					'command' => 'reboot server',
					'menu_path' => 'folder1/folder2/'.'/folder4'
				],
				'expected_error' => 'Invalid parameter "/1/menu_path": directory cannot be empty.'
			],
			'Test script.create invalid menu_path for event scope' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_EVENT,
					'command' => 'reboot server',
					'menu_path' => 'folder1/folder2/'.'/folder4'
				],
				'expected_error' => 'Invalid parameter "/1/menu_path": directory cannot be empty.'
			],
			'Test script.create unexpected "menu_path" field for action scope (empty string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'menu_path' => ''
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "menu_path".'
			],
			'Test script.create unexpected "menu_path" field for action scope' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'menu_path' => 'folder1/folder2/'.'/folder4'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "menu_path".'
			],

			// Check script host access.
			'Test script.create unexpected "host_access" field for action scope (empty string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'host_access' => ''
				],
				// Must be changed in future if CApiInputValidator is improved.
				'expected_error' => 'Invalid parameter "/1/host_access": an integer is expected.'
			],
			'Test script.create unexpected "host_access" field for action scope' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'host_access' => 999999
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "host_access".'
			],
			'Test script.create invalid "host_access" field for host scope (empty string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_HOST,
					'command' => 'reboot server',
					'host_access' => ''
				],
				'expected_error' => 'Invalid parameter "/1/host_access": an integer is expected.'
			],
			'Test script.create invalid "host_access" field for host scope (string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_HOST,
					'command' => 'reboot server',
					'host_access' => 'abc'
				],
				'expected_error' => 'Invalid parameter "/1/host_access": an integer is expected.'
			],
			'Test script.create invalid "host_access" field host scope' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_HOST,
					'command' => 'reboot server',
					'host_access' => 999999
				],
				'expected_error' => 'Invalid parameter "/1/host_access": value must be one of '.
					implode(', ', [PERM_READ, PERM_READ_WRITE]).'.'
			],
			'Test script.create invalid "host_access" field for event scope (empty string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_EVENT,
					'command' => 'reboot server',
					'host_access' => ''
				],
				'expected_error' => 'Invalid parameter "/1/host_access": an integer is expected.'
			],
			'Test script.create invalid "host_access" field for event scope (string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_EVENT,
					'command' => 'reboot server',
					'host_access' => 'abc'
				],
				'expected_error' => 'Invalid parameter "/1/host_access": an integer is expected.'
			],
			'Test script.create invalid "host_access" field event scope' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_EVENT,
					'command' => 'reboot server',
					'host_access' => 999999
				],
				'expected_error' => 'Invalid parameter "/1/host_access": value must be one of '.
					implode(', ', [PERM_READ, PERM_READ_WRITE]).'.'
			],

			// Check script user group.
			'Test script.create unexpected "usrgrpid" field for action scope (empty string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'usrgrpid' => ''
				],
				// Must be changed in future if CApiInputValidator is improved.
				'expected_error' => 'Invalid parameter "/1/usrgrpid": a number is expected.'
			],
			'Test script.create unexpected "usrgrpid" field for action scope' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'usrgrpid' => 0
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "usrgrpid".'
			],
			'Test script.create invalid "usrgrpid" field for host scope (empty string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_HOST,
					'command' => 'reboot server',
					'usrgrpid' => ''
				],
				'expected_error' => 'Invalid parameter "/1/usrgrpid": a number is expected.'
			],
			'Test script.create invalid "usrgrpid" field for host scope' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_HOST,
					'command' => 'reboot server',
					'usrgrpid' => 999999
				],
				'expected_error' => 'User group with ID "999999" is not available.'
			],
			'Test script.create invalid "usrgrpid" field for event scope (empty string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_EVENT,
					'command' => 'reboot server',
					'usrgrpid' => ''
				],
				'expected_error' => 'Invalid parameter "/1/usrgrpid": a number is expected.'
			],
			'Test script.create invalid "usrgrpid" field for event scope' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_EVENT,
					'command' => 'reboot server',
					'usrgrpid' => 999999
				],
				'expected_error' => 'User group with ID "999999" is not available.'
			],

			// Check script confirmation.
			'Test script.create unexpected confirmation for action scope' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'confirmation' => ''
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "confirmation".'
			],

			// Check script host group.
			'Test script.create invalid host group (empty string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'groupid' => ''
				],
				'expected_error' => 'Invalid parameter "/1/groupid": a number is expected.'
			],
			'Test script.create invalid host group' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'groupid' => 999999
				],
				'expected_error' => 'Host group with ID "999999" is not available.'
			],

			// Check unexpected fields in script.
			'Test script.create unexpected field' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'unexpected_field' => ''
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "unexpected_field".'
			],

			// Check script execute_on.
			'Test script.create invalid "execute_on" field (empty string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'execute_on' => ''
				],
				'expected_error' => 'Invalid parameter "/1/execute_on": an integer is expected.'
			],
			'Test script.create invalid "execute_on" field (string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'execute_on' => 'abc'
				],
				'expected_error' => 'Invalid parameter "/1/execute_on": an integer is expected.'
			],
			'Test script.create invalid "execute_on" field' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'execute_on' => 999999
				],
				'expected_error' => 'Invalid parameter "/1/execute_on": value must be one of '.
					implode(', ', [ZBX_SCRIPT_EXECUTE_ON_AGENT, ZBX_SCRIPT_EXECUTE_ON_SERVER,
						ZBX_SCRIPT_EXECUTE_ON_PROXY
					]).'.'
			],
			'Test script.create unexpected "execute_on" field for IPMI type script (empty string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_IPMI,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'execute_on' => ''
				],
				// Must be changed in future if CApiInputValidator is improved.
				'expected_error' => 'Invalid parameter "/1/execute_on": an integer is expected.'
			],
			'Test script.create unexpected "execute_on" field for IPMI type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_IPMI,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'execute_on' => ZBX_SCRIPT_EXECUTE_ON_AGENT
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "execute_on".'
			],
			'Test script.create unexpected "execute_on" field for SSH type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_SSH,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'execute_on' => ZBX_SCRIPT_EXECUTE_ON_AGENT
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "execute_on".'
			],
			'Test script.create unexpected "execute_on" field for Telnet type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_TELNET,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'execute_on' => ZBX_SCRIPT_EXECUTE_ON_AGENT
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "execute_on".'
			],
			'Test script.create unexpected "execute_on" field for Webhook type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'execute_on' => ZBX_SCRIPT_EXECUTE_ON_AGENT
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "execute_on".'
			],
			'Test script.create unexpected "execute_on" field for URL type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_URL,
					'scope' => ZBX_SCRIPT_SCOPE_HOST,
					'url' => 'http://localhost/',
					'execute_on' => ZBX_SCRIPT_EXECUTE_ON_AGENT
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "execute_on".'
			],

			// Check script port.
			'Test script.create invalid port (string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_SSH,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'port' => 'abc'
				],
				'expected_error' => 'Invalid parameter "/1/port": an integer is expected.'
			],
			'Test script.create invalid port' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_SSH,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'port' => 999999
				],
				'expected_error' => 'Invalid parameter "/1/port": value must be one of '.
					ZBX_MIN_PORT_NUMBER.'-'.ZBX_MAX_PORT_NUMBER.'.'
			],
			'Test script.create unexpected port field for custom type script (empty string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'port' => ''
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "port".'
			],
			'Test script.create unexpected port field for custom type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'port' => 0
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "port".'
			],
			'Test script.create unexpected port field for IPMI type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_IPMI,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'port' => 0
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "port".'
			],
			'Test script.create unexpected port field for Webhook type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'port' => 0
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "port".'
			],
			'Test script.create unexpected port field for URL type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_URL,
					'scope' => ZBX_SCRIPT_SCOPE_HOST,
					'url' => 'http://localhost/',
					'port' => 0
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "port".'
			],

			// Check script auth type.
			'Test script.create invalid authtype (empty string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_SSH,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'authtype' => ''
				],
				'expected_error' => 'Invalid parameter "/1/authtype": an integer is expected.'
			],
			'Test script.create invalid authtype (string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_SSH,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'authtype' => 'abc'
				],
				'expected_error' => 'Invalid parameter "/1/authtype": an integer is expected.'
			],
			'Test script.create invalid authtype' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_SSH,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'authtype' => 999999
				],
				'expected_error' => 'Invalid parameter "/1/authtype": value must be one of '.
					implode(', ', [ITEM_AUTHTYPE_PASSWORD, ITEM_AUTHTYPE_PUBLICKEY]).'.'
			],
			'Test script.create unexpected authtype field for custom type script (empty string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'authtype' => ''
				],
				// Must be changed in future if CApiInputValidator is improved.
				'expected_error' => 'Invalid parameter "/1/authtype": an integer is expected.'
			],
			'Test script.create unexpected "authtype" field for custom type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'authtype' => ITEM_AUTHTYPE_PASSWORD
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "authtype".'
			],
			'Test script.create unexpected "authtype" field for IPMI type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_IPMI,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'authtype' => ITEM_AUTHTYPE_PASSWORD
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "authtype".'
			],
			'Test script.create unexpected "authtype" field for Telnet type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_TELNET,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'authtype' => ITEM_AUTHTYPE_PASSWORD
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "authtype".'
			],
			'Test script.create unexpected "authtype" field for Webhook type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'authtype' => ITEM_AUTHTYPE_PASSWORD
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "authtype".'
			],
			'Test script.create unexpected "authtype" field for URL type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_URL,
					'scope' => ZBX_SCRIPT_SCOPE_HOST,
					'url' => 'http://localhost/',
					'authtype' => ITEM_AUTHTYPE_PASSWORD
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "authtype".'
			],

			// Check script username.
			'Test script.create missing username for SSH type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_SSH,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server'
				],
				'expected_error' => 'Invalid parameter "/1": the parameter "username" is missing.'
			],
			'Test script.create missing username for Telnet type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_TELNET,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server'
				],
				'expected_error' => 'Invalid parameter "/1": the parameter "username" is missing.'
			],
			'Test script.create empty username for SSH type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_SSH,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'username' => ''
				],
				'expected_error' => 'Invalid parameter "/1/username": cannot be empty.'
			],
			'Test script.create empty username for Telnet type' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_TELNET,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'username' => ''
				],
				'expected_error' => 'Invalid parameter "/1/username": cannot be empty.'
			],
			'Test script.create unexpected username for custom type script (empty string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'username' => ''
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "username".'
			],
			'Test script.create unexpected username for custom type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'username' => 'John'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "username".'
			],
			'Test script.create unexpected username for IPMI type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_IPMI,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'username' => 'John'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "username".'
			],
			'Test script.create unexpected username for Webhook type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'username' => 'John'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "username".'
			],
			'Test script.create unexpected username for URL type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_URL,
					'scope' => ZBX_SCRIPT_SCOPE_HOST,
					'url' => 'http://localhost/',
					'username' => 'John'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "username".'
			],

			// Check script password.
			'Test script.create unexpected password for custom type script (empty string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'password' => ''
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "password".'
			],
			'Test script.create unexpected password for custom type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'password' => 'psswd'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "password".'
			],
			'Test script.create unexpected password for IPMI type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_IPMI,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'password' => 'psswd'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "password".'
			],
			'Test script.create unexpected password for Webhook type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'password' => 'psswd'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "password".'
			],
			'Test script.create unexpected password for URL type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_URL,
					'scope' => ZBX_SCRIPT_SCOPE_HOST,
					'url' => 'http://localhost/',
					'password' => 'psswd'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "password".'
			],

			// Check script public key.
			'Test script.create missing publickey' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_SSH,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'username' => 'John',
					'authtype' => ITEM_AUTHTYPE_PUBLICKEY
				],
				'expected_error' => 'Invalid parameter "/1": the parameter "publickey" is missing.'
			],
			'Test script.create empty "publickey" field' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_SSH,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'username' => 'John',
					'authtype' => ITEM_AUTHTYPE_PUBLICKEY,
					'publickey' => ''
				],
				'expected_error' => 'Invalid parameter "/1/publickey": cannot be empty.'
			],
			'Test script.create unexpected "publickey" field for custom type script (empty string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'publickey' => ''
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "publickey".'
			],
			'Test script.create unexpected "publickey" field for custom type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'publickey' => 'secretpubkey'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "publickey".'
			],
			'Test script.create unexpected "publickey" field for IPMI type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_IPMI,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'publickey' => 'secretpubkey'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "publickey".'
			],
			'Test script.create unexpected "publickey" field for Telnet type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_TELNET,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'publickey' => 'secretpubkey'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "publickey".'
			],
			'Test script.create unexpected "publickey" field for Webhook type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'publickey' => 'secretpubkey'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "publickey".'
			],
			'Test script.create unexpected "publickey" field for URL type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_URL,
					'scope' => ZBX_SCRIPT_SCOPE_HOST,
					'url' => 'http://localhost/',
					'publickey' => 'secretpubkey'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "publickey".'
			],

			// Check script private key.
			'Test script.create missing "privatekey" field' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_SSH,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'username' => 'John',
					'authtype' => ITEM_AUTHTYPE_PUBLICKEY,
					'publickey' => 'secretpubkey'
				],
				'expected_error' => 'Invalid parameter "/1": the parameter "privatekey" is missing.'
			],
			'Test script.create empty "privatekey" field' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_SSH,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'username' => 'John',
					'authtype' => ITEM_AUTHTYPE_PUBLICKEY,
					'publickey' => 'secretpubkey',
					'privatekey' => ''
				],
				'expected_error' => 'Invalid parameter "/1/privatekey": cannot be empty.'
			],
			'Test script.create unexpected "privatekey" field for custom type script (empty string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'privatekey' => ''
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "privatekey".'
			],
			'Test script.create unexpected "privatekey" field for custom type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'privatekey' => 'secretprivkey'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "privatekey".'
			],
			'Test script.create unexpected "privatekey" field for IPMI type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_IPMI,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'privatekey' => 'secretprivkey'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "privatekey".'
			],
			'Test script.create unexpected "privatekey" field for Telnet type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_TELNET,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'privatekey' => 'secretprivkey'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "privatekey".'
			],
			'Test script.create unexpected "privatekey" field for Webhook type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'privatekey' => 'secretprivkey'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "privatekey".'
			],
			'Test script.create unexpected "privatekey" field for URL type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_URL,
					'scope' => ZBX_SCRIPT_SCOPE_HOST,
					'url' => 'http://localhost/',
					'privatekey' => 'secretprivkey'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "privatekey".'
			],

			// Check script timeout.
			'Test script.create invalid timeout' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'timeout' => '100'
				],
				'expected_error' => 'Invalid parameter "/1/timeout": value must be one of 1-'.SEC_PER_MIN.'.'
			],
			'Test script.create unsupported macros in timeout' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'timeout' => '{$MACRO}'
				],
				'expected_error' => 'Invalid parameter "/1/timeout": a time unit is expected.'
			],
			'Test script.create unexpected timeout for custom type script (empty string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'timeout' => ''
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "timeout".'
			],
			'Test script.create unexpected timeout for custom type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'timeout' => '30s'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "timeout".'
			],
			'Test script.create unexpected timeout for IPMI type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_IPMI,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'timeout' => '30s'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "timeout".'
			],
			'Test script.create unexpected timeout for SSH type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_SSH,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'timeout' => '30s'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "timeout".'
			],
			'Test script.create unexpected timeout for Telnet type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_TELNET,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'timeout' => '30s'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "timeout".'
			],
			'Test script.create unexpected timeout for URL type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_URL,
					'scope' => ZBX_SCRIPT_SCOPE_HOST,
					'url' => 'http://localhost/',
					'timeout' => '30s'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "timeout".'
			],

			// Check script parameters.
			'Test script.create invalid parameters' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'parameters' => ''
				],
				'expected_error' => 'Invalid parameter "/1/parameters": an array is expected.'
			],
			'Test script.create missing name in parameters' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'parameters' => [[]]
				],
				'expected_error' => 'Invalid parameter "/1/parameters/1": the parameter "name" is missing.'
			],
			'Test script.create empty name in parameters' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'parameters' => [[
						'name' => ''
					]]
				],
				'expected_error' => 'Invalid parameter "/1/parameters/1/name": cannot be empty.'
			],
			'Test script.create missing value in parameters' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'parameters' => [[
						'name' => 'param1'
					]]
				],
				'expected_error' => 'Invalid parameter "/1/parameters/1": the parameter "value" is missing.'
			],
			'Test script.create duplicate parameters' => [
				'script' => [
					'name' => 'Webhook validation with params',
					'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'parameters' => [
						[
							'name' => 'param1',
							'value' => 'value1'
						],
						[
							'name' => 'param1',
							'value' => 'value1'
						]
					]
				],
				'expected_error' => 'Invalid parameter "/1/parameters/2": value (name)=(param1) already exists.'
			],
			'Test script.create unexpected parameters for custom type script (empty array)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'parameters' => []
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "parameters".'
			],
			'Test script.create unexpected parameters for custom type script (empty sub-params)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'parameters' => [[]]
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "parameters".'
			],
			'Test script.create unexpected parameters for custom type script (string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'parameters' => ''
				],
				// Must be changed in future if CApiInputValidator is improved.
				'expected_error' => 'Invalid parameter "/1/parameters": an array is expected.'
			],
			'Test script.create unexpected parameters for custom type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'parameters' => [[
						'name' => 'param1',
						'value' => 'value1'
					]]
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "parameters".'
			],
			'Test script.create unexpected parameters for IPMI type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_IPMI,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'parameters' => [[
						'name' => 'param1',
						'value' => 'value1'
					]]
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "parameters".'
			],
			'Test script.create unexpected parameters for SSH type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_SSH,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'parameters' => [[
						'name' => 'param1',
						'value' => 'value1'
					]]
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "parameters".'
			],
			'Test script.create unexpected parameters for Telnet type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_TELNET,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'parameters' => [[
						'name' => 'param1',
						'value' => 'value1'
					]]
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "parameters".'
			],
			'Test script.create unexpected parameters for URL type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_URL,
					'scope' => ZBX_SCRIPT_SCOPE_HOST,
					'url' => 'http://localhost/',
					'parameters' => [[
						'name' => 'param1',
						'value' => 'value1'
					]]
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "parameters".'
			],

			// Check "new_window".
			'Test script.create "new_window" field for URL type script (empty string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_URL,
					'scope' => ZBX_SCRIPT_SCOPE_HOST,
					'url' => 'http://localhost/',
					'new_window' => ''
				],
				'expected_error' => 'Invalid parameter "/1/new_window": an integer is expected.'
			],
			'Test script.create "new_window" field for URL type script (string)' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_URL,
					'scope' => ZBX_SCRIPT_SCOPE_HOST,
					'url' => 'http://localhost/',
					'new_window' => 'abc'
				],
				'expected_error' => 'Invalid parameter "/1/new_window": an integer is expected.'
			],
			'Test script.create invalid "new_window" field for URL type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_URL,
					'scope' => ZBX_SCRIPT_SCOPE_HOST,
					'url' => 'http://localhost/',
					'new_window' => 999999
				],
				'expected_error' => 'Invalid parameter "/1/new_window": value must be one of '.
					implode(', ', [ZBX_SCRIPT_URL_NEW_WINDOW_NO, ZBX_SCRIPT_URL_NEW_WINDOW_YES]).'.'
			],
			'Test script.create unexpected "new_window" field for custom type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'new_window' => ZBX_SCRIPT_URL_NEW_WINDOW_NO
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "new_window".'
			],
			'Test script.create unexpected "new_window" field for IPMI type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_IPMI,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'new_window' => ZBX_SCRIPT_URL_NEW_WINDOW_NO
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "new_window".'
			],
			'Test script.create unexpected "new_window" field for SSH type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_SSH,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'username' => 'John',
					'new_window' => ZBX_SCRIPT_URL_NEW_WINDOW_NO
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "new_window".'
			],
			'Test script.create unexpected "new_window" field for Telnet type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_TELNET,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'username' => 'John',
					'new_window' => ZBX_SCRIPT_URL_NEW_WINDOW_NO
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "new_window".'
			],
			'Test script.create unexpected "new_window" field for Webhook type script' => [
				'script' => [
					'name' => 'API create script',
					'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server',
					'new_window' => ZBX_SCRIPT_URL_NEW_WINDOW_NO
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "new_window".'
			]
		];
	}

	/**
	 * Data provider for script.create. Array contains valid scripts.
	 *
	 * @return array
	 */
	public static function getScriptCreateDataValid() {
		return [
			'Test script.create successful UTF-8 name' => [
				'script' => [
					[
						'name' => 'Апи скрипт создан утф-8',
						'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
						'scope' => ZBX_SCRIPT_SCOPE_ACTION,
						'command' => 'reboot server 1'
					]
				],
				'expected_error' => null
			],
			'Test script.create successful multiple scripts' => [
				'script' => [
					[
						'name' => 'API create one script',
						'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
						'scope' => ZBX_SCRIPT_SCOPE_ACTION,
						'command' => 'reboot server 1'
					],
					[
						'name' => 'æų',
						'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
						'scope' => ZBX_SCRIPT_SCOPE_ACTION,
						'command' => 'æų'
					]
				],
				'expected_error' => null
			],
			'Test script.create successful menu path for host scope (empty string)' => [
				'script' => [
					[
						'name' => 'API create script (menu path test 1)',
						'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
						'scope' => ZBX_SCRIPT_SCOPE_HOST,
						'command' => 'reboot server',
						'menu_path' => ''
					]
				],
				'expected_error' => null
			],
			'Test script.create successful menu path for event scope (empty string)' => [
				'script' => [
					[
						'name' => 'API create script (menu path test 2)',
						'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
						'scope' => ZBX_SCRIPT_SCOPE_EVENT,
						'command' => 'reboot server',
						'menu_path' => ''
					]
				],
				'expected_error' => null
			],
			'Test script.create successful menu path (empty root)' => [
				'script' => [
					[
						'name' => 'API create script (menu path test 3)',
						'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
						'scope' => ZBX_SCRIPT_SCOPE_HOST,
						'command' => 'reboot server',
						'menu_path' => '/'
					]
				],
				'expected_error' => null
			],
			'Test script.create successful menu path (preceding slash)' => [
				'script' => [
					[
						'name' => 'API create script (menu path test 4)',
						'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
						'scope' => ZBX_SCRIPT_SCOPE_HOST,
						'command' => 'reboot server',
						'menu_path' => '/folder1/folder2'
					]
				],
				'expected_error' => null
			],
			'Test script.create successful menu path (trailing slash)' => [
				'script' => [
					[
						'name' => 'API create script (menu path test 5)',
						'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
						'scope' => ZBX_SCRIPT_SCOPE_HOST,
						'command' => 'reboot server',
						'menu_path' => 'folder1/folder2/'
					]
				],
				'expected_error' => null
			],
			'Test script.create successful menu path (preceding and trailing slash)' => [
				'script' => [
					[
						'name' => 'API create script (menu path test 6)',
						'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
						'scope' => ZBX_SCRIPT_SCOPE_HOST,
						'command' => 'reboot server',
						'menu_path' => '/folder1/folder2/'
					]
				],
				'expected_error' => null
			],
			'Test script.create successful menu path (no slash)' => [
				'script' => [
					[
						'name' => 'API create script (menu path test 7)',
						'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
						'scope' => ZBX_SCRIPT_SCOPE_HOST,
						'command' => 'reboot server',
						'menu_path' => 'folder1/folder2'
					]
				],
				'expected_error' => null
			],
			'Test script.create successful custom type script with random non-default parameters' => [
				'script' => [
					[
						'name' => 'API create custom script with random non-default parameters',
						'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
						'scope' => ZBX_SCRIPT_SCOPE_EVENT,
						'command' => 'reboot server',
						'execute_on' => ZBX_SCRIPT_EXECUTE_ON_SERVER,
						'description' => 'custom event script that executes on server for all user groups and a host group with read-write permissions',
						'usrgrpid' => 0,
						'groupid' => 'rw',
						'host_access' => PERM_READ_WRITE,
						'confirmation' => 'confirmation text',
						'menu_path' => 'folder1/folder2'
					]
				],
				'expected_error' => null
			],
			'Test script.create successful SSH type script with random non-default parameters' => [
				'script' => [
					[
						'name' => 'API create SSH script with random non-default parameters',
						'type' => ZBX_SCRIPT_TYPE_SSH,
						'scope' => ZBX_SCRIPT_SCOPE_HOST,
						'command' => 'reboot server',
						'description' =>
							'SSH host script for regular admins and all host groups with read-write permissions',
						'usrgrpid' => 'admin',
						'groupid' => 0,
						'host_access' => PERM_READ_WRITE,
						'confirmation' => 'confirmation text',
						'port' => '{$MACRO}',
						'authtype' => ITEM_AUTHTYPE_PUBLICKEY,
						'username' => 'John',
						'password' => 'Ada',
						'publickey' => 'secret_public_key',
						'privatekey' => 'secret_private_key',
						'menu_path' => 'folder1/folder2'
					]
				],
				'expected_error' => null
			],
			'Test script.create successful Telnet type script with random non-default parameters' => [
				'script' => [
					[
						'name' => 'API create Telnet script with random non-default parameters',
						'type' => ZBX_SCRIPT_TYPE_TELNET,
						'scope' => ZBX_SCRIPT_SCOPE_EVENT,
						'command' => 'reboot server',
						'description' =>
							'Telnet event script for regular users and host groups with read-write permissions',
						'usrgrpid' => 'user',
						'groupid' => 'rw',
						'host_access' => PERM_READ_WRITE,
						'confirmation' => 'confirmation text',
						'port' => 456,
						'username' => 'John',
						'password' => 'Ada',
						'menu_path' => 'folder1/folder2'
					]
				],
				'expected_error' => null
			],
			'Test script.create successful Webhook type script with random non-default parameters' => [
				'script' => [
					[
						'name' => 'API create Javascript script with random non-default parameters',
						'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
						'scope' => ZBX_SCRIPT_SCOPE_EVENT,
						'command' => 'reboot server',
						'description' =>
							'Webhook event script with for regular users and host groups with read-write permissions',
						'usrgrpid' => 'user',
						'groupid' => 'rw',
						'host_access' => PERM_READ_WRITE,
						'confirmation' => 'confirmation text',
						'timeout' => '10',
						'menu_path' => 'folder1/folder2',
						'parameters' => [
							[
								'name' => '!@#$%^&*()_+<>,.\/',
								'value' => '!@#$%^&*()_+<>,.\/'
							],
							[
								'name' => str_repeat('n', 255),
								'value' => str_repeat('v', 2048)
							],
							[
								'name' => '{$MACRO:A}',
								'value' => '{$MACRO:A}'
							],
							[
								'name' => '{$USERMACRO}',
								'value' => ''
							],
							[
								'name' => '{HOST.HOST}',
								'value' => '{EVENT.NAME}'
							],
							[
								'name' => 'Имя',
								'value' => 'Значение'
							]
						]
					]
				],
				'expected_error' => null
			],
			'Test script.create successful URL type script with random non-default parameters' => [
				'script' => [
					[
						'name' => 'API create URL script with random non-default parameters',
						'type' => ZBX_SCRIPT_TYPE_URL,
						'scope' => ZBX_SCRIPT_SCOPE_EVENT,
						'url' => 'http://localhost/',
						'new_window' => ZBX_SCRIPT_URL_NEW_WINDOW_NO,
						'description' =>
							'URL type event script for regular admins and host groups with read-write permissions',
						'usrgrpid' => 'admin',
						'groupid' => 'rw',
						'host_access' => PERM_READ_WRITE,
						'confirmation' => 'confirmation text',
						'menu_path' => 'folder1/folder2'
					]
				],
				'expected_error' => null
			]
		];
	}

	/**
	 * Test script.create with errors like missing fields, optional invalid fields and valid fields.
	 *
	 * @dataProvider getScriptCreateDataInvalid
	 * @dataProvider getScriptCreateDataValid
	 */
	public function testScript_Create($scripts, $expected_error) {
		// Accept single and multiple scripts just like API method. Work with multi-dimensional array in result.
		if (!array_key_exists(0, $scripts)) {
			$scripts = zbx_toArray($scripts);
		}

		// Replace ID placeholders with real IDs.
		foreach ($scripts as &$script) {
			$script = self::resolveIds($script);
		}
		unset($script);

		$sql_scripts = 'SELECT NULL FROM scripts';
		$old_hash_scripts = CDBHelper::getHash($sql_scripts);

		$result = $this->call('script.create', $scripts, $expected_error);

		if ($expected_error === null) {
			// Something was changed in DB.
			$this->assertNotSame($old_hash_scripts, CDBHelper::getHash($sql_scripts));
			$this->assertEquals(count($scripts), count($result['result']['scriptids']));

			// Add script IDs to create array, so they can be deleted after tests are complete.
			self::$data['created'] = array_merge(self::$data['created'], $result['result']['scriptids']);

			// Check individual fields according to each script type.
			foreach ($result['result']['scriptids'] as $num => $scriptid) {
				$db_scripts = $this->getScripts([$scriptid]);
				$db_script = $db_scripts[$scriptid];

				// Required fields.
				$this->assertNotEmpty($db_script['name']);
				$this->assertSame($scripts[$num]['name'], $db_script['name']);
				$this->assertEquals($scripts[$num]['type'], $db_script['type']);
				$this->assertEquals($scripts[$num]['scope'], $db_script['scope']);

				// Check menu path.
				if ($db_script['scope'] == ZBX_SCRIPT_SCOPE_ACTION) {
					$this->assertEmpty($db_script['menu_path']);
					$this->assertEquals(0, $db_script['usrgrpid']);
					$this->assertEquals(DB::getDefault('scripts', 'host_access'), $db_script['host_access']);
					$this->assertEmpty($db_script['confirmation']);
				}
				else {
					// Check menu path.
					if (array_key_exists('menu_path', $scripts[$num])) {
						$this->assertSame($scripts[$num]['menu_path'], $db_script['menu_path']);
					}
					else {
						$this->assertEmpty($db_script['menu_path']);
					}

					// Check user group.
					if (array_key_exists('usrgrpid', $scripts[$num])) {
						$this->assertEquals($scripts[$num]['usrgrpid'], $db_script['usrgrpid']);
					}
					else {
						// Despite the default in DB is NULL, getting value from DB gets us 0 as string.
						$this->assertEquals(0, $db_script['usrgrpid']);
					}

					// Check host access.
					if (array_key_exists('host_access', $scripts[$num])) {
						$this->assertEquals($scripts[$num]['host_access'], $db_script['host_access']);
					}
					else {
						$this->assertEquals(DB::getDefault('scripts', 'host_access'), $db_script['host_access']);
					}

					// Check confirmation.
					if (array_key_exists('confirmation', $scripts[$num])) {
						$this->assertSame($scripts[$num]['confirmation'], $db_script['confirmation']);
					}
					else {
						$this->assertEmpty($db_script['confirmation']);
					}
				}

				// Optional common fields for all script types.
				if (array_key_exists('groupid', $scripts[$num])) {
					$this->assertEquals($scripts[$num]['groupid'], $db_script['groupid']);
				}
				else {
					// Despite the default in DB is NULL, getting value from DB gets us 0 as string.
					$this->assertEquals(0, $db_script['groupid']);
				}

				if (array_key_exists('description', $scripts[$num])) {
					$this->assertSame($scripts[$num]['description'], $db_script['description']);
				}
				else {
					$this->assertEmpty($db_script['description']);
				}

				if ($scripts[$num]['type']) {
					switch ($scripts[$num]['type']) {
						case ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT:
							// Check execute on.
							if (array_key_exists('execute_on', $scripts[$num])) {
								$this->assertEquals($scripts[$num]['execute_on'], $db_script['execute_on']);
							}
							else {
								$this->assertEquals(DB::getDefault('scripts', 'execute_on'), $db_script['execute_on']);
							}

							// Check other fields.
							$this->assertNotEmpty($db_script['command']);
							$this->assertSame($scripts[$num]['command'], $db_script['command']);
							$this->assertSame(DB::getDefault('scripts', 'timeout'), $db_script['timeout']);
							$this->assertEmpty($db_script['port']);
							$this->assertEquals(DB::getDefault('scripts', 'authtype'), $db_script['authtype']);
							$this->assertEmpty($db_script['username']);
							$this->assertEmpty($db_script['password']);
							$this->assertEmpty($db_script['publickey']);
							$this->assertEmpty($db_script['privatekey']);
							$this->assertEmpty($db_script['url']);
							$this->assertEquals(DB::getDefault('scripts', 'new_window'), $db_script['new_window']);
							$this->assertEmpty($db_script['parameters']);
							break;

						case ZBX_SCRIPT_TYPE_IPMI:
							$this->assertNotEmpty($db_script['command']);
							$this->assertSame($scripts[$num]['command'], $db_script['command']);
							$this->assertEquals(DB::getDefault('scripts', 'execute_on'), $db_script['execute_on']);
							$this->assertSame(DB::getDefault('scripts', 'timeout'), $db_script['timeout']);
							$this->assertEmpty($db_script['port']);
							$this->assertEquals(DB::getDefault('scripts', 'authtype'), $db_script['authtype']);
							$this->assertEmpty($db_script['username']);
							$this->assertEmpty($db_script['password']);
							$this->assertEmpty($db_script['publickey']);
							$this->assertEmpty($db_script['privatekey']);
							$this->assertEmpty($db_script['url']);
							$this->assertEquals(DB::getDefault('scripts', 'new_window'), $db_script['new_window']);
							$this->assertEmpty($db_script['parameters']);
							break;

						case ZBX_SCRIPT_TYPE_SSH:
							// Check username.
							$this->assertNotEmpty($db_script['username']);
							$this->assertSame($scripts[$num]['username'], $db_script['username']);

							// Check port.
							if (array_key_exists('port', $scripts[$num])) {
								$this->assertEquals($scripts[$num]['port'], $db_script['port']);
							}
							else {
								$this->assertEmpty($db_script['port']);
							}

							// Check auth type.
							if (array_key_exists('authtype', $scripts[$num])) {
								$this->assertEquals($scripts[$num]['authtype'], $db_script['authtype']);

								if ($scripts[$num]['authtype'] == ITEM_AUTHTYPE_PUBLICKEY) {
									$this->assertNotEmpty($db_script['publickey']);
									$this->assertNotEmpty($db_script['privatekey']);
									$this->assertSame($scripts[$num]['publickey'], $db_script['publickey']);
									$this->assertSame($scripts[$num]['privatekey'], $db_script['privatekey']);
								}
								else {
									$this->assertEmpty($db_script['publickey']);
									$this->assertEmpty($db_script['privatekey']);
								}
							}
							else {
								$this->assertEquals(DB::getDefault('scripts', 'authtype'), $db_script['authtype']);
								$this->assertEmpty($db_script['publickey']);
								$this->assertEmpty($db_script['privatekey']);
							}

							// Check password.
							if (array_key_exists('password', $scripts[$num])) {
								$this->assertSame($scripts[$num]['password'], $db_script['password']);
							}
							else {
								$this->assertEmpty($db_script['password']);
							}

							// Check other fields.
							$this->assertNotEmpty($db_script['command']);
							$this->assertSame($scripts[$num]['command'], $db_script['command']);
							$this->assertEquals(DB::getDefault('scripts', 'execute_on'), $db_script['execute_on']);
							$this->assertSame(DB::getDefault('scripts', 'timeout'), $db_script['timeout']);
							$this->assertEmpty($db_script['url']);
							$this->assertEquals(DB::getDefault('scripts', 'new_window'), $db_script['new_window']);
							$this->assertEmpty($db_script['parameters']);
							break;

						case ZBX_SCRIPT_TYPE_TELNET:
							// Check username.
							$this->assertNotEmpty($db_script['username']);
							$this->assertSame($scripts[$num]['username'], $db_script['username']);

							// Check password.
							if (array_key_exists('password', $scripts[$num])) {
								$this->assertSame($scripts[$num]['password'], $db_script['password']);
							}
							else {
								$this->assertEmpty($db_script['password']);
							}

							// Check port.
							if (array_key_exists('port', $scripts[$num])) {
								$this->assertEquals($scripts[$num]['port'], $db_script['port']);
							}
							else {
								$this->assertEmpty($db_script['port']);
							}

							// Check other fields.
							$this->assertNotEmpty($db_script['command']);
							$this->assertSame($scripts[$num]['command'], $db_script['command']);
							$this->assertEquals(DB::getDefault('scripts', 'execute_on'), $db_script['execute_on']);
							$this->assertSame(DB::getDefault('scripts', 'timeout'), $db_script['timeout']);
							$this->assertEquals(DB::getDefault('scripts', 'authtype'), $db_script['authtype']);
							$this->assertEmpty($db_script['publickey']);
							$this->assertEmpty($db_script['privatekey']);
							$this->assertEmpty($db_script['url']);
							$this->assertEquals(DB::getDefault('scripts', 'new_window'), $db_script['new_window']);
							$this->assertEmpty($db_script['parameters']);
							break;

						case ZBX_SCRIPT_TYPE_WEBHOOK:
							// Check timeout.
							if (array_key_exists('timeout', $scripts[$num])) {
								$this->assertSame($scripts[$num]['timeout'], $db_script['timeout']);
							}
							else {
								$this->assertSame(DB::getDefault('scripts', 'timeout'), $db_script['timeout']);
							}

							// Check parameters.
							if (array_key_exists('parameters', $scripts[$num])) {
								if ($scripts[$num]['parameters']) {
									// Check newly added parameters.
									$this->assertNotEmpty($db_script['parameters']);
									$this->assertEqualsCanonicalizing($scripts[$num]['parameters'],
										$db_script['parameters']
									);
								}
								else {
									// Check that parameters are removed.
									$this->assertEmpty($db_script['parameters']);
								}
							}
							else {
								// Check that parameters not even added.
								$this->assertEmpty($db_script['parameters']);
							}

							// Check other fields.
							$this->assertNotEmpty($db_script['command']);
							$this->assertSame($scripts[$num]['command'], $db_script['command']);
							$this->assertEquals(DB::getDefault('scripts', 'execute_on'), $db_script['execute_on']);
							$this->assertEmpty($db_script['port']);
							$this->assertEquals(DB::getDefault('scripts', 'authtype'), $db_script['authtype']);
							$this->assertEmpty($db_script['username']);
							$this->assertEmpty($db_script['password']);
							$this->assertEmpty($db_script['publickey']);
							$this->assertEmpty($db_script['privatekey']);
							$this->assertEmpty($db_script['url']);
							$this->assertEquals(DB::getDefault('scripts', 'new_window'), $db_script['new_window']);
							break;

						case ZBX_SCRIPT_TYPE_URL:
							$this->assertNotEmpty($db_script['url']);
							$this->assertSame($scripts[$num]['url'], $db_script['url']);

							// Check "new_window".
							if (array_key_exists('new_window', $scripts[$num])) {
								$this->assertEquals($scripts[$num]['new_window'], $db_script['new_window']);
							}
							else {
								$this->assertSame(DB::getDefault('scripts', 'new_window'), $db_script['new_window']);
							}

							// Check other fields.
							$this->assertEmpty($db_script['command']);
							$this->assertEquals(DB::getDefault('scripts', 'execute_on'), $db_script['execute_on']);
							$this->assertSame(DB::getDefault('scripts', 'timeout'), $db_script['timeout']);
							$this->assertEmpty($db_script['port']);
							$this->assertEquals(DB::getDefault('scripts', 'authtype'), $db_script['authtype']);
							$this->assertEmpty($db_script['username']);
							$this->assertEmpty($db_script['password']);
							$this->assertEmpty($db_script['publickey']);
							$this->assertEmpty($db_script['privatekey']);
							$this->assertEmpty($db_script['parameters']);
							break;
					}
				}
			}
		}
		else {
			$this->assertSame($old_hash_scripts, CDBHelper::getHash($sql_scripts));
		}
	}

	/**
	 * Data provider for script.get to check inherited groups and hosts.
	 *
	 * @return array
	 */
	public static function getScriptGetInheritance() {
		return [
			// This is a top group, nothing to inherit from.
			'Test script.get top level group' => [
				'request' => [
					'output' => ['scriptid'],
					'groupids' => ['inherit_a_rw']
				],
				'expected_result' => [
					'result_keys' => ['scriptid'],
					'has.scriptid' => ['get_inherit_a_r']
				]
			],

			// This is a child group and script from parent group is inherited.
			'Test script.get child group' => [
				'request' => [
					'output' => ['scriptid'],
					'groupids' => ['inherit_b_r']
				],
				'expected_result' => [
					'result_keys' => ['scriptid'],
					'has.scriptid' => ['get_inherit_b_rw', 'get_inherit_a_r']
				]
			],

			// Host is in a child group and script from parent group is inherited.
			'Test script.get child group host' => [
				'request' => [
					'output' => ['scriptid'],
					'hostids' => ['inherit_b_r']
				],
				'expected_result' => [
					'result_keys' => ['scriptid'],
					'has.scriptid' => ['get_inherit_b_rw', 'get_inherit_a_r']
				]
			],

			// Child host has 2 inherited scripts but only one of them may not be invoked on parent group.
			'Test script.get child host and top group' => [
				'request' => [
					'output' => ['scriptid'],
					'hostids' => ['inherit_b_r'],
					'groupids' => ['inherit_a_rw']
				],
				'expected_result' => [
					'result_keys' => ['scriptid'],
					'has.scriptid' => ['get_inherit_a_r'],
					'!has.scriptid' => ['get_inherit_b_rw']
				]
			],

			// Child group has 2 inherited scripts but only one of them may not be invoked on parent group host.
			'Test script.get child group and top group host' => [
				'request' => [
					'output' => ['scriptid'],
					'hostids' => ['inherit_a_rw'],
					'groupids' => ['inherit_b_r']
				],
				'expected_result' => [
					'result_keys' => ['scriptid'],
					'has.scriptid' => ['get_inherit_a_r'],
					'!has.scriptid' => ['get_inherit_b_rw']
				]
			],

			// User has no permissions to certain script.
			'Test script.get script permissions by "usrgrpids"' => [
				'request' => [
					'login' => ['user' => 'api_test_user', 'password' => '4P1T3$tEr'],
					'output' => ['scriptid'],
					'usrgrpids' => ['user']
				],
				'expected_result' => [
					'result_keys' => ['scriptid'],
					'has.scriptid' => ['exec_usrgrp_user'],
					'!has.scriptid' => ['exec_usrgrp_admin']
				]
			],

			// Test "selectHosts" option.
			'Test script.get selectHosts' => [
				'request' => [
					'output' => ['scriptid'],
					'hostids' => ['inherit_b_r'],
					'selectHosts' => ['hostid'],
					'preservekeys' => true
				],
				'expected_result' => [
					'result_keys' => ['scriptid', 'hosts'],
					'has.scriptid:hostid' => [
						'get_inherit_a_r' => ['inherit_a_rw', 'inherit_b_r', 'inherit_c_rw', 'inherit_d_rw'],
						'get_inherit_b_rw' => ['inherit_b_r', 'inherit_c_rw', 'inherit_d_rw']
					]
				]
			],

			// User has no write permission for group, but script requires that permission.
			'Test script.get selectHosts permissions' => [
				'request' => [
					'login' => ['user' => 'api_test_admin', 'password' => '4P1T3$tEr'],
					'output' => ['scriptid'],
					'hostids' => ['inherit_b_r'],
					'selectHosts' => ['hostid'],
					'preservekeys' => true
				],
				'expected_result' => [
					'result_keys' => ['scriptid', 'hosts'],
					'has.scriptid:hostid' => [
						'get_inherit_a_r' => ['inherit_a_rw', 'inherit_b_r', 'inherit_c_rw', 'inherit_d_rw'],
						'get_inherit_b_rw' => ['inherit_c_rw', 'inherit_d_rw']
					],
					'!has.scriptid:hostid' => [
						'get_inherit_a_r' => [],
						'get_inherit_b_rw' => ['inherit_a_rw', 'inherit_b_r']
					]
				]
			],

			// Test "selectHostGroups" option.
			'Test script.get selectHostGroups' => [
				'request' => [
					'output' => ['scriptid'],
					'hostids' => ['inherit_b_r'],
					'selectHostGroups' => ['groupid'],
					'preservekeys' => true
				],
				'expected_result' => [
					'result_keys' => ['scriptid', 'hostgroups'],
					'has.scriptid:groupid' => [
						'get_inherit_a_r' => ['inherit_a_rw', 'inherit_b_r', 'inherit_c_rw', 'inherit_d_rw'],
						'get_inherit_b_rw' => ['inherit_b_r', 'inherit_c_rw', 'inherit_d_rw']
					]
				]
			],

			// User has no write permission for group, so that group is not shown.
			'Test script.get selectHostGroups permissions' => [
				'request' => [
					'login' => ['user' => 'api_test_admin', 'password' => '4P1T3$tEr'],
					'output' => ['scriptid'],
					'hostids' => ['inherit_b_r'],
					'selectHostGroups' => ['groupid'],
					'preservekeys' => true
				],
				'expected_result' => [
					'result_keys' => ['scriptid', 'hostgroups'],
					'has.scriptid:groupid' => [
						'get_inherit_a_r' => ['inherit_a_rw', 'inherit_b_r', 'inherit_c_rw', 'inherit_d_rw'],
						'get_inherit_b_rw' => ['inherit_c_rw', 'inherit_d_rw']
					],
					'!has.scriptid:groupid' => [
						'get_inherit_a_r' => [],
						'get_inherit_b_rw' => ['inherit_a_rw', 'inherit_b_r']
					]
				]
			],

			// Test "selectActions" option.
			'Test script.get selectActions' => [
				'request' => [
					'output' => ['scriptid'],
					'hostids' => ['inherit_b_r'],
					'selectActions' => ['actionid'],
					'preservekeys' => true
				],
				'expected_result' => [
					'result_keys' => ['scriptid', 'actions'],
					'has.scriptid:actionid' => [
						'update_action' => ['update']
					]
				]
			],

			// No extra output is present.
			'Test script.get selectHostGroups output' => [
				'request' => [
					'login' => ['user' => 'api_test_admin', 'password' => '4P1T3$tEr'],
					'output' => ['scriptid'],
					'hostids' => ['inherit_b_r'],
					'selectHostGroups' => ['flags']
				],
				'expected_result' => [
					'result_keys' => ['scriptid', 'hostgroups'],
					'groupsObjectProperties' => ['flags']
				]
			]
		];
	}

	/**
	 * Test script.get with various users. Checks if result has keys, has script IDs, host IDs and group IDs.
	 *
	 * @dataProvider getScriptGetInheritance
	 */
	public function testScripts_GetInheritance($request, $expected_result) {
		if (array_key_exists('login', $request)) {
			$this->authorize($request['login']['user'], $request['login']['password']);
			unset($request['login']);
		}

		// Replace ID placeholders with real IDs.
		$request = self::resolveIds($request);
		$expected_result = self::resolveComplexIds($expected_result);

		// Only valid data is requested, so no errors are expected.
		$result = $this->call('script.get', $request, null);
		//$this->enableAuthorization();

		if (array_key_exists('has.scriptid', $expected_result)) {
			$ids = array_column($result['result'], 'scriptid');
			$this->assertEmpty(array_diff($expected_result['has.scriptid'], $ids));
		}

		if (array_key_exists('!has.scriptid', $expected_result)) {
			$ids = array_column($result['result'], 'scriptid');
			$this->assertEquals($expected_result['!has.scriptid'], array_diff($expected_result['!has.scriptid'], $ids));
		}

		if (array_key_exists('has.scriptid:hostid', $expected_result)) {
			foreach ($expected_result['has.scriptid:hostid'] as $scriptid => $hostids) {
				$this->assertTrue(array_key_exists($scriptid, $result['result']), 'expected script ID '.$scriptid);
				$ids = array_column($result['result'][$scriptid]['hosts'], 'hostid');
				$this->assertEmpty(array_diff($hostids, $ids), 'Expected ids: '.implode(',', $hostids));
			}
		}

		if (array_key_exists('!has.scriptid:hostid', $expected_result)) {
			foreach ($expected_result['!has.scriptid:hostid'] as $scriptid => $hostids) {
				$this->assertTrue(array_key_exists($scriptid, $result['result']), 'expected script ID '.$scriptid);
				$ids = array_column($result['result'][$scriptid]['hosts'], 'hostid');
				$this->assertEquals($hostids, array_diff($hostids, $ids));
			}
		}

		if (array_key_exists('has.scriptid:groupid', $expected_result)) {
			foreach ($expected_result['has.scriptid:groupid'] as $scriptid => $groupids) {
				$this->assertTrue(array_key_exists($scriptid, $result['result']), 'expected script ID '.$scriptid);
				$ids = array_column($result['result'][$scriptid]['hostgroups'], 'groupid');
				$this->assertEmpty(array_diff($groupids, $ids), 'Expected ids: '.implode(',', $groupids));
			}
		}

		if (array_key_exists('!has.scriptid:groupid', $expected_result)) {
			foreach ($expected_result['!has.scriptid:groupid'] as $scriptid => $groupids) {
				$this->assertTrue(array_key_exists($scriptid, $result['result']), 'expected script ID '.$scriptid);
				$ids = array_column($result['result'][$scriptid]['hostgroups'], 'groupid');
				$this->assertEquals($groupids, array_diff($groupids, $ids));
			}
		}

		if (array_key_exists('groupsObjectProperties', $expected_result)) {
			sort($expected_result['groupsObjectProperties']);
			foreach ($result['result'] as $script) {
				foreach ($script['hostgroups'] as $group) {
					ksort($group);
					$this->assertEquals($expected_result['groupsObjectProperties'], array_keys($group));
				}
			}
		}

		if (array_key_exists('result_keys', $expected_result)) {
			foreach ($result['result'] as $script) {
				sort($expected_result['result_keys']);
				ksort($script);
				$this->assertEquals($expected_result['result_keys'], array_keys($script));
				if (array_key_exists('parameters', $expected_result)) {
					$this->assertEquals($expected_result['parameters'], $script['parameters']);
				}
			}
		}
	}

	/**
	 * Data provider for script.get. Array contains invalid script parameters.
	 *
	 * @return array
	 */
	public static function getScriptGetInvalid() {
		return [
			// Check expected params.
			'Test script.get unexpected field' => [
				'request' => [
					'abc' => 'abc'
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/": unexpected parameter "abc".'
			],

			// Check "scriptids" field.
			'Test script.get invalid "scriptids" field (empty string)' => [
				'request' => [
					'scriptids' => ''
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/scriptids": an array is expected.'
			],
			'Test script.get invalid "scriptids" field (array with empty string)' => [
				'request' => [
					'scriptids' => ['']
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/scriptids/1": a number is expected.'
			],

			// Check "hostids" field.
			'Test script.get invalid "hostids" field (empty string)' => [
				'request' => [
					'hostids' => ''
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/hostids": an array is expected.'
			],
			'Test script.get invalid "hostids" field (array with empty string)' => [
				'request' => [
					'hostids' => ['']
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/hostids/1": a number is expected.'
			],

			// Check "groupids" field.
			'Test script.get invalid "groupids" field (empty string)' => [
				'request' => [
					'groupids' => ''
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/groupids": an array is expected.'
			],
			'Test script.get invalid "groupids" field (array with string)' => [
				'request' => [
					'groupids' => ['']
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/groupids/1": a number is expected.'
			],

			// Check "usrgrpids" field.
			'Test script.get invalid "usrgrpids" field (empty string)' => [
				'request' => [
					'usrgrpids' => ''
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/usrgrpids": an array is expected.'
			],
			'Test script.get invalid "usrgrpids" field (array with empty string)' => [
				'request' => [
					'usrgrpids' => ['']
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/usrgrpids/1": a number is expected.'
			],

			// Check filter.
			'Test script.get invalid filter (empty string)' => [
				'request' => [
					'filter' => ''
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/filter": an array is expected.'
			],

			// Check unexpected parameters that exist in object, but not in filter.
			'Test script.get unexpected parameter in filter' => [
				'request' => [
					'filter' => [
						'username' => 'username'
					]
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/filter": unexpected parameter "username".'
			],

			// Check "name" in filter.
			'Test script.get invalid parameter "name" in filter (bool)' => [
				'request' => [
					'filter' => [
						'name' => false
					]
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/filter/name": an array is expected.'
			],

			// Check "command" in filter.
			'Test script.get invalid parameter "command" in filter (bool)' => [
				'request' => [
					'filter' => [
						'command' => false
					]
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/filter/command": an array is expected.'
			],

			// Check "confirmation" in filter.
			'Test script.get invalid parameter "confirmation" in filter (bool)' => [
				'request' => [
					'filter' => [
						'confirmation' => false
					]
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/filter/confirmation": an array is expected.'
			],

			// Check "url" in filter.
			'Test script.get invalid parameter "url" in filter (bool)' => [
				'request' => [
					'filter' => [
						'url' => false
					]
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/filter/url": an array is expected.'
			],

			// Check "menu_path" in filter.
			'Test script.get invalid parameter "menu_path" in filter (bool)' => [
				'request' => [
					'filter' => [
						'menu_path' => false
					]
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/filter/menu_path": an array is expected.'
			],

			// Check search.
			'Test script.get invalid search (string)' => [
				'request' => [
					'search' => 'abc'
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/search": an array is expected.'
			],

			// Check unexpected parameters that exist in object, but not in search.
			'Test script.get unexpected parameter in search' => [
				'request' => [
					'search' => [
						'scriptid' => 'scriptid'
					]
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/search": unexpected parameter "scriptid".'
			],

			// Check "name" in search.
			'Test script.get invalid parameter "name" in search (bool)' => [
				'request' => [
					'search' => [
						'name' => false
					]
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/search/name": an array is expected.'
			],

			// Check "command" in search.
			'Test script.get invalid parameter "command" in search (bool)' => [
				'request' => [
					'search' => [
						'command' => false
					]
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/search/command": an array is expected.'
			],

			// Check "url" in search.
			'Test script.get invalid parameter "url" in search (bool)' => [
				'request' => [
					'search' => [
						'url' => false
					]
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/search/url": an array is expected.'
			],

			// Check "description" in search.
			'Test script.get invalid parameter "description" in search (bool)' => [
				'request' => [
					'search' => [
						'description' => false
					]
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/search/description": an array is expected.'
			],

			// Check "confirmation" in search.
			'Test script.get invalid parameter "confirmation" in search (bool)' => [
				'request' => [
					'search' => [
						'confirmation' => false
					]
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/search/confirmation": an array is expected.'
			],

			// Check "username" in search.
			'Test script.get invalid parameter "username" in search (bool)' => [
				'request' => [
					'search' => [
						'username' => false
					]
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/search/username": an array is expected.'
			],

			// Check "menu_path" in search.
			'Test script.get invalid parameter "menu_path" in search (bool)' => [
				'request' => [
					'search' => [
						'menu_path' => false
					]
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/search/menu_path": an array is expected.'
			],

			// Check "output" option.
			'Test script.get invalid parameter "output" (string)' => [
				'request' => [
					'output' => 'abc'
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/output": value must be "'.API_OUTPUT_EXTEND.'".'
			],
			'Test script.get invalid parameter "output"' => [
				'request' => [
					'output' => ['abc']
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/output/1": value must be one of "scriptid", "name", "command", "host_access", "usrgrpid", "groupid", "description", "confirmation", "type", "execute_on", "timeout", "parameters", "scope", "port", "authtype", "username", "password", "publickey", "privatekey", "menu_path", "url", "new_window".'
			],

			// Check "selectHostGroups" option.
			'Test script.get invalid parameter "selectHostGroups" (string)' => [
				'request' => [
					'selectHostGroups' => 'abc'
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/selectHostGroups": value must be "'.API_OUTPUT_EXTEND.'".'
			],
			'Test script.get invalid parameter "selectHostGroups"' => [
				'request' => [
					'selectHostGroups' => ['abc']
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/selectHostGroups/1": value must be one of "groupid", "name", "flags", "uuid".'
			],

			// Check "selectHosts" option.
			'Test script.get invalid parameter "selectHosts" (string)' => [
				'request' => [
					'selectHosts' => 'abc'
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/selectHosts": value must be "'.API_OUTPUT_EXTEND.'".'
			],
			'Test script.get invalid parameter "selectHosts"' => [
				'request' => [
					'selectHosts' => ['abc']
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/selectHosts/1": value must be one of "hostid", "host", "name", "description", "status", "proxy_hostid", "inventory_mode", "flags", "ipmi_authtype", "ipmi_privilege", "ipmi_username", "ipmi_password", "maintenanceid", "maintenance_status", "maintenance_type", "maintenance_from", "tls_connect", "tls_accept", "tls_issuer", "tls_subject".'
			],

			// Check "selectActions" option.
			'Test script.get invalid parameter "selectActions" (string)' => [
				'request' => [
					'selectActions' => 'abc'
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/selectActions": value must be "'.API_OUTPUT_EXTEND.'".'
			],
			'Test script.get invalid parameter "selectActions"' => [
				'request' => [
					'selectActions' => ['abc']
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/selectActions/1": value must be one of "actionid", "name", "eventsource", "status", "esc_period", "pause_suppressed", "notify_if_canceled", "pause_symptoms".'
			],

			// Check common fields that are not flags, but require strict validation.
			'Test script.get invalid parameter "searchByAny" (string)' => [
				'request' => [
					'searchByAny' => 'abc'
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/searchByAny": a boolean is expected.'
			],
			'Test script.get invalid parameter "searchWildcardsEnabled" (string)' => [
				'request' => [
					'searchWildcardsEnabled' => 'abc'
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/searchWildcardsEnabled": a boolean is expected.'
			],
			'Test script.get invalid parameter "sortfield" (bool)' => [
				'request' => [
					'sortfield' => false
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/sortfield": an array is expected.'
			],
			'Test script.get invalid parameter "sortfield"' => [
				'request' => [
					'sortfield' => 'abc'
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/sortfield/1": value must be one of "scriptid", "name".'
			],
			'Test script.get invalid parameter "sortorder" (bool)' => [
				'request' => [
					'sortorder' => false
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/sortorder": an array or a character string is expected.'
			],
			'Test script.get invalid parameter "sortorder"' => [
				'request' => [
					'sortorder' => 'abc'
				],
				'expected_results' => [],
				'expected_error' =>
					'Invalid parameter "/sortorder": value must be one of "'.ZBX_SORT_UP.'", "'.ZBX_SORT_DOWN.'".'
			],
			'Test script.get invalid parameter "limit" (bool)' => [
				'request' => [
					'limit' => false
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/limit": an integer is expected.'
			],
			'Test script.get invalid parameter "editable" (string)' => [
				'request' => [
					'editable' => 'abc'
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/editable": a boolean is expected.'
			],
			'Test script.get invalid parameter "preservekeys" (string)' => [
				'request' => [
					'preservekeys' => 'abc'
				],
				'expected_results' => [],
				'expected_error' => 'Invalid parameter "/preservekeys": a boolean is expected.'
			]
		];
	}

	/**
	 * Data provider for script.get. Array contains valid script parameters.
	 *
	 * @return array
	 */
	public static function getScriptGetValid() {
		return [
			// Check validity if "scriptids" without getting any results.
			'Test script.get empty "scriptids" parameter' => [
				'request' => [
					'scriptids' => []
				],
				'expected_results' => [],
				'expected_error' => null
			],

			// No fields are returned on empty selection.
			'Test script.get empty output' => [
				'request' => [
					'output' => [],
					'scriptids' => ['get_custom_defaults']
				],
				'expected_results' => [[]],
				'expected_error' => null
			],

			// Get scripts parameters.
			'Test script.get parameters' => [
				'request' => [
					'output' => ['scriptid', 'parameters'],
					'scriptids' => ['get_webhook_filter']
				],
				'expected_results' => [
					[
						'scriptid' => 'get_webhook_filter',
						'parameters' => [
							[
								'name' => 'parameter one',
								'value' => ''
							],
							[
								'name' => 'parameter two',
								'value' => 'value 2'
							],
							[
								'name' => 'parameter three',
								'value' => 'value 3'
							]
						]
					]
				],
				'expected_error' => null
			],

			// Filter webhooks.
			'Test script.get filter webhooks' => [
				'request' => [
					'output' => [ 'scriptid', 'name', 'command', 'parameters'],
					'scriptids' => ['get_ipmi_defaults', 'get_webhook_filter'],
					'filter' => ['type' => ZBX_SCRIPT_TYPE_WEBHOOK]
				],
				'expected_results' => [
					[
						'scriptid' => 'get_webhook_filter',
						'name' => 'API test script.get for filter webhooks and parameters',
						'command' => 'reboot server',
						'parameters' => [
							[
								'name' => 'parameter one',
								'value' => ''
							],
							[
								'name' => 'parameter two',
								'value' => 'value 2'
							],
							[
								'name' => 'parameter three',
								'value' => 'value 3'
							]
						]
					]
				],
				'expected_error' => null
			],

			// Filter IPMI.
			'Test script.get filter IPMI' => [
				'request' => [
					'output' => ['scriptid', 'command'],
					'scriptids' => ['get_ipmi_defaults', 'get_webhook_filter'],
					'filter' => ['type' => ZBX_SCRIPT_TYPE_IPMI]
				],
				'expected_results' => [
					[
						'scriptid' => 'get_ipmi_defaults',
						'command' => 'reboot server'
					]
				],
				'expected_error' => null
			],

			// Search URL type scripts (macros are not resolved).
			'Test script.get search URL' => [
				'request' => [
					'output' => ['name', 'url'],
					'search' => ['url' => 'http://zabbix']
				],
				'expected_results' => [
					[
						'name' => 'API test script.get URL',
						'url' => 'http://zabbix/ui/zabbix.php?action=host.edit&hostid={HOST.ID}'
					],
					[
						'name' => 'API test script.getScriptsByHosts - URL',
						'url' => 'http://zabbix/ui/zabbix.php?action=host.edit&hostid={HOST.ID}'
					],
					[
						'name' => 'API test script.getScriptsByEvents - URL',
						'url' => 'http://zabbix/ui/zabbix.php?action=host.edit&hostid={HOST.ID}'
					],
					[
						'name' => 'API test script.getScriptsByEvents - URL cause',
						'url' => 'http://zabbix/ui/tr_events.php?eventid={EVENT.ID}'
					]
				],
				'expected_error' => null
			],

			// Check "scriptid" in filter.
			'Test script.get invalid parameter "scriptid" in filter (empty string)' => [
				'request' => [
					'filter' => [
						'scriptid' => ''
					]
				],
				'expected_results' => [],
				'expected_error' => null
			],
			'Test script.get invalid parameter "scriptid" in filter (array)' => [
				'request' => [
					'filter' => [
						'scriptid' => ['']
					]
				],
				'expected_results' => [],
				'expected_error' => null
			],

			// Check "host_access" in filter.
			'Test script.get invalid parameter "host_access" in filter (string)' => [
				'request' => [
					'filter' => [
						'host_access' => 'abc'
					]
				],
				'expected_results' => [],
				'expected_error' => null
			],
			'Test script.get invalid parameter "host_access" in filter (array)' => [
				'request' => [
					'filter' => [
						'host_access' => ['abc']
					]
				],
				'expected_results' => [],
				'expected_error' => null
			],

			// Check "usrgrpid" in filter.
			'Test script.get invalid parameter "usrgrpid" in filter (empty string)' => [
				'request' => [
					'filter' => [
						'usrgrpid' => ''
					]
				],
				'expected_results' => [],
				'expected_error' => null
			],
			'Test script.get invalid parameter "usrgrpid" in filter (array)' => [
				'request' => [
					'filter' => [
						'usrgrpid' => ['']
					]
				],
				'expected_results' => [],
				'expected_error' => null
			],

			// Check "groupid" in filter.
			'Test script.get invalid parameter "groupid" in filter (empty string)' => [
				'request' => [
					'filter' => [
						'groupid' => ''
					]
				],
				'expected_results' => [],
				'expected_error' => null
			],
			'Test script.get invalid parameter "groupid" in filter (array)' => [
				'request' => [
					'filter' => [
						'groupid' => ['']
					]
				],
				'expected_results' => [],
				'expected_error' => null
			],

			// Check "type" in filter.
			'Test script.get invalid parameter "type" in filter (string)' => [
				'request' => [
					'filter' => [
						'type' => 'abc'
					]
				],
				'expected_results' => [],
				'expected_error' => null
			],
			'Test script.get invalid parameter "type" in filter (array)' => [
				'request' => [
					'filter' => [
						'type' => ['abc']
					]
				],
				'expected_results' => [],
				'expected_error' => null
			],
			'Test script.get invalid parameter "type" in filter' => [
				'request' => [
					'filter' => [
						'type' => 999999
					]
				],
				'expected_results' => [],
				'expected_error' => null
			],

			// Check "new_window" in filter.
			'Test script.get invalid parameter "new_window" in filter (string)' => [
				'request' => [
					'filter' => [
						'new_window' => 'abc'
					]
				],
				'expected_results' => [],
				'expected_error' => null
			],
			'Test script.get invalid parameter "new_window" in filter (array)' => [
				'request' => [
					'filter' => [
						'new_window' => ['abc']
					]
				],
				'expected_results' => [],
				'expected_error' => null
			],
			'Test script.get invalid parameter "new_window" in filter' => [
				'request' => [
					'filter' => [
						'new_window' => 999999
					]
				],
				'expected_results' => [],
				'expected_error' => null
			],

			// Check "execute_on" in filter.
			'Test script.get invalid parameter "execute_on" in filter (string)' => [
				'request' => [
					'filter' => [
						'execute_on' => 'abc'
					]
				],
				'expected_results' => [],
				'expected_error' => null
			],
			'Test script.get invalid parameter "execute_on" in filter (array)' => [
				'request' => [
					'filter' => [
						'execute_on' => ['abc']
					]
				],
				'expected_results' => [],
				'expected_error' => null
			],
			'Test script.get invalid parameter "execute_on" in filter' => [
				'request' => [
					'filter' => [
						'execute_on' => 999999
					]
				],
				'expected_results' => [],
				'expected_error' => null
			],

			// Check "scope" in filter.
			'Test script.get invalid parameter "scope" in filter (string)' => [
				'request' => [
					'filter' => [
						'scope' => 'abc'
					]
				],
				'expected_results' => [],
				'expected_error' => null
			],
			'Test script.get invalid parameter "scope" in filter (array)' => [
				'request' => [
					'filter' => [
						'scope' => ['abc']
					]
				],
				'expected_results' => [],
				'expected_error' => null
			],
			'Test script.get invalid parameter "scope" in filter' => [
				'request' => [
					'filter' => [
						'scope' => 999999
					]
				],
				'expected_results' => [],
				'expected_error' => null
			]
		];
	}

	/**
	 * Test script.get with all options.
	 *
	 * @dataProvider getScriptGetInvalid
	 * @dataProvider getScriptGetValid
	 */
	public function testScripts_Get($request, $expected_results, $expected_error) {
		// Replace ID placeholders with real IDs.
		$request = self::resolveIds($request);

		foreach ($expected_results as &$script) {
			$script = self::resolveIds($script);
		}
		unset($script);

		$result = $this->call('script.get', $request, $expected_error);

		if ($expected_error === null) {
			foreach ($expected_results as &$script) {
				// Check Webhook parameters.
				if (array_key_exists('parameters', $script)) {
					foreach ($result['result'] as &$script_) {
						if (bccomp($script_['scriptid'], $script['scriptid']) == 0) {
							$this->assertEqualsCanonicalizing($script['parameters'], $script_['parameters']);
							unset($script['parameters'], $script_['parameters']);
						}
					}
					unset($script_);
				}
			}
			unset($script);

			$this->assertSame($expected_results, $result['result']);
		}
	}

	/**
	 * Data provider for script.update. Array contains invalid script parameters.
	 *
	 * @return array
	 */
	public static function getScriptUpdateInvalid() {
		return [
			// Check script ID.
			'Test script.update empty request' => [
				'script' => [],
				'expected_error' => 'Invalid parameter "/": cannot be empty.'
			],
			'Test script.update missing ID' => [
				'script' => [[
					'name' => 'API updated script',
					'command' => 'reboot'
				]],
				'expected_error' => 'Invalid parameter "/1": the parameter "scriptid" is missing.'
			],
			'Test script.update empty ID' => [
				'script' => [[
					'scriptid' => '',
					'name' => 'API updated script',
					'command' => 'reboot'
				]],
				'expected_error' => 'Invalid parameter "/1/scriptid": a number is expected.'
			],
			'Test script.update invalid ID (non-existent)' => [
				'script' => [[
					'scriptid' => 999999,
					'name' => 'API updated script',
					'command' => 'reboot'
				]],
				'expected_error' => 'No permissions to referred object or it does not exist!'
			],

			// Check script name.
			'Test script.update empty name' => [
				'script' => [[
					'scriptid' => 'update_ipmi',
					'name' => ''
				]],
				'expected_error' => 'Invalid parameter "/1/name": cannot be empty.'
			],
			'Test script.update existing name' => [
				'script' => [[
					'scriptid' => 'update_telnet',
					'name' => 'API test script.update - IPMI',
					'type' => ZBX_SCRIPT_TYPE_IPMI,
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'command' => 'reboot server'
				]],
				'expected_error' => 'Script "API test script.update - IPMI" already exists.'
			],
			'Test script.update same name' => [
				'script' => [
					[
						'scriptid' => 'update_ipmi',
						'name' => 'Scripts with the same name'
					],
					[
						'scriptid' => 'update_telnet',
						'name' => 'Scripts with the same name'
					]
				],
				'expected_error' => 'Invalid parameter "/2": value (name)=(Scripts with the same name) already exists.'
			],

			// Check script command.
			'Test script.update empty command' => [
				'script' => [[
					'scriptid' => 'update_ipmi',
					'command' => ''
				]],
				'expected_error' => 'Invalid parameter "/1/command": cannot be empty.'
			],

			// Check script type.
			'Test script.update invalid type (empty string)' => [
				'script' => [
					'scriptid' => 'update_ipmi',
					'type' => ''
				],
				'expected_error' => 'Invalid parameter "/1/type": an integer is expected.'
			],
			'Test script.update invalid type (string)' => [
				'script' => [
					'scriptid' => 'update_ipmi',
					'type' => 'abc'
				],
				'expected_error' => 'Invalid parameter "/1/type": an integer is expected.'
			],
			'Test script.update invalid type' => [
				'script' => [
					'scriptid' => 'update_ipmi',
					'type' => 999999
				],
				'expected_error' => 'Invalid parameter "/1/type": value must be one of '.
					implode(', ', [ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT, ZBX_SCRIPT_TYPE_IPMI, ZBX_SCRIPT_TYPE_SSH,
						ZBX_SCRIPT_TYPE_TELNET, ZBX_SCRIPT_TYPE_WEBHOOK, ZBX_SCRIPT_TYPE_URL
					]).'.'
			],
			'Test script.update invalid type for wrong scope' => [
				'script' => [
					'scriptid' => 'update_ipmi',
					'type' => ZBX_SCRIPT_TYPE_URL
				],
				'expected_error' => 'Invalid parameter "/1/scope": value must be one of '.
					implode(', ', [ZBX_SCRIPT_SCOPE_HOST, ZBX_SCRIPT_SCOPE_EVENT]).'.'
			],

			// Check script scope.
			'Test script.update invalid scope (empty string)' => [
				'script' => [
					'scriptid' => 'update_ipmi',
					'scope' => ''
				],
				'expected_error' => 'Invalid parameter "/1/scope": an integer is expected.'
			],
			'Test script.update invalid scope (string)' => [
				'script' => [
					'scriptid' => 'update_ipmi',
					'scope' => 'abc'
				],
				'expected_error' => 'Invalid parameter "/1/scope": an integer is expected.'
			],
			'Test script.update invalid scope' => [
				'script' => [
					'scriptid' => 'update_ipmi',
					'scope' => 999999
				],
				'expected_error' => 'Invalid parameter "/1/scope": value must be one of '.
					implode(', ', [ZBX_SCRIPT_SCOPE_ACTION, ZBX_SCRIPT_SCOPE_HOST, ZBX_SCRIPT_SCOPE_EVENT]).'.'
			],
			'Test script.update invalid scope for wrong type' => [
				'script' => [
					'scriptid' => 'update_url',
					'scope' => ZBX_SCRIPT_SCOPE_ACTION
				],
				'expected_error' => 'Invalid parameter "/1/scope": value must be one of '.
					implode(', ', [ZBX_SCRIPT_SCOPE_HOST, ZBX_SCRIPT_SCOPE_EVENT]).'.'
			],
			'Test script.update scope change assigned to action' => [
				'script' => [
					'scriptid' => 'update_action',
					'scope' => ZBX_SCRIPT_SCOPE_HOST
				],
				'expected_error' => 'Cannot update script scope. Script "API test script.update action" is used in action "API test script.update action".'
			],

			// Check script menu path.
			'Test script.update invalid "menu_path" field' => [
				'script' => [
					'scriptid' => 'update_ipmi_host',
					'menu_path' => 'folder1/folder2/'.'/folder4'
				],
				'expected_error' => 'Invalid parameter "/1/menu_path": directory cannot be empty.'
			],
			'Test script.update unexpected "menu_path" field (change of scope)' => [
				'script' => [
					'scriptid' => 'update_ipmi_host',
					'scope' => ZBX_SCRIPT_SCOPE_ACTION,
					'menu_path' => 'folder1/folder2/'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "menu_path".'
			],

			// Check script host access.
			'Test script.update unexpected "host_access" field for action scope (empty string)' => [
				'script' => [
					'scriptid' => 'update_ipmi',
					'host_access' => ''
				],
				// Must be changed in future if CApiInputValidator is improved.
				'expected_error' => 'Invalid parameter "/1/host_access": an integer is expected.'
			],
			'Test script.update invalid "host_access" field (empty string)' => [
				'script' => [
					'scriptid' => 'update_ipmi_host',
					'host_access' => ''
				],
				'expected_error' => 'Invalid parameter "/1/host_access": an integer is expected.'
			],
			'Test script.update invalid "host_access" field (string)' => [
				'script' => [
					'scriptid' => 'update_ipmi_host',
					'host_access' => 'abc'
				],
				'expected_error' => 'Invalid parameter "/1/host_access": an integer is expected.'
			],
			'Test script.update invalid "host_access" field' => [
				'script' => [
					'scriptid' => 'update_ipmi_host',
					'host_access' => 999999
				],
				'expected_error' => 'Invalid parameter "/1/host_access": value must be one of '.
					implode(', ', [PERM_READ, PERM_READ_WRITE]).'.'
			],

			// Check script user group.
			'Test script.update unexpected "usrgrpid" field for action scope (empty string)' => [
				'script' => [
					'scriptid' => 'update_ipmi',
					'usrgrpid' => ''
				],
				// Must be changed in future if CApiInputValidator is improved.
				'expected_error' => 'Invalid parameter "/1/usrgrpid": a number is expected.'
			],
			'Test script.update unexpected "usrgrpid" field for action scope (int)' => [
				'script' => [
					'scriptid' => 'update_ipmi',
					'usrgrpid' => 0
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "usrgrpid".'
			],
			'Test script.update invalid "usrgrpid" field for host scope (empty string)' => [
				'script' => [
					'scriptid' => 'update_ipmi_host',
					'usrgrpid' => ''
				],
				'expected_error' => 'Invalid parameter "/1/usrgrpid": a number is expected.'
			],
			'Test script.update invalid "usrgrpid" field for host scope' => [
				'script' => [
					'scriptid' => 'update_ipmi_host',
					'usrgrpid' => 999999
				],
				'expected_error' => 'User group with ID "999999" is not available.'
			],

			// Check script confirmation.
			'Test script.update unexpected confirmation for action scope' => [
				'script' => [
					'scriptid' => 'update_ipmi',
					'confirmation' => ''
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "confirmation".'
			],

			// Check script host group.
			'Test script.update invalid host group (empty string)' => [
				'script' => [
					'scriptid' => 'update_ipmi',
					'groupid' => ''
				],
				'expected_error' => 'Invalid parameter "/1/groupid": a number is expected.'
			],
			'Test script.update invalid host group' => [
				'script' => [
					'scriptid' => 'update_ipmi',
					'groupid' => 999999
				],
				'expected_error' => 'Host group with ID "999999" is not available.'
			],

			// Check unexpected fields in script.
			'Test script.update unexpected field' => [
				'script' => [
					'scriptid' => 'update_ipmi',
					'unexpected_field' => ''
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "unexpected_field".'
			],

			// Check script execute_on.
			'Test script.update invalid "execute_on" field (empty string)' => [
				'script' => [
					'scriptid' => 'update_custom',
					'execute_on' => ''
				],
				'expected_error' => 'Invalid parameter "/1/execute_on": an integer is expected.'
			],
			'Test script.update invalid "execute_on" field (string)' => [
				'script' => [
					'scriptid' => 'update_custom',
					'execute_on' => 'abc'
				],
				'expected_error' => 'Invalid parameter "/1/execute_on": an integer is expected.'
			],
			'Test script.update invalid "execute_on" field' => [
				'script' => [
					'scriptid' => 'update_custom',
					'execute_on' => 999999
				],
				'expected_error' => 'Invalid parameter "/1/execute_on": value must be one of '.
					implode(', ', [ZBX_SCRIPT_EXECUTE_ON_AGENT, ZBX_SCRIPT_EXECUTE_ON_SERVER,
						ZBX_SCRIPT_EXECUTE_ON_PROXY
					]).'.'
			],
			'Test script.update unexpected "execute_on" field for IPMI type (empty string)' => [
				'script' => [
					'scriptid' => 'update_ipmi',
					'execute_on' => ''
				],
				// Must be changed in future if CApiInputValidator is improved.
				'expected_error' => 'Invalid parameter "/1/execute_on": an integer is expected.'
			],
			'Test script.update unexpected "execute_on" field for IPMI type' => [
				'script' => [
					'scriptid' => 'update_ipmi',
					'execute_on' => ZBX_SCRIPT_EXECUTE_ON_AGENT
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "execute_on".'
			],
			'Test script.update unexpected "execute_on" field for SSH type' => [
				'script' => [
					'scriptid' => 'update_ssh_pwd',
					'execute_on' => ZBX_SCRIPT_EXECUTE_ON_AGENT
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "execute_on".'
			],
			'Test script.update unexpected "execute_on" field for Telnet type' => [
				'script' => [
					'scriptid' => 'update_telnet',
					'execute_on' => ZBX_SCRIPT_EXECUTE_ON_AGENT
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "execute_on".'
			],
			'Test script.update unexpected "execute_on" field for Webhook type' => [
				'script' => [
					'scriptid' => 'update_webhook',
					'execute_on' => ZBX_SCRIPT_EXECUTE_ON_AGENT
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "execute_on".'
			],
			'Test script.update unexpected "execute_on" field for URL type' => [
				'script' => [
					'scriptid' => 'update_url',
					'execute_on' => ZBX_SCRIPT_EXECUTE_ON_AGENT
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "execute_on".'
			],

			// Check script port.
			'Test script.update invalid port (string)' => [
				'script' => [
					'scriptid' => 'update_ssh_pwd',
					'port' => 'abc'
				],
				'expected_error' => 'Invalid parameter "/1/port": an integer is expected.'
			],
			'Test script.update invalid port (not macro)' => [
				'script' => [
					'scriptid' => 'update_ssh_pwd',
					'port' => '{$NOT_MACRO'
				],
				'expected_error' => 'Invalid parameter "/1/port": an integer is expected.'
			],
			'Test script.update invalid port' => [
				'script' => [
					'scriptid' => 'update_ssh_pwd',
					'port' => 999999
				],
				'expected_error' => 'Invalid parameter "/1/port": value must be one of '.
					ZBX_MIN_PORT_NUMBER.'-'.ZBX_MAX_PORT_NUMBER.'.'
			],
			'Test script.update unexpected port field for custom script type (empty string)' => [
				'script' => [
					'scriptid' => 'update_custom',
					'port' => ''
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "port".'
			],
			'Test script.update unexpected port field for custom script type' => [
				'script' => [
					'scriptid' => 'update_custom',
					'port' => 0
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "port".'
			],
			'Test script.update unexpected port field for IPMI type' => [
				'script' => [
					'scriptid' => 'update_ipmi',
					'port' => 0
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "port".'
			],
			'Test script.update unexpected port field for Webhook type' => [
				'script' => [
					'scriptid' => 'update_webhook',
					'port' => 0
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "port".'
			],
			'Test script.update unexpected port field for URL type' => [
				'script' => [
					'scriptid' => 'update_url',
					'port' => 0
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "port".'
			],

			// Check script auth type.
			'Test script.update invalid "authtype" field (empty string)' => [
				'script' => [
					'scriptid' => 'update_ssh_key',
					'authtype' => ''
				],
				'expected_error' => 'Invalid parameter "/1/authtype": an integer is expected.'
			],
			'Test script.update invalid "authtype" field (string)' => [
				'script' => [
					'scriptid' => 'update_ssh_key',
					'authtype' => 'abc'
				],
				'expected_error' => 'Invalid parameter "/1/authtype": an integer is expected.'
			],
			'Test script.update invalid "authtype" field' => [
				'script' => [
					'scriptid' => 'update_ssh_key',
					'authtype' => 999999
				],
				'expected_error' => 'Invalid parameter "/1/authtype": value must be one of '.
					implode(', ', [ITEM_AUTHTYPE_PASSWORD, ITEM_AUTHTYPE_PUBLICKEY]).'.'
			],
			'Test script.update unexpected "authtype" field for custom script type (empty string)' => [
				'script' => [
					'scriptid' => 'update_custom',
					'authtype' => ''
				],
				// Must be changed in future if CApiInputValidator is improved.
				'expected_error' => 'Invalid parameter "/1/authtype": an integer is expected.'
			],
			'Test script.update unexpected "authtype" field for custom script type' => [
				'script' => [
					'scriptid' => 'update_custom',
					'authtype' => ITEM_AUTHTYPE_PASSWORD
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "authtype".'
			],
			'Test script.update unexpected "authtype" field for IPMI type' => [
				'script' => [
					'scriptid' => 'update_ipmi',
					'authtype' => ITEM_AUTHTYPE_PASSWORD
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "authtype".'
			],
			'Test script.update unexpected "authtype" field for Telnet type' => [
				'script' => [
					'scriptid' => 'update_telnet',
					'authtype' => ITEM_AUTHTYPE_PASSWORD
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "authtype".'
			],
			'Test script.update unexpected "authtype" field for Webhook type' => [
				'script' => [
					'scriptid' => 'update_webhook',
					'authtype' => ITEM_AUTHTYPE_PASSWORD
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "authtype".'
			],
			'Test script.update unexpected "authtype" field for URL type' => [
				'script' => [
					'scriptid' => 'update_url',
					'authtype' => ITEM_AUTHTYPE_PASSWORD
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "authtype".'
			],

			// Check script username.
			'Test script.update empty username for SSH type' => [
				'script' => [
					'scriptid' => 'update_ssh_pwd',
					'username' => ''
				],
				'expected_error' => 'Invalid parameter "/1/username": cannot be empty.'
			],
			'Test script.update empty username for Telnet type' => [
				'script' => [
					'scriptid' => 'update_telnet',
					'username' => ''
				],
				'expected_error' => 'Invalid parameter "/1/username": cannot be empty.'
			],
			'Test script.update unexpected username for custom script type (empty string)' => [
				'script' => [
					'scriptid' => 'update_custom',
					'username' => ''
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "username".'
			],
			'Test script.update unexpected username for custom script type' => [
				'script' => [
					'scriptid' => 'update_custom',
					'username' => 'John'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "username".'
			],
			'Test script.update unexpected username for IPMI type' => [
				'script' => [
					'scriptid' => 'update_ipmi',
					'username' => 'John'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "username".'
			],
			'Test script.update unexpected username for Webhook type' => [
				'script' => [
					'scriptid' => 'update_webhook',
					'username' => 'John'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "username".'
			],
			'Test script.update unexpected username for URL type' => [
				'script' => [
					'scriptid' => 'update_webhook',
					'username' => 'John'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "username".'
			],

			// Check script password.
			'Test script.update unexpected password for custom script type (empty string)' => [
				'script' => [
					'scriptid' => 'update_custom',
					'password' => ''
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "password".'
			],
			'Test script.update unexpected password for custom script type' => [
				'script' => [
					'scriptid' => 'update_custom',
					'password' => 'psswd'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "password".'
			],
			'Test script.update unexpected password for IPMI type' => [
				'script' => [
					'scriptid' => 'update_ipmi',
					'password' => 'psswd'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "password".'
			],
			'Test script.update unexpected password for Webhook type' => [
				'script' => [
					'scriptid' => 'update_webhook',
					'password' => 'psswd'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "password".'
			],
			'Test script.update unexpected password for URL type' => [
				'script' => [
					'scriptid' => 'update_url',
					'password' => 'psswd'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "password".'
			],

			// Check script public key.
			'Test script.update empty "publickey" field' => [
				'script' => [
					'scriptid' => 'update_ssh_key',
					'publickey' => ''
				],
				'expected_error' => 'Invalid parameter "/1/publickey": cannot be empty.'
			],
			'Test script.update unexpected "publickey" field for SSH password type' => [
				'script' => [
					'scriptid' => 'update_ssh_pwd',
					'publickey' => ''
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "publickey".'
			],
			'Test script.update unexpected "publickey" field for custom script type (empty string)' => [
				'script' => [
					'scriptid' => 'update_custom',
					'publickey' => ''
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "publickey".'
			],
			'Test script.update unexpected "publickey" field for custom script type' => [
				'script' => [
					'scriptid' => 'update_custom',
					'publickey' => 'secretpubkey'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "publickey".'
			],
			'Test script.update unexpected "publickey" field for IPMI type' => [
				'script' => [
					'scriptid' => 'update_ipmi',
					'publickey' => 'secretpubkey'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "publickey".'
			],
			'Test script.update unexpected "publickey" field for Telnet type' => [
				'script' => [
					'scriptid' => 'update_telnet',
					'publickey' => 'secretpubkey'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "publickey".'
			],
			'Test script.update unexpected "publickey" field for Webhook type' => [
				'script' => [
					'scriptid' => 'update_webhook',
					'publickey' => 'secretpubkey'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "publickey".'
			],
			'Test script.update unexpected "publickey" field for URL type' => [
				'script' => [
					'scriptid' => 'update_url',
					'publickey' => 'secretpubkey'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "publickey".'
			],

			// Check script private key.
			'Test script.update empty "privatekey" field' => [
				'script' => [
					'scriptid' => 'update_ssh_key',
					'privatekey' => ''
				],
				'expected_error' => 'Invalid parameter "/1/privatekey": cannot be empty.'
			],
			'Test script.update unexpected "privatekey" field for SSH password type' => [
				'script' => [
					'scriptid' => 'update_ssh_pwd',
					'privatekey' => ''
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "privatekey".'
			],
			'Test script.update unexpected "privatekey" field for custom script type (empty string)' => [
				'script' => [
					'scriptid' => 'update_custom',
					'privatekey' => ''
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "privatekey".'
			],
			'Test script.update unexpected "privatekey" field for custom script type' => [
				'script' => [
					'scriptid' => 'update_custom',
					'privatekey' => 'secretprivkey'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "privatekey".'
			],
			'Test script.update unexpected "privatekey" field for IPMI type' => [
				'script' => [
					'scriptid' => 'update_ipmi',
					'privatekey' => 'secretprivkey'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "privatekey".'
			],
			'Test script.update unexpected "privatekey" field for Telnet type' => [
				'script' => [
					'scriptid' => 'update_telnet',
					'privatekey' => 'secretprivkey'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "privatekey".'
			],
			'Test script.update unexpected "privatekey" field for Webhook type' => [
				'script' => [
					'scriptid' => 'update_webhook',
					'privatekey' => 'secretprivkey'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "privatekey".'
			],
			'Test script.update unexpected "privatekey" field for URL type' => [
				'script' => [
					'scriptid' => 'update_url',
					'privatekey' => 'secretprivkey'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "privatekey".'
			],

			// Check script timeout.
			'Test script.update invalid timeout' => [
				'script' => [
					'scriptid' => 'update_webhook',
					'timeout' => '100'
				],
				'expected_error' => 'Invalid parameter "/1/timeout": value must be one of 1-'.SEC_PER_MIN.'.'
			],
			'Test script.update unsupported macros in timeout' => [
				'script' => [
					'scriptid' => 'update_webhook',
					'timeout' => '{$MACRO}'
				],
				'expected_error' => 'Invalid parameter "/1/timeout": a time unit is expected.'
			],
			'Test script.update unexpected timeout field for custom script type (empty string)' => [
				'script' => [
					'scriptid' => 'update_custom',
					'timeout' => ''
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "timeout".'
			],
			'Test script.update unexpected timeout field for custom script type' => [
				'script' => [
					'scriptid' => 'update_custom',
					'timeout' => '30s'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "timeout".'
			],
			'Test script.update unexpected timeout field for IPMI type' => [
				'script' => [
					'scriptid' => 'update_ipmi',
					'timeout' => '30s'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "timeout".'
			],
			'Test script.update unexpected timeout field for SSH type' => [
				'script' => [
					'scriptid' => 'update_ssh_pwd',
					'timeout' => '30s'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "timeout".'
			],
			'Test script.update unexpected timeout field for Telnet type' => [
				'script' => [
					'scriptid' => 'update_telnet',
					'timeout' => '30s'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "timeout".'
			],
			'Test script.update unexpected timeout field for URL type' => [
				'script' => [
					'scriptid' => 'update_url',
					'timeout' => '30s'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "timeout".'
			],

			// Check script parameters.
			'Test script.update invalid parameters' => [
				'script' => [
					'scriptid' => 'update_webhook',
					'parameters' => ''
				],
				'expected_error' => 'Invalid parameter "/1/parameters": an array is expected.'
			],
			'Test script.update missing name in parameters' => [
				'script' => [
					'scriptid' => 'update_webhook',
					'parameters' => [[]]
				],
				'expected_error' => 'Invalid parameter "/1/parameters/1": the parameter "name" is missing.'
			],
			'Test script.update empty name in parameters' => [
				'script' => [
					'scriptid' => 'update_webhook',
					'parameters' => [[
						'name' => ''
					]]
				],
				'expected_error' => 'Invalid parameter "/1/parameters/1/name": cannot be empty.'
			],
			'Test script.update missing value in parameters' => [
				'script' => [
					'scriptid' => 'update_webhook',
					'parameters' => [[
						'name' => 'param x'
					]]
				],
				'expected_error' => 'Invalid parameter "/1/parameters/1": the parameter "value" is missing.'
			],
			'Test script.update unexpected parameters for custom script type (empty array)' => [
				'script' => [
					'scriptid' => 'update_custom',
					'parameters' => []
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "parameters".'
			],
			'Test script.update unexpected parameters for custom script type (empty sub-params)' => [
				'script' => [
					'scriptid' => 'update_custom',
					'parameters' => [[]]
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "parameters".'
			],
			'Test script.update unexpected parameters for custom script type (string)' => [
				'script' => [
					'scriptid' => 'update_custom',
					'parameters' => ''
				],
				// Must be changed in future if CApiInputValidator is improved.
				'expected_error' => 'Invalid parameter "/1/parameters": an array is expected.'
			],
			'Test script.update unexpected parameters for custom script type' => [
				'script' => [
					'scriptid' => 'update_custom',
					'parameters' => [[
						'name' => 'param1',
						'value' => 'value1'
					]]
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "parameters".'
			],
			'Test script.update unexpected parameters for IPMI type' => [
				'script' => [
					'scriptid' => 'update_ipmi',
					'parameters' => [[
						'name' => 'param1',
						'value' => 'value1'
					]]
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "parameters".'
			],
			'Test script.update unexpected parameters for SSH type' => [
				'script' => [
					'scriptid' => 'update_ssh_pwd',
					'parameters' => [[
						'name' => 'param1',
						'value' => 'value1'
					]]
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "parameters".'
			],
			'Test script.update unexpected parameters for Telnet type' => [
				'script' => [
					'scriptid' => 'update_telnet',
					'parameters' => [[
						'name' => 'param1',
						'value' => 'value1'
					]]
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "parameters".'
			],
			'Test script.update unexpected parameters for URL type' => [
				'script' => [
					'scriptid' => 'update_url',
					'parameters' => [[
						'name' => 'param1',
						'value' => 'value1'
					]]
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "parameters".'
			],

			// Check required fields on type change.
			'Test script.update custom change to SSH (missing username)' => [
				'script' => [
					'scriptid' => 'update_custom',
					'type' => ZBX_SCRIPT_TYPE_SSH,
					'command' => 'reboot'
				],
				'expected_error' => 'Invalid parameter "/1": the parameter "username" is missing.'
			],
			'Test script.update custom change to SSH (empty username)' => [
				'script' => [
					'scriptid' => 'update_custom',
					'type' => ZBX_SCRIPT_TYPE_SSH,
					'command' => 'reboot',
					'username' => ''
				],
				'expected_error' => 'Invalid parameter "/1/username": cannot be empty.'
			],
			'Test script.update custom change to SSH (unexpected publickey, empty)' => [
				'script' => [
					'scriptid' => 'update_custom',
					'type' => ZBX_SCRIPT_TYPE_SSH,
					'command' => 'reboot',
					'username' => 'John',
					'publickey' => ''
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "publickey".'
			],
			'Test script.update custom change to SSH (unexpected publickey)' => [
				'script' => [
					'scriptid' => 'update_custom',
					'type' => ZBX_SCRIPT_TYPE_SSH,
					'command' => 'reboot',
					'username' => 'John',
					'publickey' => 'secretpubkey'
				],
				'expected_error' => 'Invalid parameter "/1": unexpected parameter "publickey".'
			],
			'Test script.update custom change to SSH (missing publickey)' => [
				'script' => [
					'scriptid' => 'update_custom',
					'type' => ZBX_SCRIPT_TYPE_SSH,
					'command' => 'reboot',
					'username' => 'John',
					'authtype' => ITEM_AUTHTYPE_PUBLICKEY
				],
				'expected_error' => 'Invalid parameter "/1": the parameter "publickey" is missing.'
			],
			'Test script.update custom change to Telnet (missing username)' => [
				'script' => [
					'scriptid' => 'update_custom',
					'type' => ZBX_SCRIPT_TYPE_TELNET,
					'command' => 'reboot'
				],
				'expected_error' => 'Invalid parameter "/1": the parameter "username" is missing.'
			],
			'Test script.update custom change to URL' => [
				'script' => [
					'scriptid' => 'update_custom',
					'type' => ZBX_SCRIPT_TYPE_URL,
					'scope' => ZBX_SCRIPT_SCOPE_HOST
				],
				'expected_error' => 'Invalid parameter "/1": the parameter "url" is missing.'
			],
			'Test script.update URL change to custom' => [
				'script' => [
					'scriptid' => 'update_url',
					'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT
				],
				'expected_error' => 'Invalid parameter "/1": the parameter "command" is missing.'
			]
		];
	}

	/**
	 * Data provider for script.update. Array contains valid script parameters.
	 *
	 * @return array
	 */
	public static function getScriptUpdateValid() {
		return [
			'Test script.update successful custom script update without changes' => [
				'script' => [
					[
						'scriptid' => 'update_custom'
					]
				],
				'expected_error' => null
			],
			'Test script.update successful multiple updates' => [
				'script' => [
					[
						'scriptid' => 'update_custom',
						'name' => 'API test script.update - custom script updated',
						'command' => 'reboot server 1'
					],
					[
						'scriptid' => 'update_ipmi',
						'name' => 'API test script.update - IPMI updated',
						'command' => 'reboot server 2'
					]
				],
				'expected_error' => null
			],

			// Check update for various script types.
			'Test script.update successful custom script update' => [
				'script' => [
					[
						'scriptid' => 'update_custom',
						'name' => 'Апи скрипт обнавлён утф-8',
						'scope' => ZBX_SCRIPT_SCOPE_HOST,
						'command' => 'shutdown -r',
						'execute_on' => ZBX_SCRIPT_EXECUTE_ON_SERVER,
						'usrgrpid' => 'user',
						'groupid' => 'r',
						'description' => 'Check successful update custom script',
						'confirmation' => 'Do you want to shutdown?',
						'menu_path' => '/root/folder1/',
						'host_access' => PERM_READ_WRITE
					]
				],
				'expected_error' => null
			],
			'Test script.update successful IPMI update' => [
				'script' => [
					[
						'scriptid' => 'update_ipmi',
						'scope' => ZBX_SCRIPT_SCOPE_HOST,
						'name' => 'API test script.update - IPMI updated',
						'command' => 'shutdown -r',
						'host_access' => PERM_READ_WRITE,
						'usrgrpid' => 'user',
						'groupid' => 'r',
						'description' => 'Check successful update IPMI',
						'confirmation' => 'Do you want to shutdown?'
					]
				],
				'expected_error' => null
			],
			'Test script.update successful SSH update with password' => [
				'script' => [
					[
						'scriptid' => 'update_ssh_pwd',
						'scope' => ZBX_SCRIPT_SCOPE_HOST,
						'name' => 'API test script - SSH with password updated',
						'command' => 'shutdown -r',
						'host_access' => PERM_READ_WRITE,
						'usrgrpid' => 'user',
						'groupid' => 'r',
						'description' => 'Check successful update SSH with password',
						'confirmation' => 'Do you want to shutdown?',
						'port' => '{$MACRO}',
						'username' => 'Jill',
						'password' => 'Barry'
					]
				],
				'expected_error' => null
			],
			'Test script.update successful SSH update with public key' => [
				'script' => [
					[
						'scriptid' => 'update_ssh_key',
						'name' => 'API test script.update - SSH with public key updated',
						'scope' => ZBX_SCRIPT_SCOPE_HOST,
						'command' => 'shutdown -r',
						'host_access' => PERM_READ_WRITE,
						'usrgrpid' => 'user',
						'groupid' => 'r',
						'description' => 'Check successful update SSH with publick key',
						'confirmation' => 'Do you want to shutdown?',
						'port' => '{$MACRO}',
						'username' => 'Jill',
						'password' => 'Barry',
						'publickey' => 'updatedpubkey',
						'privatekey' => 'updatedprivkey'
					]
				],
				'expected_error' => null
			],
			'Test script.update successful SSH update and authtype change to password' => [
				'script' => [
					[
						/*
						 * "username" and "password" and the rest of fields that are not given are left unchanged, but
						 * "publickey" and "privatekey" should be cleared.
						 */
						'scriptid' => 'update_ssh_key',
						'authtype' => ITEM_AUTHTYPE_PASSWORD,
						'name' => 'API test script.update - SSH public key update and change to password'
					]
				],
				'expected_error' => null
			],
			'Test script.update successful SSH update and authtype change to public key' => [
				'script' => [
					[
						// Fields that are not given are not changed, but "publickey" and "privatekey" are added.
						'scriptid' => 'update_ssh_pwd',
						'authtype' => ITEM_AUTHTYPE_PUBLICKEY,
						'name' => 'API test script.update - SSH password update and change to public key',
						'publickey' => 'updatedpubkey',
						'privatekey' => 'updatedprivkey',
						'password' => 'different password'
					]
				],
				'expected_error' => null
			],
			'Test script.update successful Telnet update' => [
				'script' => [
					[
						'scriptid' => 'update_telnet',
						'name' => 'API test script.update - Telnet updated',
						'command' => 'shutdown -r',
						'scope' => ZBX_SCRIPT_SCOPE_HOST,
						'host_access' => PERM_READ_WRITE,
						'usrgrpid' => 'user',
						'groupid' => 'r',
						'description' => 'Check successful update Telnet',
						'confirmation' => 'Do you want to shutdown?',
						'port' => '{$MACRO}',
						'username' => 'Barry'
					]
				],
				'expected_error' => null
			],
			'Test script.update successful URL update' => [
				'script' => [
					[
						'scriptid' => 'update_url',
						'name' => 'API test script.update - URL updated',
						'scope' => ZBX_SCRIPT_SCOPE_HOST,
						'url' => '{$MACRO}',
						'host_access' => PERM_READ_WRITE,
						'usrgrpid' => 'user',
						'groupid' => 'r',
						'description' => 'Check successful update URL',
						'confirmation' => 'Do you want to navigate now?',
						'new_window' => ZBX_SCRIPT_URL_NEW_WINDOW_NO
					]
				],
				'expected_error' => null
			],

			// Check Webhook parameter changes - add, remove and update.
			'Test script.update successful Webhook update by adding parameters' => [
				'script' => [
					[
						'scriptid' => 'update_webhook',
						'name' => 'API test script.update - Webhook no params updated with params',
						'scope' => ZBX_SCRIPT_SCOPE_HOST,
						'command' => 'shutdown -r',
						'host_access' => PERM_READ_WRITE,
						'usrgrpid' => 'user',
						'groupid' => 'r',
						'description' => 'Check successful update Webhook now has params',
						'confirmation' => 'Do you want to shutdown?',
						'parameters' => [
							[
								'name' => 'param_added_1',
								'value' => 'value_added_1'
							],
							[
								'name' => 'param_added_2',
								'value' => 'value_added_2'
							]
						]
					]
				],
				'expected_error' => null
			],
			'Test script.update successful Webhook update by removing parameters' => [
				'script' => [
					[
						'scriptid' => 'update_webhook_params',
						'name' => 'API test script.update - Webhook with params updated but no more params',
						'scope' => ZBX_SCRIPT_SCOPE_HOST,
						'command' => 'shutdown -r',
						'host_access' => PERM_READ_WRITE,
						'usrgrpid' => 'user',
						'groupid' => 'r',
						'description' => 'Check successful update Webhook no longer has params',
						'confirmation' => 'Do you want to shutdown?',
						'parameters' => []
					]
				],
				'expected_error' => null
			],
			'Test script.update successful Webhook update by changing parameters' => [
				'script' => [
					[
						'scriptid' => 'update_webhook_params',
						'name' => 'API test script.update - Webhook with params to change updated with new params',
						'scope' => ZBX_SCRIPT_SCOPE_HOST,
						'command' => 'shutdown -r',
						'host_access' => PERM_READ_WRITE,
						'usrgrpid' => 'user',
						'groupid' => 'r',
						'description' => 'Check successful update Webhook parameters change',
						'confirmation' => 'Do you want to shutdown?',
						'parameters' => [
							[
								'name' => 'new_param_1',
								'value' => 'new_value_1'
							]
						]
					]
				],
				'expected_error' => null
			],

			// Check custom script type change.
			'Test script.update successful custom script type change to IPMI' => [
				'script' => [
					[
						'scriptid' => 'update_custom',
						'command' => 'reboot',
						'name' => 'API script custom changed to IPMI',
						'type' => ZBX_SCRIPT_TYPE_IPMI
					]
				],
				'expected_error' => null
			],
			'Test script.update successful custom script type change to SSH with password' => [
				'script' => [
					[
						'scriptid' => 'update_custom',
						'name' => 'API test script.update - custom changed to SSH with password',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_SSH,
						'username' => 'Admin',
						'password' => 'zabbix',
						'port' => 456
					]
				],
				'expected_error' => null
			],
			'Test script.update successful custom script type change to SSH with public key' => [
				'script' => [
					[
						'scriptid' => 'update_custom',
						'name' => 'API test script.update - custom changed to SSH with public key',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_SSH,
						'username' => 'Admin',
						'password' => 'zabbix',
						'port' => 456,
						'authtype' => ITEM_AUTHTYPE_PUBLICKEY,
						'publickey' => 'newsecretpublickey',
						'privatekey' => 'newsecretprivatekey'
					]
				],
				'expected_error' => null
			],
			'Test script.update successful custom script type change to Telnet' => [
				'script' => [
					[
						'scriptid' => 'update_custom',
						'name' => 'API test script.update - custom changed to Telnet',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_TELNET,
						'username' => 'Admin',
						'password' => 'zabbix',
						'port' => 456
					]
				],
				'expected_error' => null
			],
			'Test script.update successful custom script type change to Webhook' => [
				'script' => [
					[
						'scriptid' => 'update_custom',
						'name' => 'API test script.update - custom changed to Webhook',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
						'timeout' => '60s',
						'parameters' => [
							[
								'name' => 'username',
								'value' => 'Admin'
							],
							[
								'name' => 'password',
								'value' => 'zabbix'
							]
						]
					]
				],
				'expected_error' => null
			],
			'Test script.update successful custom script type change to URL' => [
				'script' => [
					[
						'scriptid' => 'update_custom',
						'name' => 'API test script.update - custom script changed to URL',
						'type' => ZBX_SCRIPT_TYPE_URL,
						'scope' => ZBX_SCRIPT_SCOPE_HOST,
						'url' => 'http://new_address/'
					]
				],
				'expected_error' => null
			],

			// Check IPMI type change.
			'Test script.update successful IPMI type change to custom script' => [
				'script' => [
					[
						'scriptid' => 'update_ipmi',
						'name' => 'API test script.update - IPMI changed to custom script (with execute on agent)',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
						'execute_on' => ZBX_SCRIPT_EXECUTE_ON_AGENT
					]
				],
				'expected_error' => null
			],
			'Test script.update successful IPMI type change to SSH with password' => [
				'script' => [
					[
						'scriptid' => 'update_ipmi',
						'name' => 'API test script.update - IPMI changed to SSH with password',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_SSH,
						'username' => 'Admin',
						'password' => 'zabbix',
						'port' => 456
					]
				],
				'expected_error' => null
			],
			'Test script.update successful IPMI type change to SSH with public key' => [
				'script' => [
					[
						'scriptid' => 'update_ipmi',
						'name' => 'API test script.update - IPMI changed to SSH with public key',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_SSH,
						'username' => 'Admin',
						'password' => 'zabbix',
						'port' => 456,
						'authtype' => ITEM_AUTHTYPE_PUBLICKEY,
						'publickey' => 'newsecretepublickey',
						'privatekey' => 'newsecreteprivatekey'
					]
				],
				'expected_error' => null
			],
			'Test script.update successful IPMI type change to Telnet' => [
				'script' => [
					[
						'scriptid' => 'update_ipmi',
						'name' => 'API test script.update - IPMI changed to Telnet',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_TELNET,
						'username' => 'Admin',
						'password' => 'zabbix',
						'port' => 456
					]
				],
				'expected_error' => null
			],
			'Test script.update successful IPMI type change to Webhook' => [
				'script' => [
					[
						'scriptid' => 'update_ipmi',
						'name' => 'API test script.update - IPMI changed to Webhook',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
						'timeout' => '60s',
						'parameters' => [
							[
								'name' => 'username',
								'value' => 'Admin'
							],
							[
								'name' => 'password',
								'value' => 'zabbix'
							]
						]
					]
				],
				'expected_error' => null
			],
			'Test script.update successful IPMI type change to URL' => [
				'script' => [
					[
						'scriptid' => 'update_ipmi',
						'name' => 'API test script.update - IPMI changed to URL',
						'type' => ZBX_SCRIPT_TYPE_URL,
						'scope' => ZBX_SCRIPT_SCOPE_HOST,
						'url' => 'http://new_address/'
					]
				],
				'expected_error' => null
			],

			// Check SSH with password type change.
			'Test script.update successful SSH with password type change to custom script' => [
				'script' => [
					[
						'scriptid' => 'update_ssh_pwd',
						'name' => 'API test script.update - SSH with password changed to custom script (with execute on agent)',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
						'execute_on' => ZBX_SCRIPT_EXECUTE_ON_AGENT
					]
				],
				'expected_error' => null
			],
			'Test script.update successful SSH with password type change to IPMI' => [
				'script' => [
					[
						'scriptid' => 'update_ssh_pwd',
						'name' => 'API test script.update - SSH with password changed to IPMI',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_IPMI
					]
				],
				'expected_error' => null
			],
			'Test script.update successful SSH with password type change to Telnet' => [
				'script' => [
					[
						'scriptid' => 'update_ssh_pwd',
						'name' => 'API test script.update - SSH with password changed to Telnet',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_TELNET,
						'username' => 'Admin',
						'password' => 'zabbix',
						'port' => 456
					]
				],
				'expected_error' => null
			],
			'Test script.update successful SSH with password type change to Webhook' => [
				'script' => [
					[
						'scriptid' => 'update_ssh_pwd',
						'name' => 'API test script.update - SSH with password changed to Webhook',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
						'timeout' => '60s',
						'parameters' => [
							[
								'name' => 'username',
								'value' => 'Admin'
							],
							[
								'name' => 'password',
								'value' => 'zabbix'
							]
						]
					]
				],
				'expected_error' => null
			],
			'Test script.update successful SSH with password type change to URL' => [
				'script' => [
					[
						'scriptid' => 'update_ssh_pwd',
						'name' => 'API test script.update - SSH with password changed to URL',
						'type' => ZBX_SCRIPT_TYPE_URL,
						'scope' => ZBX_SCRIPT_SCOPE_HOST,
						'url' => 'http://new_address/'
					]
				],
				'expected_error' => null
			],

			// Check SSH with public key type change.
			'Test script.update successful SSH with public key type change to custom script' => [
				'script' => [
					[
						'scriptid' => 'update_ssh_key',
						'name' => 'API test script.update - SSH with public key changed to custom script (with execute on agent)',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
						'execute_on' => ZBX_SCRIPT_EXECUTE_ON_AGENT
					]
				],
				'expected_error' => null
			],
			'Test script.update successful SSH with public key type change to IPMI' => [
				'script' => [
					[
						'scriptid' => 'update_ssh_key',
						'name' => 'API test script.update - SSH with public key changed to IPMI',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_IPMI
					]
				],
				'expected_error' => null
			],
			'Test script.update successful SSH with public key type change to Telnet' => [
				'script' => [
					[
						'scriptid' => 'update_ssh_key',
						'name' => 'API test script.update - SSH with public key changed to Telnet',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_TELNET,
						'username' => 'Admin',
						'password' => 'zabbix',
						'port' => 456
					]
				],
				'expected_error' => null
			],
			'Test script.update successful SSH with public key type change to Webhook' => [
				'script' => [
					[
						'scriptid' => 'update_ssh_key',
						'name' => 'API test script.update - SSH with public key changed to Webhook',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
						'timeout' => '60s',
						'parameters' => [
							[
								'name' => 'username',
								'value' => 'Admin'
							],
							[
								'name' => 'password',
								'value' => 'zabbix'
							]
						]
					]
				],
				'expected_error' => null
			],
			'Test script.update successful SSH with public key type change to URL' => [
				'script' => [
					[
						'scriptid' => 'update_ssh_key',
						'name' => 'API test script.update - SSH with public key changed to URL',
						'type' => ZBX_SCRIPT_TYPE_URL,
						'scope' => ZBX_SCRIPT_SCOPE_HOST,
						'url' => 'http://new_address/'
					]
				],
				'expected_error' => null
			],

			// Check Telnet type change.
			'Test script.update successful Telnet type change to custom script' => [
				'script' => [
					[
						'scriptid' => 'update_telnet',
						'name' => 'API test script.update - Telnet changed to custom script (with execute on agent)',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
						'execute_on' => ZBX_SCRIPT_EXECUTE_ON_AGENT
					]
				],
				'expected_error' => null
			],
			'Test script.update successful Telnet type change to SSH with password' => [
				'script' => [
					[
						'scriptid' => 'update_telnet',
						'name' => 'API test script.update - Telnet changed to SSH with password',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_SSH,
						'username' => 'Admin',
						'password' => 'zabbix',
						'port' => 456
					]
				],
				'expected_error' => null
			],
			'Test script.update successful Telnet type change to SSH with public key' => [
				'script' => [
					[
						'scriptid' => 'update_telnet',
						'name' => 'API test script.update - Telnet changed to SSH with public key',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_SSH,
						'username' => 'Admin',
						'password' => 'zabbix',
						'port' => 456,
						'authtype' => ITEM_AUTHTYPE_PUBLICKEY,
						'publickey' => 'newsecretepublickey',
						'privatekey' => 'newsecreteprivatekey'
					]
				],
				'expected_error' => null
			],
			'Test script.update successful Telnet type change to IPMI' => [
				'script' => [
					[
						'scriptid' => 'update_telnet',
						'name' => 'API test script.update - Telnet changed to IPMI',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_IPMI
					]
				],
				'expected_error' => null
			],
			'Test script.update successful Telnet type change to Webhook' => [
				'script' => [
					[
						'scriptid' => 'update_telnet',
						'name' => 'API test script.update - Telnet changed to Webhook',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
						'timeout' => '60s',
						'parameters' => [
							[
								'name' => 'username',
								'value' => 'Admin'
							],
							[
								'name' => 'password',
								'value' => 'zabbix'
							]
						]
					]
				],
				'expected_error' => null
			],
			'Test script.update successful Telnet type change to URL' => [
				'script' => [
					[
						'scriptid' => 'update_telnet',
						'name' => 'API test script.update - Telnet changed to URL',
						'type' => ZBX_SCRIPT_TYPE_URL,
						'scope' => ZBX_SCRIPT_SCOPE_HOST,
						'url' => 'http://new_address/'
					]
				],
				'expected_error' => null
			],

			// Check Webhook type change.
			'Test script.update successful Webhook type change to custom script' => [
				'script' => [
					[
						'scriptid' => 'update_webhook_params',
						'name' => 'API test script.update - Webhook changed to custom script (with execute on agent)',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
						'execute_on' => ZBX_SCRIPT_EXECUTE_ON_AGENT
					]
				],
				'expected_error' => null
			],
			'Test script.update successful Webhook type change to SSH with password' => [
				'script' => [
					[
						'scriptid' => 'update_webhook_params',
						'name' => 'API test script.update - Webhook changed to SSH with password',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_SSH,
						'username' => 'Admin',
						'password' => 'zabbix',
						'port' => 456
					]
				],
				'expected_error' => null
			],
			'Test script.update successful Webhook type change to SSH with public key' => [
				'script' => [
					[
						'scriptid' => 'update_webhook_params',
						'name' => 'API test script.update - Webhook changed to SSH with public key',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_SSH,
						'username' => 'Admin',
						'password' => 'zabbix',
						'port' => 456,
						'authtype' => ITEM_AUTHTYPE_PUBLICKEY,
						'publickey' => 'newsecretepublickey',
						'privatekey' => 'newsecreteprivatekey'
					]
				],
				'expected_error' => null
			],
			'Test script.update successful Webhook type change to IPMI' => [
				'script' => [
					[
						'scriptid' => 'update_webhook_params',
						'name' => 'API test script.update - Webhook changed to IPMI',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_IPMI
					]
				],
				'expected_error' => null
			],
			'Test script.update successful Webhook type change to Telnet' => [
				'script' => [
					[
						'scriptid' => 'update_webhook_params',
						'name' => 'API test script.update - Webhook changed to Telnet',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_TELNET,
						'username' => 'Admin',
						'password' => 'zabbix',
						'port' => 456
					]
				],
				'expected_error' => null
			],
			'Test script.update successful Webhook type change to URL' => [
				'script' => [
					[
						'scriptid' => 'update_webhook_params',
						'name' => 'API test script.update - Webhook changed to URL',
						'type' => ZBX_SCRIPT_TYPE_URL,
						'scope' => ZBX_SCRIPT_SCOPE_HOST,
						'url' => 'http://new_address/'
					]
				],
				'expected_error' => null
			],

			// Check URL type change.
			'Test script.update successful URL type change to custom script' => [
				'script' => [
					[
						'scriptid' => 'update_url',
						'name' => 'API test script.update - URL changed to custom script (with execute on agent)',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT,
						'execute_on' => ZBX_SCRIPT_EXECUTE_ON_AGENT
					]
				],
				'expected_error' => null
			],
			'Test script.update successful URL type change to SSH with password' => [
				'script' => [
					[
						'scriptid' => 'update_url',
						'name' => 'API test script.update - URL changed to SSH with password',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_SSH,
						'username' => 'Admin',
						'password' => 'zabbix',
						'port' => 456
					]
				],
				'expected_error' => null
			],
			'Test script.update successful URL type change to SSH with public key' => [
				'script' => [
					[
						'scriptid' => 'update_url',
						'name' => 'API test script.update - URL changed to SSH with public key',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_SSH,
						'username' => 'Admin',
						'password' => 'zabbix',
						'port' => 456,
						'authtype' => ITEM_AUTHTYPE_PUBLICKEY,
						'publickey' => 'newsecretepublickey',
						'privatekey' => 'newsecreteprivatekey'
					]
				],
				'expected_error' => null
			],
			'Test script.update successful URL type change to IPMI' => [
				'script' => [
					[
						'scriptid' => 'update_url',
						'name' => 'API test script.update - URL changed to IPMI',
						'type' => ZBX_SCRIPT_TYPE_IPMI,
						'command' => 'reboot'
					]
				],
				'expected_error' => null
			],
			'Test script.update successful URL type change to Telnet' => [
				'script' => [
					[
						'scriptid' => 'update_url',
						'name' => 'API test script.update - URL changed to Telnet',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_TELNET,
						'username' => 'Admin',
						'password' => 'zabbix',
						'port' => 456
					]
				],
				'expected_error' => null
			],
			'Test script.update successful URL type change to Webhhook with params' => [
				'script' => [
					[
						'scriptid' => 'update_webhook_params',
						'name' => 'API test script.update - URL changed to Webhook with params',
						'command' => 'reboot',
						'type' => ZBX_SCRIPT_TYPE_WEBHOOK,
						'parameters' => [
							[
								'name' => 'new parameter one',
								'value' => ''
							],
							[
								'name' => 'new parameter two',
								'value' => 'new value 2'
							]
						]
					]
				],
				'expected_error' => null
			],

			// Check field updates depending on scope.
			'Test script.update successful parameter update in existing scope' => [
				'script' => [
					[
						'scriptid' => 'update_ipmi_host',
						'menu_path' => '/new_folder1/new_folder2/',
						'usrgrpid' => 'admin',
						'confirmation' => 'confirmation text updated',
						'host_access' => PERM_READ_WRITE
					]
				],
				'expected_error' => null
			],
			'Test script.update successful parameter update when scope is changed to host' => [
				'script' => [
					[
						'scriptid' => 'update_ipmi',
						'scope' => ZBX_SCRIPT_SCOPE_HOST,
						'menu_path' => '/new_folder1/new_folder2/',
						'usrgrpid' => 'admin',
						'confirmation' => 'confirmation text updated',
						'host_access' => PERM_READ_WRITE
					]
				],
				'expected_error' => null
			],
			'Test script.update successful parameter reset when scope changes to action' => [
				'script' => [
					[
						'scriptid' => 'update_ipmi_host',
						'scope' => ZBX_SCRIPT_SCOPE_ACTION
					]
				],
				'expected_error' => null
			]
		];
	}

	/**
	 * Test script.update method.
	 *
	 * @dataProvider getScriptUpdateInvalid
	 * @dataProvider getScriptUpdateValid
	 */
	public function testScript_Update($scripts, $expected_error) {
		// Accept single and multiple scripts just like API method. Work with multi-dimensional array in result.
		if (!array_key_exists(0, $scripts)) {
			$scripts = zbx_toArray($scripts);
		}

		// Replace ID placeholders with real IDs.
		foreach ($scripts as &$script) {
			$script = self::resolveIds($script);
		}
		unset($script);

		$sql_scripts = 'SELECT NULL FROM scripts';
		$old_hash_scripts = CDBHelper::getHash($sql_scripts);

		if ($expected_error === null) {
			$scriptids = array_column($scripts, 'scriptid');
			$db_scripts = $this->getScripts($scriptids);

			$this->call('script.update', $scripts, $expected_error);

			$scripts_upd = $this->getScripts($scriptids);

			// Compare records from DB before and after API call.
			foreach ($scripts as $script) {
				$db_script = $db_scripts[$script['scriptid']];
				$script_upd = $scripts_upd[$script['scriptid']];

				// Check name.
				$this->assertNotEmpty($script_upd['name']);

				if (array_key_exists('name', $script)) {
					$this->assertSame($script['name'], $script_upd['name']);
				}
				else {
					$this->assertSame($db_script['name'], $script_upd['name']);
				}

				// Check type.
				if (array_key_exists('type', $script)) {
					$this->assertEquals($script['type'], $script_upd['type']);

					// Type has changed.
					if ($script_upd['type'] != $db_script['type']) {
						// Check the new type and make sure all the new values are there and other fields are cleared.
						switch ($script_upd['type']) {
							case ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT:
								// Check execute on.
								if (array_key_exists('execute_on', $script)) {
									$this->assertEquals($script['execute_on'], $script_upd['execute_on']);
								}
								else {
									$this->assertEquals(DB::getDefault('scripts', 'execute_on'),
										$script_upd['execute_on']
									);
								}

								// If previous type was not URL, the command could be updated or not.
								if (array_key_exists('command', $script)) {
									$this->assertEquals($script['command'], $script_upd['command']);
								}
								else {
									$this->assertEquals($db_script['command'], $script_upd['command']);
								}

								// Check other fields.
								$this->assertSame(DB::getDefault('scripts', 'timeout'), $script_upd['timeout']);
								$this->assertEmpty($script_upd['port']);
								$this->assertEquals(DB::getDefault('scripts', 'authtype'), $script_upd['authtype']);
								$this->assertEmpty($script_upd['username']);
								$this->assertEmpty($script_upd['password']);
								$this->assertEmpty($script_upd['publickey']);
								$this->assertEmpty($script_upd['privatekey']);
								$this->assertEmpty($script_upd['parameters']);
								$this->assertEmpty($script_upd['url']);
								$this->assertEquals(DB::getDefault('scripts', 'new_window'), $script_upd['new_window']);
								break;

							case ZBX_SCRIPT_TYPE_IPMI:
								// If previous type was not URL, the command could be updated or not.
								if (array_key_exists('command', $script)) {
									$this->assertEquals($script['command'], $script_upd['command']);
								}
								else {
									$this->assertEquals($db_script['command'], $script_upd['command']);
								}

								// Check other fields.
								$this->assertEquals(DB::getDefault('scripts', 'execute_on'), $script_upd['execute_on']);
								$this->assertSame(DB::getDefault('scripts', 'timeout'), $script_upd['timeout']);
								$this->assertEmpty($script_upd['port']);
								$this->assertEquals(DB::getDefault('scripts', 'authtype'), $script_upd['authtype']);
								$this->assertEmpty($script_upd['username']);
								$this->assertEmpty($script_upd['password']);
								$this->assertEmpty($script_upd['publickey']);
								$this->assertEmpty($script_upd['privatekey']);
								$this->assertEmpty($script_upd['parameters']);
								$this->assertEmpty($script_upd['url']);
								$this->assertEquals(DB::getDefault('scripts', 'new_window'), $script_upd['new_window']);
								break;

							case ZBX_SCRIPT_TYPE_SSH:
								// If previous type was not URL, the command could be updated or not.
								if (array_key_exists('command', $script)) {
									$this->assertEquals($script['command'], $script_upd['command']);
								}
								else {
									$this->assertEquals($db_script['command'], $script_upd['command']);
								}

								// Check username.
								$this->assertNotEmpty($script_upd['username']);
								if (array_key_exists('username', $script)) {
									$this->assertSame($script['username'], $script_upd['username']);
								}
								else {
									$this->assertSame($db_script['username'], $script_upd['username']);
								}

								// Check port.
								if (array_key_exists('port', $script)) {
									$this->assertEquals($script['port'], $script_upd['port']);
								}
								else {
									$this->assertSame($db_script['port'], $script_upd['port']);
								}

								// Check "authtype" field.
								if (array_key_exists('authtype', $script)) {
									$this->assertEquals($script['authtype'], $script_upd['authtype']);

									if ($script['authtype'] == ITEM_AUTHTYPE_PUBLICKEY) {
										// Check public and private keys.
										$this->assertNotEmpty($script_upd['publickey']);
										$this->assertNotEmpty($script_upd['privatekey']);

										// Check public key.
										if (array_key_exists('publickey', $script)) {
											$this->assertSame($script['publickey'], $script_upd['publickey']);
										}
										else {
											$this->assertSame($db_script['publickey'], $script_upd['publickey']);
										}

										// Check private key.
										if (array_key_exists('privatekey', $script)) {
											$this->assertSame($script['privatekey'], $script_upd['privatekey']);
										}
										else {
											$this->assertSame($db_script['privatekey'], $script_upd['privatekey']);
										}
									}
									else {
										// Check password type.
										$this->assertEmpty($db_script['publickey']);
										$this->assertEmpty($db_script['privatekey']);
									}
								}
								else {
									$this->assertEquals($db_script['authtype'], $script_upd['authtype']);

									if ($db_script['authtype'] == ITEM_AUTHTYPE_PUBLICKEY) {
										$this->assertNotEmpty($script_upd['publickey']);
										$this->assertNotEmpty($script_upd['privatekey']);
										$this->assertSame($db_script['publickey'], $script_upd['publickey']);
										$this->assertSame($db_script['privatekey'], $script_upd['privatekey']);
									}
									else {
										$this->assertEmpty($db_script['publickey']);
										$this->assertEmpty($db_script['privatekey']);
									}
								}

								// Check password.
								if (array_key_exists('password', $script)) {
									$this->assertSame($script['password'], $script_upd['password']);
								}
								else {
									$this->assertSame($db_script['password'], $script_upd['password']);
								}

								// Check other fields.
								$this->assertEquals(DB::getDefault('scripts', 'execute_on'), $script_upd['execute_on']);
								$this->assertSame(DB::getDefault('scripts', 'timeout'), $script_upd['timeout']);
								$this->assertEmpty($script_upd['parameters']);
								$this->assertEmpty($script_upd['url']);
								$this->assertEquals(DB::getDefault('scripts', 'new_window'), $script_upd['new_window']);
								break;

							case ZBX_SCRIPT_TYPE_TELNET:
								// If previous type was not URL, the command could be updated or not.
								if (array_key_exists('command', $script)) {
									$this->assertEquals($script['command'], $script_upd['command']);
								}
								else {
									$this->assertEquals($db_script['command'], $script_upd['command']);
								}

								// Check username.
								$this->assertNotEmpty($script_upd['username']);
								if (array_key_exists('username', $script)) {
									$this->assertSame($script['username'], $script_upd['username']);
								}
								else {
									$this->assertSame($db_script['username'], $script_upd['username']);
								}

								// Check password.
								if (array_key_exists('password', $script)) {
									$this->assertSame($script['password'], $script_upd['password']);
								}
								else {
									$this->assertSame($db_script['password'], $script_upd['password']);
								}

								// Check port.
								if (array_key_exists('port', $script)) {
									$this->assertEquals($script['port'], $script_upd['port']);
								}
								else {
									$this->assertSame($db_script['port'], $script_upd['port']);
								}

								// Check other fields.
								$this->assertEquals(DB::getDefault('scripts', 'execute_on'), $script_upd['execute_on']);
								$this->assertSame(DB::getDefault('scripts', 'timeout'), $script_upd['timeout']);
								$this->assertEquals(DB::getDefault('scripts', 'authtype'), $script_upd['authtype']);
								$this->assertEmpty($script_upd['publickey']);
								$this->assertEmpty($script_upd['privatekey']);
								$this->assertEmpty($script_upd['parameters']);
								$this->assertEmpty($script_upd['url']);
								$this->assertEquals(DB::getDefault('scripts', 'new_window'), $script_upd['new_window']);
								break;

							case ZBX_SCRIPT_TYPE_WEBHOOK:
								// Check timeout.
								if (array_key_exists('timeout', $script)) {
									$this->assertSame($script['timeout'], $script_upd['timeout']);
								}
								else {
									$this->assertSame($db_script['timeout'], $script_upd['timeout']);
								}

								// Check parameters.
								if (array_key_exists('parameters', $script)) {
									if ($script['parameters']) {
										$this->assertNotEmpty($script_upd['parameters']);
										$this->assertEqualsCanonicalizing($script['parameters'],
											$script_upd['parameters']
										);
									}
									else {
										$this->assertEmpty($script_upd['parameters']);
									}
								}
								else {
									$this->assertEmpty($script_upd['parameters']);
								}

								// Check other fields.
								$this->assertEquals(DB::getDefault('scripts', 'execute_on'), $script_upd['execute_on']);
								$this->assertEmpty($script_upd['port']);
								$this->assertEquals(DB::getDefault('scripts', 'authtype'), $script_upd['authtype']);
								$this->assertEmpty($script_upd['username']);
								$this->assertEmpty($script_upd['password']);
								$this->assertEmpty($script_upd['publickey']);
								$this->assertEmpty($script_upd['privatekey']);
								$this->assertEmpty($script_upd['url']);
								$this->assertEquals(DB::getDefault('scripts', 'new_window'), $script_upd['new_window']);
								break;

							case ZBX_SCRIPT_TYPE_URL:
								// Check "url" field.
								$this->assertNotEmpty($script_upd['url']);
								$this->assertSame($script['url'], $script_upd['url']);

								// Check "new_window" field.
								if (array_key_exists('new_window', $script)) {
									$this->assertEquals($script['new_window'], $script_upd['new_window']);
								}
								else {
									$this->assertSame(DB::getDefault('scripts', 'new_window'),
										$script_upd['new_window']
									);
								}

								// Check other fields.
								$this->assertEmpty($script_upd['command']);
								$this->assertEquals(DB::getDefault('scripts', 'execute_on'), $script_upd['execute_on']);
								$this->assertSame(DB::getDefault('scripts', 'timeout'), $script_upd['timeout']);
								$this->assertEmpty($script_upd['port']);
								$this->assertEquals(DB::getDefault('scripts', 'authtype'), $script_upd['authtype']);
								$this->assertEmpty($script_upd['username']);
								$this->assertEmpty($script_upd['password']);
								$this->assertEmpty($script_upd['publickey']);
								$this->assertEmpty($script_upd['privatekey']);
								$this->assertEmpty($script_upd['parameters']);
								break;
						}
					}
				}
				else {
					// Type has not changed.
					$this->assertEquals($db_script['type'], $script_upd['type']);

					// Some fields can still be changed within same type.
					switch ($script_upd['type']) {
						case ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT:
							// Check command.
							$this->assertNotEmpty($script_upd['command']);

							if (array_key_exists('command', $script)) {
								$this->assertEquals($script['command'], $script_upd['command']);
							}
							else {
								$this->assertEquals($db_script['command'], $script_upd['command']);
							}

							// Check execute on.
							if (array_key_exists('execute_on', $script)) {
								$this->assertEquals($script['execute_on'], $script_upd['execute_on']);
							}
							else {
								$this->assertEquals(DB::getDefault('scripts', 'execute_on'), $script_upd['execute_on']);
							}
							break;

						case ZBX_SCRIPT_TYPE_IPMI:
							// Check command.
							$this->assertNotEmpty($script_upd['command']);

							if (array_key_exists('command', $script)) {
								$this->assertEquals($script['command'], $script_upd['command']);
							}
							else {
								$this->assertEquals($db_script['command'], $script_upd['command']);
							}
							break;

						case ZBX_SCRIPT_TYPE_SSH:
							// Check command.
							$this->assertNotEmpty($script_upd['command']);

							if (array_key_exists('command', $script)) {
								$this->assertEquals($script['command'], $script_upd['command']);
							}
							else {
								$this->assertEquals($db_script['command'], $script_upd['command']);
							}

							// Check username.
							$this->assertNotEmpty($script_upd['username']);
							if (array_key_exists('username', $script)) {
								$this->assertSame($script['username'], $script_upd['username']);
							}
							else {
								$this->assertSame($db_script['username'], $script_upd['username']);
							}

							// Check port.
							if (array_key_exists('port', $script)) {
								$this->assertEquals($script['port'], $script_upd['port']);
							}
							else {
								$this->assertSame($db_script['port'], $script_upd['port']);
							}

							// Check "authtype" field.
							if (array_key_exists('authtype', $script)) {
								$this->assertEquals($script['authtype'], $script_upd['authtype']);

								if ($script['authtype'] != $db_script['authtype']) {
									// Change from password to public key.
									if ($script['authtype'] == ITEM_AUTHTYPE_PUBLICKEY) {
										$this->assertNotEmpty($script_upd['publickey']);
										$this->assertNotEmpty($script_upd['privatekey']);
										$this->assertSame($script['publickey'], $script_upd['publickey']);
										$this->assertSame($script['privatekey'], $script_upd['privatekey']);
									}
								}
								else {
									// Same "authtype" field.
									if ($db_script['authtype'] == ITEM_AUTHTYPE_PUBLICKEY) {
										if (array_key_exists('publickey', $script)) {
											$this->assertSame($script['publickey'], $script_upd['publickey']);
										}
										else {
											$this->assertSame($db_script['publickey'], $script_upd['publickey']);
										}

										if (array_key_exists('privatekey', $script)) {
											$this->assertSame($script['privatekey'], $script_upd['privatekey']);
										}
										else {
											$this->assertSame($db_script['privatekey'], $script_upd['privatekey']);
										}
									}
								}
							}
							else {
								// FIeld "authtype" was not given, os it is unchanged.
								$this->assertEquals($db_script['authtype'], $script_upd['authtype']);

								if ($db_script['authtype'] == ITEM_AUTHTYPE_PUBLICKEY) {
									$this->assertNotEmpty($script_upd['publickey']);
									$this->assertNotEmpty($script_upd['privatekey']);

									if (array_key_exists('publickey', $script)) {
										$this->assertSame($script['publickey'], $script_upd['publickey']);
									}
									else {
										$this->assertSame($db_script['publickey'], $script_upd['publickey']);
									}

									if (array_key_exists('privatekey', $script)) {
										$this->assertSame($script['privatekey'], $script_upd['privatekey']);
									}
									else {
										$this->assertSame($db_script['privatekey'], $script_upd['privatekey']);
									}
								}
								else {
									// Remains password type.
									$this->assertEmpty($db_script['publickey']);
									$this->assertEmpty($db_script['privatekey']);
								}
							}

							// Check password.
							if (array_key_exists('password', $script)) {
								$this->assertSame($script['password'], $script_upd['password']);
							}
							else {
								$this->assertSame($db_script['password'], $script_upd['password']);
							}
							break;

						case ZBX_SCRIPT_TYPE_TELNET:
							// Check command.
							$this->assertNotEmpty($script_upd['command']);

							if (array_key_exists('command', $script)) {
								$this->assertEquals($script['command'], $script_upd['command']);
							}
							else {
								$this->assertEquals($db_script['command'], $script_upd['command']);
							}

							// Check username.
							$this->assertNotEmpty($script_upd['username']);
							if (array_key_exists('username', $script)) {
								$this->assertSame($script['username'], $script_upd['username']);
							}
							else {
								$this->assertSame($db_script['username'], $script_upd['username']);
							}

							// Check password.
							if (array_key_exists('password', $script)) {
								$this->assertSame($script['password'], $script_upd['password']);
							}
							else {
								$this->assertSame($db_script['password'], $script_upd['password']);
							}

							// Check port.
							if (array_key_exists('port', $script)) {
								$this->assertEquals($script['port'], $script_upd['port']);
							}
							else {
								$this->assertSame($db_script['port'], $script_upd['port']);
							}
							break;

						case ZBX_SCRIPT_TYPE_WEBHOOK:
							// Check command.
							$this->assertNotEmpty($script_upd['command']);

							if (array_key_exists('command', $script)) {
								$this->assertEquals($script['command'], $script_upd['command']);
							}
							else {
								$this->assertEquals($db_script['command'], $script_upd['command']);
							}

							// Check timeout.
							if (array_key_exists('timeout', $script)) {
								$this->assertSame($script['timeout'], $script_upd['timeout']);
							}
							else {
								$this->assertSame($db_script['timeout'], $script_upd['timeout']);
							}

							// Check parameters.
							if (array_key_exists('parameters', $script)) {
								if ($script['parameters']) {
									// Check newly added parameters.
									$this->assertNotEmpty($script_upd['parameters']);
									$this->assertEqualsCanonicalizing($script['parameters'], $script_upd['parameters']);
								}
								else {
									// Check that parameters are removed.
									$this->assertEmpty($script_upd['parameters']);
								}
							}
							else {
								// Check that parameters remain the same. Order is not important.
								$this->assertEqualsCanonicalizing($db_script['parameters'], $script_upd['parameters']);
							}
							break;

						case ZBX_SCRIPT_TYPE_URL:
							// Check "url" field.
							$this->assertNotEmpty($script_upd['url']);

							if (array_key_exists('url', $script)) {
								$this->assertEquals($script['url'], $script_upd['url']);
							}
							else {
								$this->assertSame($db_script['url'], $script_upd['url']);
							}

							// Check "new_window" field.
							if (array_key_exists('new_window', $script)) {
								$this->assertEquals($script['new_window'], $script_upd['new_window']);
							}
							else {
								$this->assertSame($db_script['new_window'], $script_upd['new_window']);
							}
							break;
					}
				}

				// Check scope.
				if (array_key_exists('scope', $script)) {
					$this->assertEquals($script['scope'], $script_upd['scope']);
				}
				else {
					$this->assertEquals($db_script['scope'], $script_upd['scope']);
				}

				// Check scope dependent fields.
				if ($script_upd['scope'] == ZBX_SCRIPT_SCOPE_ACTION) {
					$this->assertEmpty($script_upd['menu_path']);
					$this->assertEquals(0, $script_upd['usrgrpid']);
					$this->assertEquals(DB::getDefault('scripts', 'host_access'), $script_upd['host_access']);
					$this->assertEmpty($script_upd['confirmation']);
				}
				else {
					// Check "menu_path" field.
					if (array_key_exists('menu_path', $script)) {
						$this->assertSame($script_upd['menu_path'], $script['menu_path']);
					}
					else {
						$this->assertSame($db_script['menu_path'], $script_upd['menu_path']);
					}

					// Check user group.
					if (array_key_exists('usrgrpid', $script)) {
						$this->assertEquals($script['usrgrpid'], $script_upd['usrgrpid']);
					}
					else {
						$this->assertSame($db_script['usrgrpid'], $script_upd['usrgrpid']);
					}

					// Check "host_access" field.
					if (array_key_exists('host_access', $script)) {
						$this->assertEquals($script['host_access'], $script_upd['host_access']);
					}
					else {
						$this->assertEquals($db_script['host_access'], $script_upd['host_access']);
					}

					// Check confirmation.
					if (array_key_exists('confirmation', $script)) {
						$this->assertSame($script['confirmation'], $script_upd['confirmation']);
					}
					else {
						$this->assertSame($db_script['confirmation'], $script_upd['confirmation']);
					}
				}

				// Check host group.
				if (array_key_exists('groupid', $script)) {
					$this->assertEquals($script_upd['groupid'], $script['groupid']);
				}
				else {
					$this->assertSame($db_script['groupid'], $script_upd['groupid']);
				}

				// Check description.
				if (array_key_exists('description', $script)) {
					$this->assertSame($script_upd['description'], $script['description']);
				}
				else {
					$this->assertSame($db_script['description'], $script_upd['description']);
				}
			}

			// Restore script original data after each test.
			$this->restoreScripts($db_scripts);
		}
		else {
			// Call method and make sure it really returns the error.
			$this->call('script.update', $scripts, $expected_error);

			// Make sure nothing has changed as well.
			$this->assertSame($old_hash_scripts, CDBHelper::getHash($sql_scripts));
		}
	}

	/**
	 * Data provider for script.delete. Array contains invalid scripts that are not possible to delete.
	 *
	 * @return array
	 */
	public static function getScriptDeleteInvalid() {
		return [
			// Check script IDs.
			'Test script.delete with empty ID' => [
				'scriptids' => [''],
				'expected_error' => 'Invalid parameter "/1": a number is expected.'
			],
			'Test script.delete with non-existent ID' => [
				'scriptids' => [999999],
				'expected_error' => 'No permissions to referred object or it does not exist!'
			],
			'Test script.delete with two same IDs' => [
				'scriptids' => [0, 0],
				'expected_error' => 'Invalid parameter "/2": value (0) already exists.'
			],

			// Check if deleted scripts used in actions.
			'Test script.delete with attached action' => [
				'scriptids' => ['delete_action'],
				'expected_error' => 'Cannot delete scripts. Script "API test script.delete - not allowed due to action" is used in action operation "API test script.delete action".'
			]
		];
	}

	/**
	 * Data provider for host.delete. Array contains valid scripts.
	 *
	 * @return array
	 */
	public static function getScriptDeleteValid() {
		return [
			// Successfully delete scripts.
			'Test script.delete' => [
				'script' => ['delete_single'],
				'expected_error' => null
			],
			'Test script.delete (multiple)' => [
				'script' => [
					'delete_multi_1',
					// Webhook script.
					'delete_multi_2'
				],
				'expected_error' => null
			]
		];
	}

	/**
	 * Test script.delete method.
	 *
	 * @dataProvider getScriptDeleteInvalid
	 * @dataProvider getScriptDeleteValid
	 */
	public function testScript_Delete($scriptids, $expected_error) {
		// Replace ID placeholders with real IDs.
		foreach ($scriptids as &$scriptid) {
			if ($scriptid != '0' && $scriptid !== '' && $scriptid !== null && $scriptid != 999999
					&& !is_array($scriptid)) {
				$scriptid = self::$data['scriptids'][$scriptid];
			}
		}
		unset($scriptid);

		$sql_scripts = 'SELECT NULL FROM scripts';
		$old_hash_scripts = CDBHelper::getHash($sql_scripts);

		// Make sure there are no parameters left for Webhook scripts.
		$sql_script_param = 'SELECT NULL FROM script_param';
		$old_hash_script_param = CDBHelper::getHash($sql_script_param);

		$this->call('script.delete', $scriptids, $expected_error);

		if ($expected_error === null) {
			$this->assertNotSame($old_hash_scripts, CDBHelper::getHash($sql_scripts));
			$this->assertEquals(0, CDBHelper::getCount(
				'SELECT s.scriptid FROM scripts s WHERE '.dbConditionId('s.scriptid', $scriptids)
			));

			$this->assertEquals(0, CDBHelper::getCount(
				'SELECT sp.scriptid FROM script_param sp WHERE '.dbConditionId('sp.scriptid', $scriptids)
			));

			// script.delete checks if given "scriptid" exists, so they need to be removed from self::$data['scriptids']
			foreach ($scriptids as $scriptid) {
				$key = array_search($scriptid, self::$data['scriptids']);
				if ($key !== false) {
					unset(self::$data['scriptids'][$key]);
				}
			}
		}
		else {
			$this->assertSame($old_hash_scripts, CDBHelper::getHash($sql_scripts));
			$this->assertSame($old_hash_script_param, CDBHelper::getHash($sql_script_param));
		}
	}

	/**
	 * Data provider for script.execute. Array contains invalid scripts that are not possible to execute.
	 *
	 * @return array
	 */
	public static function getScriptExecuteInvalid() {
		return [
			// Check unexpected parameters.
			'Test script.execute unexpected parameter "value"' => [
				'script' => [
					'scriptid' => 'exec_usrgrp_admin',
					'hostid' => 'plain_rw',
					'value' => 'test'
				],
				'expected_error' => 'Invalid parameter "/": unexpected parameter "value".'
			],

			// Check script ID.
			'Test script.execute empty request' => [
				'script' => [],
				'expected_error' => 'Invalid parameter "/": the parameter "scriptid" is missing.'
			],
			'Test script.execute missing script ID' => [
				'script' => [
					'hostid' => 'plain_rw'
				],
				'expected_error' => 'Invalid parameter "/": the parameter "scriptid" is missing.'
			],
			'Test script.execute invalid script ID' => [
				'script' => [
					'scriptid' => '',
					'hostid' => 'plain_rw'
				],
				'expected_error' => 'Invalid parameter "/scriptid": a number is expected.'
			],
			'Test script.execute non-existent script' => [
				'script' => [
					'scriptid' => 999999,
					'hostid' => 'plain_rw'
				],
				'expected_error' => 'No permissions to referred object or it does not exist!'
			],

			// Check host ID.
			'Test script.execute missing event ID or host ID' => [
				'script' => [
					'scriptid' => 'exec_usrgrp_admin'
				],
				'expected_error' => 'Invalid parameter "/": the parameter "eventid" is missing.'
			],
			'Test script.execute invalid host ID' => [
				'script' => [
					'scriptid' => 'exec_usrgrp_admin',
					'hostid' => ''
				],
				'expected_error' => 'Invalid parameter "/hostid": a number is expected.'
			],
			'Test script.execute non-existent host ID' => [
				'script' => [
					'scriptid' => 'exec_usrgrp_admin',
					'hostid' => 999999
				],
				'expected_error' => 'No permissions to referred object or it does not exist!'
			],

			// Check event ID.
			'Test script.execute both event ID and host ID' => [
				'script' => [
					'scriptid' => 'exec_usrgrp_admin',
					'hostid' => 'plain_rw',
					'eventid' => 'plain_rw_single_d'
				],
				'expected_error' => 'Invalid parameter "/": unexpected parameter "eventid".'
			],
			'Test script.execute non-existent event ID' => [
				'script' => [
					'scriptid' => 'exec_usrgrp_admin',
					'eventid' => 0
				],
				'expected_error' => 'No permissions to referred object or it does not exist!'
			],

			/*
			 * Check script permissions for host group. Host belongs to the host group that has no permission to execute
			 * current script.
			 */
			'Test script.execute permissions' => [
				'script' => [
					'scriptid' => 'exec_hstgrp',
					'hostid' => 'inherit_a_rw'
				],
				'expected_error' => 'No permissions to referred object or it does not exist!'
			],

			// URL type scripts are not possible to execute.
			'Test script.execute URL' => [
				'script' => [
					'scriptid' => 'exec_url',
					'hostid' => 'plain_rw'
				],
				'expected_error' => 'Cannot execute URL type script.'
			]
		];
	}

	/**
	 * Test script.execute with errors like missing fields, invalid fields, permissions etc.
	 *
	 * @dataProvider getScriptExecuteInvalid
	 */
	public function testScripts_Execute($script, $expected_error) {
		// Replace ID placeholders with real IDs.
		$script = self::resolveIds($script);

		$this->call('script.execute', $script, $expected_error);
	}

	/**
	 * Data provider for script.execute, script.create, script.update, script.delete. Array contains invalid scripts.
	 *
	 * @return array
	 */
	public static function getScriptPermissions() {
		return [
			// User has permissions to host, but not to script (script can execute only specific user group).
			'Test script.execute script permissions' => [
				'method' => 'script.execute',
				'login' => ['user' => 'api_test_user', 'password' => '4P1T3$tEr'],
				'script' => [
					'scriptid' => 'exec_usrgrp_admin',
					'hostid' => 'plain_rw'
				],
				'expected_error' => 'No permissions to referred object or it does not exist!'
			],

			// User have permissions to script, but not to host (script can execute only on specific host group).
			'Test script.execute host permissions' => [
				'method' => 'script.execute',
				'login' => ['user' => 'api_test_user', 'password' => '4P1T3$tEr'],
				'script' => [
					'scriptid' => 'exec_hstgrp',
					'hostid' => 'plain_r'
				],
				'expected_error' => 'No permissions to referred object or it does not exist!'
			],

			// User have deny permissions to host, but script required read permissions for the host.
			[
				'method' => 'script.execute',
				'login' => ['user' => 'api_test_user', 'password' => '4P1T3$tEr'],
				'script' => [
					'scriptid' => 'exec_usrgrp_user',
					'hostid' => 'plain_d'
				],
				'expected_error' => 'No permissions to referred object or it does not exist!'
			],

			// Check regular zabbix admin permissions to create, update, delete.
			[
				'method' => 'script.create',
				'login' => ['user' => 'api_test_admin', 'password' => '4P1T3$tEr'],
				'script' => [
					'name' => 'API script create as zabbix admin',
					'command' => 'reboot server 1'
				],
				'expected_error' => 'No permissions to call "script.create".'
			],
			[
				'method' => 'script.update',
				'login' => ['user' => 'api_test_admin', 'password' => '4P1T3$tEr'],
				'script' => [
					'scriptid' => 'update_telnet',
					'name' => 'API script update as zabbix admin'
				],
				'expected_error' => 'No permissions to call "script.update".'
			],
			[
				'method' => 'script.delete',
				'login' => ['user' => 'api_test_admin', 'password' => '4P1T3$tEr'],
				'script' => ['get_custom_defaults'],
				'expected_error' => 'No permissions to call "script.delete".'
			],

			// Check regular user permissions to create, update, delete.
			[
				'method' => 'script.create',
				'login' => ['user' => 'api_test_user', 'password' => '4P1T3$tEr'],
				'script' => [
					'name' => 'API script create as zabbix user',
					'command' => 'reboot server 1'
				],
				'expected_error' => 'No permissions to call "script.create".'
			],
			[
				'method' => 'script.update',
				'login' => ['user' => 'api_test_user', 'password' => '4P1T3$tEr'],
				'script' => [
					'scriptid' => 'update_telnet',
					'name' => 'API script update as zabbix user'
				],
				'expected_error' => 'No permissions to call "script.update".'
			],
			[
				'method' => 'script.delete',
				'login' => ['user' => 'api_test_user', 'password' => '4P1T3$tEr'],
				'script' => ['get_custom_defaults'],
				'expected_error' => 'No permissions to call "script.delete".'
			]
		];
	}

	/**
	 * Test script.create, script.update, script.delete, script.execute with various users and premissions.
	 *
	 * @dataProvider getScriptPermissions
	 */
	public function testScripts_Permissions($method, $login, $script, $expected_error) {
		// Replace ID placeholders with real IDs.
		$script = self::resolveIds($script);

		$this->authorize($login['user'], $login['password']);
		$this->call($method, $script, $expected_error);
	}

	/**
	 * Data provider for script.getScriptsByHosts. Array contains invalid data.
	 *
	 * @return array
	 */
	public static function getScriptsByHostsDataInvalid() {
		return [
			'Test script.getScriptsByHosts invalid fields' => [
				'request' => [
					'hostids' => ['']
				],
				'expected_result' => [],
				'expected_error' => 'Invalid parameter "/1": a number is expected.'
			],
			'Test script.getScriptsByHosts identical IDs given' => [
				'request' => [
					'hostids' => [0, 0]
				],
				'expected_result' => [],
				'expected_error' => 'Invalid parameter "/2": value (0) already exists.'
			]
		];
	}

	/**
	 * Data provider for script.getScriptsByHosts. Array contains valid data. Checks if result contains cetrain scripts
	 * and those scripts contain fields with resolved macros. Some macros cannot be resolved. They either resolve to
	 * *UNKNOWN* or do not resolve at all. Each host and request can have different macros.
	 *
	 * @return array
	 */
	public static function getScriptsByHostsDataValid() {
		return [
			'Test script.getScriptsByHosts with superadmin' => [
				'request' => [
					'hostids' => [
						'plain_r', 'plain_d', 'macros_rw_1', 'macros_r_2', 'macros_rw_3', 'interface_rw_1',
						'interface_rw_2', 'inventory_rw_1', 'inventory_rw_2'
					]
				],
				'expected_result' => [
					'has.hostid:scriptid' => [
						// Superadmin has all scripts available.
						'plain_r' => ['get_hosts_url', 'get_hosts_ipmi', 'get_hosts_webhook', 'get_hosts_ssh'],
						'plain_d' => ['get_hosts_url', 'get_hosts_ipmi', 'get_hosts_webhook', 'get_hosts_ssh'],
						'macros_rw_1' => ['get_hosts_url', 'get_hosts_ipmi', 'get_hosts_webhook', 'get_hosts_ssh'],
						'macros_r_2' => ['get_hosts_url', 'get_hosts_ipmi', 'get_hosts_webhook', 'get_hosts_ssh'],
						'macros_rw_3' => ['get_hosts_url', 'get_hosts_ipmi', 'get_hosts_webhook', 'get_hosts_ssh'],
						'interface_rw_1' => ['get_hosts_url', 'get_hosts_ipmi', 'get_hosts_webhook',
							'get_hosts_ssh'
						],
						'interface_rw_2' => ['get_hosts_url', 'get_hosts_ipmi', 'get_hosts_webhook',
							'get_hosts_ssh'
						],
						'inventory_rw_1' => ['get_hosts_url', 'get_hosts_ipmi', 'get_hosts_webhook',
							'get_hosts_ssh'
						],
						'inventory_rw_2' => ['get_hosts_url', 'get_hosts_ipmi', 'get_hosts_webhook',
							'get_hosts_ssh'
						]
					],
					'scripts' => [
						[
							'scriptid' => 'get_hosts_url',
							'name' => 'API test script.getScriptsByHosts - URL',
							'command' => '',
							'host_access' => (string) PERM_READ,
							'usrgrpid' => '0',
							'groupid' => '0',
							'description' => '',
							'confirmation' => 'Confirmation macros: {$HOST_MACRO}, {$HOST_MACRO_OTHER},'.
								' {$GLOBAL_MACRO}, {$DOESNOTEXIST}, {HOST.ID}, {HOST.HOST}, {HOST.NAME}, {HOST.CONN},'.
								' {HOST.DNS}, {HOST.PORT}, {HOST.NAME1}, {HOST.NAME2}, {EVENT.ID}, {EVENT.NAME},'.
								' {EVENT.NSEVERITY}, {EVENT.SEVERITY}',
							'type' => (string) ZBX_SCRIPT_TYPE_URL,
							'execute_on' => (string) ZBX_SCRIPT_EXECUTE_ON_PROXY,
							'timeout' => '30s',
							'scope' => (string) ZBX_SCRIPT_SCOPE_HOST,
							'port' => '',
							'authtype' => (string) ITEM_AUTHTYPE_PASSWORD,
							'username' => '',
							'password' => '',
							'publickey' => '',
							'privatekey' => '',
							'menu_path' => '',
							'url' => 'http://zabbix/ui/zabbix.php?action=host.edit&hostid={HOST.ID}',
							'new_window' => (string) ZBX_SCRIPT_URL_NEW_WINDOW_YES
						],
						[
							'scriptid' => 'get_hosts_ipmi',
							'name' => 'API test script.getScriptsByHosts - IPMI',
							'command' => 'reboot server',
							'host_access' => (string) PERM_READ,
							'usrgrpid' => 'admin',
							'groupid' => '0',
							'description' => '',
							'confirmation' => 'Confirmation macros: {USER.FULLNAME}, {USER.NAME}, {USER.SURNAME},'.
								' {USER.USERNAME}, {INVENTORY.ALIAS}, {INVENTORY.OS}, {INVENTORY.TYPE},'.
								' {INVENTORY.CONTACT}, {INVENTORY.OS1}, {INVENTORY.OS2}, {HOSTGROUP.ID}',
							'type' => (string) ZBX_SCRIPT_TYPE_IPMI,
							'execute_on' => (string) ZBX_SCRIPT_EXECUTE_ON_PROXY,
							'timeout' => '30s',
							'scope' => (string) ZBX_SCRIPT_SCOPE_HOST,
							'port' => '',
							'authtype' => (string) ITEM_AUTHTYPE_PASSWORD,
							'username' => '',
							'password' => '',
							'publickey' => '',
							'privatekey' => '',
							'menu_path' => '',
							'url' => '',
							'new_window' => (string) ZBX_SCRIPT_URL_NEW_WINDOW_YES
						],
						// Webhook does not return parameters. Mostly frontend needs only  script ID anyway.
						[
							'scriptid' => 'get_hosts_webhook',
							'name' => 'API test script.getScriptsByHosts - Webhook',
							'command' => 'reboot server',
							'host_access' => (string) PERM_READ,
							'usrgrpid' => 'user',
							'groupid' => '0',
							'description' => '',
							'confirmation' => 'Confirmation macros: {$GLOBAL_MACRO}, {HOST.HOST}, {USER.FULLNAME},'.
								' {HOST.CONN}, {HOST.IP}, {HOST.DNS}, {HOST.PORT}',
							'type' => (string) ZBX_SCRIPT_TYPE_WEBHOOK,
							'execute_on' => (string) ZBX_SCRIPT_EXECUTE_ON_PROXY,
							'timeout' => '30s',
							'scope' => (string) ZBX_SCRIPT_SCOPE_HOST,
							'port' => '',
							'authtype' => (string) ITEM_AUTHTYPE_PASSWORD,
							'username' => '',
							'password' => '',
							'publickey' => '',
							'privatekey' => '',
							'menu_path' => '',
							'url' => '',
							'new_window' => (string) ZBX_SCRIPT_URL_NEW_WINDOW_YES
						],
						[
							'scriptid' => 'get_hosts_ssh',
							'name' => 'API test script.getScriptsByHosts - SSH password',
							'command' => 'reboot server',
							'host_access' => (string) PERM_READ_WRITE,
							'usrgrpid' => '0',
							'groupid' => '0',
							'description' => '',
							'confirmation' => 'Confirmation macros: {$GLOBAL_MACRO}, {HOST.HOST}, {USER.FULLNAME},'.
								' {HOST.CONN}, {HOST.IP}, {HOST.DNS}, {HOST.PORT}, {INVENTORY.ALIAS},'.
								' {INVENTORY.OS}, {INVENTORY.TYPE}',
							'type' => (string) ZBX_SCRIPT_TYPE_SSH,
							'execute_on' => (string) ZBX_SCRIPT_EXECUTE_ON_PROXY,
							'timeout' => '30s',
							'scope' => (string) ZBX_SCRIPT_SCOPE_HOST,
							'port' => '',
							'authtype' => (string) ITEM_AUTHTYPE_PASSWORD,
							'username' => 'user',
							'password' => '',
							'publickey' => '',
							'privatekey' => '',
							'menu_path' => '',
							'url' => '',
							'new_window' => (string) ZBX_SCRIPT_URL_NEW_WINDOW_YES
						]
					],
					'host_macros' => [
						'plain_r' => [
							'{HOST.ID}' => 'plain_r',
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.HOST}' => 'api_test_host_plain_r',
							'{HOST.NAME}' => 'API test host - plain, read',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => '{HOST.NAME1}',
							'{HOST.NAME2}' => '{HOST.NAME2}',
							'{EVENT.ID}' => '{EVENT.ID}',
							'{EVENT.NAME}' => '{EVENT.NAME}',
							'{EVENT.NSEVERITY}' => '{EVENT.NSEVERITY}',
							'{EVENT.SEVERITY}' => '{EVENT.SEVERITY}',
							'{USER.FULLNAME}' => 'Zabbix Administrator (Admin)',
							'{USER.NAME}' => 'Zabbix',
							'{USER.SURNAME}' => 'Administrator',
							'{USER.USERNAME}' => 'Admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '{INVENTORY.OS1}',
							'{INVENTORY.OS2}' => '{INVENTORY.OS2}',
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}'
						],
						'plain_d' => [
							'{HOST.ID}' => 'plain_d',
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.HOST}' => 'api_test_host_plain_d',
							'{HOST.NAME}' => 'API test host - plain, deny',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => '{HOST.NAME1}',
							'{HOST.NAME2}' => '{HOST.NAME2}',
							'{EVENT.ID}' => '{EVENT.ID}',
							'{EVENT.NAME}' => '{EVENT.NAME}',
							'{EVENT.NSEVERITY}' => '{EVENT.NSEVERITY}',
							'{EVENT.SEVERITY}' => '{EVENT.SEVERITY}',
							'{USER.FULLNAME}' => 'Zabbix Administrator (Admin)',
							'{USER.NAME}' => 'Zabbix',
							'{USER.SURNAME}' => 'Administrator',
							'{USER.USERNAME}' => 'Admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '{INVENTORY.OS1}',
							'{INVENTORY.OS2}' => '{INVENTORY.OS2}',
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}'
						],
						'macros_rw_1' => [
							'{HOST.ID}' => 'macros_rw_1',
							'{$HOST_MACRO}' => 'host macro value - 1',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.HOST}' => 'api_test_host_macros_rw_1',
							'{HOST.NAME}' => 'API test host - macros 1, read-write',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => '{HOST.NAME1}',
							'{HOST.NAME2}' => '{HOST.NAME2}',
							'{EVENT.ID}' => '{EVENT.ID}',
							'{EVENT.NAME}' => '{EVENT.NAME}',
							'{EVENT.NSEVERITY}' => '{EVENT.NSEVERITY}',
							'{EVENT.SEVERITY}' => '{EVENT.SEVERITY}',
							'{USER.FULLNAME}' => 'Zabbix Administrator (Admin)',
							'{USER.NAME}' => 'Zabbix',
							'{USER.SURNAME}' => 'Administrator',
							'{USER.USERNAME}' => 'Admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '{INVENTORY.OS1}',
							'{INVENTORY.OS2}' => '{INVENTORY.OS2}',
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}'
						],
						'macros_r_2' => [
							'{HOST.ID}' => 'macros_r_2',
							'{$HOST_MACRO}' => 'host macro value - 2',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.HOST}' => 'api_test_host_macros_r_2',
							'{HOST.NAME}' => 'API test host - macros 2, read',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => '{HOST.NAME1}',
							'{HOST.NAME2}' => '{HOST.NAME2}',
							'{EVENT.ID}' => '{EVENT.ID}',
							'{EVENT.NAME}' => '{EVENT.NAME}',
							'{EVENT.NSEVERITY}' => '{EVENT.NSEVERITY}',
							'{EVENT.SEVERITY}' => '{EVENT.SEVERITY}',
							'{USER.FULLNAME}' => 'Zabbix Administrator (Admin)',
							'{USER.NAME}' => 'Zabbix',
							'{USER.SURNAME}' => 'Administrator',
							'{USER.USERNAME}' => 'Admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '{INVENTORY.OS1}',
							'{INVENTORY.OS2}' => '{INVENTORY.OS2}',
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}'
						],
						'macros_rw_3' => [
							'{HOST.ID}' => 'macros_rw_3',
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => 'host macro other value',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.HOST}' => 'api_test_host_macros_rw_3',
							'{HOST.NAME}' => 'API test host - macros 3, read-write',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => '{HOST.NAME1}',
							'{HOST.NAME2}' => '{HOST.NAME2}',
							'{EVENT.ID}' => '{EVENT.ID}',
							'{EVENT.NAME}' => '{EVENT.NAME}',
							'{EVENT.NSEVERITY}' => '{EVENT.NSEVERITY}',
							'{EVENT.SEVERITY}' => '{EVENT.SEVERITY}',
							'{USER.FULLNAME}' => 'Zabbix Administrator (Admin)',
							'{USER.NAME}' => 'Zabbix',
							'{USER.SURNAME}' => 'Administrator',
							'{USER.USERNAME}' => 'Admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '{INVENTORY.OS1}',
							'{INVENTORY.OS2}' => '{INVENTORY.OS2}',
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}'
						],
						'interface_rw_1' => [
							'{HOST.ID}' => 'interface_rw_1',
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.HOST}' => 'api_test_host_interface_rw_1',
							'{HOST.NAME}' => 'API test host - interface (read-write) 1',
							'{HOST.CONN}' => '1.1.1.1',
							'{HOST.IP}' => '1.1.1.1',
							'{HOST.DNS}' => '',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => '{HOST.NAME1}',
							'{HOST.NAME2}' => '{HOST.NAME2}',
							'{EVENT.ID}' => '{EVENT.ID}',
							'{EVENT.NAME}' => '{EVENT.NAME}',
							'{EVENT.NSEVERITY}' => '{EVENT.NSEVERITY}',
							'{EVENT.SEVERITY}' => '{EVENT.SEVERITY}',
							'{USER.FULLNAME}' => 'Zabbix Administrator (Admin)',
							'{USER.NAME}' => 'Zabbix',
							'{USER.SURNAME}' => 'Administrator',
							'{USER.USERNAME}' => 'Admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '{INVENTORY.OS1}',
							'{INVENTORY.OS2}' => '{INVENTORY.OS2}',
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}'
						],
						'interface_rw_2' => [
							'{HOST.ID}' => 'interface_rw_2',
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.HOST}' => 'api_test_host_interface_rw_2',
							'{HOST.NAME}' => 'API test host - interface (read-write) 2',
							'{HOST.CONN}' => 'dns_name',
							'{HOST.IP}' => '',
							'{HOST.DNS}' => 'dns_name',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => '{HOST.NAME1}',
							'{HOST.NAME2}' => '{HOST.NAME2}',
							'{EVENT.ID}' => '{EVENT.ID}',
							'{EVENT.NAME}' => '{EVENT.NAME}',
							'{EVENT.NSEVERITY}' => '{EVENT.NSEVERITY}',
							'{EVENT.SEVERITY}' => '{EVENT.SEVERITY}',
							'{USER.FULLNAME}' => 'Zabbix Administrator (Admin)',
							'{USER.NAME}' => 'Zabbix',
							'{USER.SURNAME}' => 'Administrator',
							'{USER.USERNAME}' => 'Admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '{INVENTORY.OS1}',
							'{INVENTORY.OS2}' => '{INVENTORY.OS2}',
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}'
						],
						'inventory_rw_1' => [
							'{HOST.ID}' => 'inventory_rw_1',
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.HOST}' => 'api_test_host_inventory_rw_1',
							'{HOST.NAME}' => 'API test host - inventory (read-write) 1',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => '{HOST.NAME1}',
							'{HOST.NAME2}' => '{HOST.NAME2}',
							'{EVENT.ID}' => '{EVENT.ID}',
							'{EVENT.NAME}' => '{EVENT.NAME}',
							'{EVENT.NSEVERITY}' => '{EVENT.NSEVERITY}',
							'{EVENT.SEVERITY}' => '{EVENT.SEVERITY}',
							'{USER.FULLNAME}' => 'Zabbix Administrator (Admin)',
							'{USER.NAME}' => 'Zabbix',
							'{USER.SURNAME}' => 'Administrator',
							'{USER.USERNAME}' => 'Admin',
							'{INVENTORY.ALIAS}' => '',
							'{INVENTORY.OS}' => 'Windows',
							'{INVENTORY.TYPE}' => '',
							'{INVENTORY.CONTACT}' => '',
							'{INVENTORY.OS1}' => '{INVENTORY.OS1}',
							'{INVENTORY.OS2}' => '{INVENTORY.OS2}',
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}'
						],
						'inventory_rw_2' => [
							'{HOST.ID}' => 'inventory_rw_2',
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.HOST}' => 'api_test_host_inventory_rw_2',
							'{HOST.NAME}' => 'API test host - inventory (read-write) 2',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => '{HOST.NAME1}',
							'{HOST.NAME2}' => '{HOST.NAME2}',
							'{EVENT.ID}' => '{EVENT.ID}',
							'{EVENT.NAME}' => '{EVENT.NAME}',
							'{EVENT.NSEVERITY}' => '{EVENT.NSEVERITY}',
							'{EVENT.SEVERITY}' => '{EVENT.SEVERITY}',
							'{USER.FULLNAME}' => 'Zabbix Administrator (Admin)',
							'{USER.NAME}' => 'Zabbix',
							'{USER.SURNAME}' => 'Administrator',
							'{USER.USERNAME}' => 'Admin',
							'{INVENTORY.ALIAS}' => 'Inventory Alias',
							'{INVENTORY.OS}' => '',
							'{INVENTORY.TYPE}' => '',
							'{INVENTORY.CONTACT}' => '',
							'{INVENTORY.OS1}' => '{INVENTORY.OS1}',
							'{INVENTORY.OS2}' => '{INVENTORY.OS2}',
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}'
						]
					]
				],
				'expected_error' => null
			],
			'Test script.getScriptsByHosts with admin' => [
				'request' => [
					'login' => ['user' => 'api_test_admin', 'password' => '4P1T3$tEr'],
					'hostids' => [
						'plain_r', 'plain_d', 'macros_rw_1', 'macros_r_2', 'macros_rw_3', 'interface_rw_1',
						'interface_rw_2', 'inventory_rw_1', 'inventory_rw_2'
					]
				],
				'expected_result' => [
					'has.hostid:scriptid' => [
						// Regular admin does not have all scripts available.
						'plain_r' => ['get_hosts_url', 'get_hosts_ipmi'],
						'plain_d' => [],
						'macros_rw_1' => ['get_hosts_url', 'get_hosts_ipmi', 'get_hosts_ssh'],
						'macros_r_2' => ['get_hosts_url', 'get_hosts_ipmi'],
						'macros_rw_3' => ['get_hosts_url', 'get_hosts_ipmi', 'get_hosts_ssh'],
						'interface_rw_1' => ['get_hosts_url', 'get_hosts_ipmi', 'get_hosts_ssh'],
						'interface_rw_2' => ['get_hosts_url', 'get_hosts_ipmi', 'get_hosts_ssh'],
						'inventory_rw_1' => ['get_hosts_url', 'get_hosts_ipmi', 'get_hosts_ssh'],
						'inventory_rw_2' => ['get_hosts_url', 'get_hosts_ipmi', 'get_hosts_ssh']
					],
					'!has.hostid:scriptid' => [
						'plain_r' => ['get_hosts_webhook', 'get_hosts_ssh'],
						'plain_d' => ['get_hosts_url', 'get_hosts_webhook', 'get_hosts_ipmi', 'get_hosts_ssh'],
						'macros_rw_1' => ['get_hosts_webhook'],
						'macros_r_2' => ['get_hosts_webhook', 'get_hosts_ssh'],
						'macros_rw_3' => ['get_hosts_webhook'],
						'interface_rw_1' => ['get_hosts_webhook'],
						'interface_rw_2' => ['get_hosts_webhook'],
						'inventory_rw_1' => ['get_hosts_webhook'],
						'inventory_rw_2' => ['get_hosts_webhook']
					],
					'scripts' => [
						[
							'scriptid' => 'get_hosts_url',
							'name' => 'API test script.getScriptsByHosts - URL',
							'command' => '',
							'host_access' => (string) PERM_READ,
							'usrgrpid' => '0',
							'groupid' => '0',
							'description' => '',
							'confirmation' => 'Confirmation macros: {$HOST_MACRO}, {$HOST_MACRO_OTHER},'.
								' {$GLOBAL_MACRO}, {$DOESNOTEXIST}, {HOST.ID}, {HOST.HOST}, {HOST.NAME},'.
								' {HOST.CONN}, {HOST.DNS}, {HOST.PORT}, {HOST.NAME1}, {HOST.NAME2}, {EVENT.ID},'.
								' {EVENT.NAME}, {EVENT.NSEVERITY}, {EVENT.SEVERITY}',
							'type' => (string) ZBX_SCRIPT_TYPE_URL,
							'execute_on' => (string) ZBX_SCRIPT_EXECUTE_ON_PROXY,
							'timeout' => '30s',
							'scope' => (string) ZBX_SCRIPT_SCOPE_HOST,
							'port' => '',
							'authtype' => (string) ITEM_AUTHTYPE_PASSWORD,
							'username' => '',
							'password' => '',
							'publickey' => '',
							'privatekey' => '',
							'menu_path' => '',
							'url' => 'http://zabbix/ui/zabbix.php?action=host.edit&hostid={HOST.ID}',
							'new_window' => (string) ZBX_SCRIPT_URL_NEW_WINDOW_YES
						],
						[
							'scriptid' => 'get_hosts_ipmi',
							'name' => 'API test script.getScriptsByHosts - IPMI',
							'command' => 'reboot server',
							'host_access' => (string) PERM_READ,
							'usrgrpid' => 'admin',
							'groupid' => '0',
							'description' => '',
							'confirmation' => 'Confirmation macros: {USER.FULLNAME}, {USER.NAME}, {USER.SURNAME},'.
								' {USER.USERNAME}, {INVENTORY.ALIAS}, {INVENTORY.OS}, {INVENTORY.TYPE},'.
								' {INVENTORY.CONTACT}, {INVENTORY.OS1}, {INVENTORY.OS2}, {HOSTGROUP.ID}',
							'type' => (string) ZBX_SCRIPT_TYPE_IPMI,
							'execute_on' => (string) ZBX_SCRIPT_EXECUTE_ON_PROXY,
							'timeout' => '30s',
							'scope' => (string) ZBX_SCRIPT_SCOPE_HOST,
							'port' => '',
							'authtype' => (string) ITEM_AUTHTYPE_PASSWORD,
							'username' => '',
							'password' => '',
							'publickey' => '',
							'privatekey' => '',
							'menu_path' => '',
							'url' => '',
							'new_window' => (string) ZBX_SCRIPT_URL_NEW_WINDOW_YES
						],
						[
							'scriptid' => 'get_hosts_ssh',
							'name' => 'API test script.getScriptsByHosts - SSH password',
							'command' => 'reboot server',
							'host_access' => (string) PERM_READ_WRITE,
							'usrgrpid' => '0',
							'groupid' => '0',
							'description' => '',
							'confirmation' => 'Confirmation macros: {$GLOBAL_MACRO}, {HOST.HOST}, {USER.FULLNAME},'.
								' {HOST.CONN}, {HOST.IP}, {HOST.DNS}, {HOST.PORT}, {INVENTORY.ALIAS},'.
								' {INVENTORY.OS}, {INVENTORY.TYPE}',
							'type' => (string) ZBX_SCRIPT_TYPE_SSH,
							'execute_on' => (string) ZBX_SCRIPT_EXECUTE_ON_PROXY,
							'timeout' => '30s',
							'scope' => (string) ZBX_SCRIPT_SCOPE_HOST,
							'port' => '',
							'authtype' => (string) ITEM_AUTHTYPE_PASSWORD,
							'username' => 'user',
							'password' => '',
							'publickey' => '',
							'privatekey' => '',
							'menu_path' => '',
							'url' => '',
							'new_window' => (string) ZBX_SCRIPT_URL_NEW_WINDOW_YES
						]
					],
					'host_macros' => [
						'plain_r' => [
							'{HOST.ID}' => 'plain_r',
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.HOST}' => 'api_test_host_plain_r',
							'{HOST.NAME}' => 'API test host - plain, read',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => '{HOST.NAME1}',
							'{HOST.NAME2}' => '{HOST.NAME2}',
							'{EVENT.ID}' => '{EVENT.ID}',
							'{EVENT.NAME}' => '{EVENT.NAME}',
							'{EVENT.NSEVERITY}' => '{EVENT.NSEVERITY}',
							'{EVENT.SEVERITY}' => '{EVENT.SEVERITY}',
							'{USER.FULLNAME}' => 'API One Tester One (api_test_admin)',
							'{USER.NAME}' => 'API One',
							'{USER.SURNAME}' => 'Tester One',
							'{USER.USERNAME}' => 'api_test_admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '{INVENTORY.OS1}',
							'{INVENTORY.OS2}' => '{INVENTORY.OS2}',
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}'
						],
						'macros_rw_1' => [
							'{HOST.ID}' => 'macros_rw_1',
							'{$HOST_MACRO}' => 'host macro value - 1',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.HOST}' => 'api_test_host_macros_rw_1',
							'{HOST.NAME}' => 'API test host - macros 1, read-write',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => '{HOST.NAME1}',
							'{HOST.NAME2}' => '{HOST.NAME2}',
							'{EVENT.ID}' => '{EVENT.ID}',
							'{EVENT.NAME}' => '{EVENT.NAME}',
							'{EVENT.NSEVERITY}' => '{EVENT.NSEVERITY}',
							'{EVENT.SEVERITY}' => '{EVENT.SEVERITY}',
							'{USER.FULLNAME}' => 'API One Tester One (api_test_admin)',
							'{USER.NAME}' => 'API One',
							'{USER.SURNAME}' => 'Tester One',
							'{USER.USERNAME}' => 'api_test_admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '{INVENTORY.OS1}',
							'{INVENTORY.OS2}' => '{INVENTORY.OS2}',
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}'
						],
						'macros_r_2' => [
							'{HOST.ID}' => 'macros_r_2',
							'{$HOST_MACRO}' => 'host macro value - 2',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.HOST}' => 'api_test_host_macros_r_2',
							'{HOST.NAME}' => 'API test host - macros 2, read',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => '{HOST.NAME1}',
							'{HOST.NAME2}' => '{HOST.NAME2}',
							'{EVENT.ID}' => '{EVENT.ID}',
							'{EVENT.NAME}' => '{EVENT.NAME}',
							'{EVENT.NSEVERITY}' => '{EVENT.NSEVERITY}',
							'{EVENT.SEVERITY}' => '{EVENT.SEVERITY}',
							'{USER.FULLNAME}' => 'API One Tester One (api_test_admin)',
							'{USER.NAME}' => 'API One',
							'{USER.SURNAME}' => 'Tester One',
							'{USER.USERNAME}' => 'api_test_admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '{INVENTORY.OS1}',
							'{INVENTORY.OS2}' => '{INVENTORY.OS2}',
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}'
						],
						'macros_rw_3' => [
							'{HOST.ID}' => 'macros_rw_3',
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => 'host macro other value',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.HOST}' => 'api_test_host_macros_rw_3',
							'{HOST.NAME}' => 'API test host - macros 3, read-write',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => '{HOST.NAME1}',
							'{HOST.NAME2}' => '{HOST.NAME2}',
							'{EVENT.ID}' => '{EVENT.ID}',
							'{EVENT.NAME}' => '{EVENT.NAME}',
							'{EVENT.NSEVERITY}' => '{EVENT.NSEVERITY}',
							'{EVENT.SEVERITY}' => '{EVENT.SEVERITY}',
							'{USER.FULLNAME}' => 'API One Tester One (api_test_admin)',
							'{USER.NAME}' => 'API One',
							'{USER.SURNAME}' => 'Tester One',
							'{USER.USERNAME}' => 'api_test_admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '{INVENTORY.OS1}',
							'{INVENTORY.OS2}' => '{INVENTORY.OS2}',
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}'
						],
						'interface_rw_1' => [
							'{HOST.ID}' => 'interface_rw_1',
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.HOST}' => 'api_test_host_interface_rw_1',
							'{HOST.NAME}' => 'API test host - interface (read-write) 1',
							'{HOST.CONN}' => '1.1.1.1',
							'{HOST.IP}' => '1.1.1.1',
							'{HOST.DNS}' => '',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => '{HOST.NAME1}',
							'{HOST.NAME2}' => '{HOST.NAME2}',
							'{EVENT.ID}' => '{EVENT.ID}',
							'{EVENT.NAME}' => '{EVENT.NAME}',
							'{EVENT.NSEVERITY}' => '{EVENT.NSEVERITY}',
							'{EVENT.SEVERITY}' => '{EVENT.SEVERITY}',
							'{USER.FULLNAME}' => 'API One Tester One (api_test_admin)',
							'{USER.NAME}' => 'API One',
							'{USER.SURNAME}' => 'Tester One',
							'{USER.USERNAME}' => 'api_test_admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '{INVENTORY.OS1}',
							'{INVENTORY.OS2}' => '{INVENTORY.OS2}',
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}'
						],
						'interface_rw_2' => [
							'{HOST.ID}' => 'interface_rw_2',
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.HOST}' => 'api_test_host_interface_rw_2',
							'{HOST.NAME}' => 'API test host - interface (read-write) 2',
							'{HOST.CONN}' => 'dns_name',
							'{HOST.IP}' => '',
							'{HOST.DNS}' => 'dns_name',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => '{HOST.NAME1}',
							'{HOST.NAME2}' => '{HOST.NAME2}',
							'{EVENT.ID}' => '{EVENT.ID}',
							'{EVENT.NAME}' => '{EVENT.NAME}',
							'{EVENT.NSEVERITY}' => '{EVENT.NSEVERITY}',
							'{EVENT.SEVERITY}' => '{EVENT.SEVERITY}',
							'{USER.FULLNAME}' => 'API One Tester One (api_test_admin)',
							'{USER.NAME}' => 'API One',
							'{USER.SURNAME}' => 'Tester One',
							'{USER.USERNAME}' => 'api_test_admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '{INVENTORY.OS1}',
							'{INVENTORY.OS2}' => '{INVENTORY.OS2}',
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}'
						],
						'inventory_rw_1' => [
							'{HOST.ID}' => 'inventory_rw_1',
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.HOST}' => 'api_test_host_inventory_rw_1',
							'{HOST.NAME}' => 'API test host - inventory (read-write) 1',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => '{HOST.NAME1}',
							'{HOST.NAME2}' => '{HOST.NAME2}',
							'{EVENT.ID}' => '{EVENT.ID}',
							'{EVENT.NAME}' => '{EVENT.NAME}',
							'{EVENT.NSEVERITY}' => '{EVENT.NSEVERITY}',
							'{EVENT.SEVERITY}' => '{EVENT.SEVERITY}',
							'{USER.FULLNAME}' => 'API One Tester One (api_test_admin)',
							'{USER.NAME}' => 'API One',
							'{USER.SURNAME}' => 'Tester One',
							'{USER.USERNAME}' => 'api_test_admin',
							'{INVENTORY.ALIAS}' => '',
							'{INVENTORY.OS}' => 'Windows',
							'{INVENTORY.TYPE}' => '',
							'{INVENTORY.CONTACT}' => '',
							'{INVENTORY.OS1}' => '{INVENTORY.OS1}',
							'{INVENTORY.OS2}' => '{INVENTORY.OS2}',
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}'
						],
						'inventory_rw_2' => [
							'{HOST.ID}' => 'inventory_rw_2',
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.HOST}' => 'api_test_host_inventory_rw_2',
							'{HOST.NAME}' => 'API test host - inventory (read-write) 2',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => '{HOST.NAME1}',
							'{HOST.NAME2}' => '{HOST.NAME2}',
							'{EVENT.ID}' => '{EVENT.ID}',
							'{EVENT.NAME}' => '{EVENT.NAME}',
							'{EVENT.NSEVERITY}' => '{EVENT.NSEVERITY}',
							'{EVENT.SEVERITY}' => '{EVENT.SEVERITY}',
							'{USER.FULLNAME}' => 'API One Tester One (api_test_admin)',
							'{USER.NAME}' => 'API One',
							'{USER.SURNAME}' => 'Tester One',
							'{USER.USERNAME}' => 'api_test_admin',
							'{INVENTORY.ALIAS}' => 'Inventory Alias',
							'{INVENTORY.OS}' => '',
							'{INVENTORY.TYPE}' => '',
							'{INVENTORY.CONTACT}' => '',
							'{INVENTORY.OS1}' => '{INVENTORY.OS1}',
							'{INVENTORY.OS2}' => '{INVENTORY.OS2}',
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}'
						]
					]
				],
				'expected_error' => null
			],
			'Test script.getScriptsByHosts with user' => [
				'request' => [
					'login' => ['user' => 'api_test_user', 'password' => '4P1T3$tEr'],
					'hostids' => [
						'plain_r', 'plain_d', 'macros_rw_1', 'macros_r_2', 'macros_rw_3', 'interface_rw_1',
						'interface_rw_2', 'inventory_rw_1', 'inventory_rw_2'
					]
				],
				'expected_result' => [
					'has.hostid:scriptid' => [
						// Regular user does not have all scripts available.
						'plain_r' => ['get_hosts_url', 'get_hosts_webhook'],
						'plain_d' => [],
						'macros_rw_1' => ['get_hosts_url', 'get_hosts_webhook', 'get_hosts_ssh'],
						'macros_r_2' => ['get_hosts_url', 'get_hosts_webhook'],
						'macros_rw_3' => ['get_hosts_url', 'get_hosts_webhook', 'get_hosts_ssh'],
						'interface_rw_1' => ['get_hosts_url', 'get_hosts_webhook', 'get_hosts_ssh'],
						'interface_rw_2' => ['get_hosts_url', 'get_hosts_webhook', 'get_hosts_ssh'],
						'inventory_rw_1' => ['get_hosts_url', 'get_hosts_webhook', 'get_hosts_ssh'],
						'inventory_rw_2' => ['get_hosts_url', 'get_hosts_webhook', 'get_hosts_ssh']
					],
					'!has.hostid:scriptid' => [
						'plain_r' => ['get_hosts_ipmi', 'get_hosts_ssh'],
						'plain_d' => ['get_hosts_url', 'get_hosts_webhook', 'get_hosts_ipmi', 'get_hosts_ssh'],
						'macros_rw_1' => ['get_hosts_ipmi'],
						'macros_r_2' => ['get_hosts_ipmi', 'get_hosts_ssh'],
						'macros_rw_3' => ['get_hosts_ipmi'],
						'interface_rw_1' => ['get_hosts_ipmi'],
						'interface_rw_2' => ['get_hosts_ipmi'],
						'inventory_rw_1' => ['get_hosts_ipmi'],
						'inventory_rw_2' => ['get_hosts_ipmi']
					],
					'scripts' => [
						[
							'scriptid' => 'get_hosts_url',
							'name' => 'API test script.getScriptsByHosts - URL',
							'command' => '',
							'host_access' => (string) PERM_READ,
							'usrgrpid' => '0',
							'groupid' => '0',
							'description' => '',
							'confirmation' => 'Confirmation macros: {$HOST_MACRO}, {$HOST_MACRO_OTHER},'.
								' {$GLOBAL_MACRO}, {$DOESNOTEXIST}, {HOST.ID}, {HOST.HOST}, {HOST.NAME},'.
								' {HOST.CONN}, {HOST.DNS}, {HOST.PORT}, {HOST.NAME1}, {HOST.NAME2}, {EVENT.ID},'.
								' {EVENT.NAME}, {EVENT.NSEVERITY}, {EVENT.SEVERITY}',
							'type' => (string) ZBX_SCRIPT_TYPE_URL,
							'execute_on' => (string) ZBX_SCRIPT_EXECUTE_ON_PROXY,
							'timeout' => '30s',
							'scope' => (string) ZBX_SCRIPT_SCOPE_HOST,
							'port' => '',
							'authtype' => (string) ITEM_AUTHTYPE_PASSWORD,
							'username' => '',
							'password' => '',
							'publickey' => '',
							'privatekey' => '',
							'menu_path' => '',
							'url' => 'http://zabbix/ui/zabbix.php?action=host.edit&hostid={HOST.ID}',
							'new_window' => (string) ZBX_SCRIPT_URL_NEW_WINDOW_YES
						],
						[
							'scriptid' => 'get_hosts_webhook',
							'name' => 'API test script.getScriptsByHosts - Webhook',
							'command' => 'reboot server',
							'host_access' => (string) PERM_READ,
							'usrgrpid' => 'user',
							'groupid' => '0',
							'description' => '',
							'confirmation' => 'Confirmation macros: {$GLOBAL_MACRO}, {HOST.HOST}, {USER.FULLNAME},'.
								' {HOST.CONN}, {HOST.IP}, {HOST.DNS}, {HOST.PORT}',
							'type' => (string) ZBX_SCRIPT_TYPE_WEBHOOK,
							'execute_on' => (string) ZBX_SCRIPT_EXECUTE_ON_PROXY,
							'timeout' => '30s',
							'scope' => (string) ZBX_SCRIPT_SCOPE_HOST,
							'port' => '',
							'authtype' => (string) ITEM_AUTHTYPE_PASSWORD,
							'username' => '',
							'password' => '',
							'publickey' => '',
							'privatekey' => '',
							'menu_path' => '',
							'url' => '',
							'new_window' => (string) ZBX_SCRIPT_URL_NEW_WINDOW_YES
						],
						[
							'scriptid' => 'get_hosts_ssh',
							'name' => 'API test script.getScriptsByHosts - SSH password',
							'command' => 'reboot server',
							'host_access' => (string) PERM_READ_WRITE,
							'usrgrpid' => '0',
							'groupid' => '0',
							'description' => '',
							'confirmation' => 'Confirmation macros: {$GLOBAL_MACRO}, {HOST.HOST}, {USER.FULLNAME},'.
								' {HOST.CONN}, {HOST.IP}, {HOST.DNS}, {HOST.PORT}, {INVENTORY.ALIAS},'.
								' {INVENTORY.OS}, {INVENTORY.TYPE}',
							'type' => (string) ZBX_SCRIPT_TYPE_SSH,
							'execute_on' => (string) ZBX_SCRIPT_EXECUTE_ON_PROXY,
							'timeout' => '30s',
							'scope' => (string) ZBX_SCRIPT_SCOPE_HOST,
							'port' => '',
							'authtype' => (string) ITEM_AUTHTYPE_PASSWORD,
							'username' => 'user',
							'password' => '',
							'publickey' => '',
							'privatekey' => '',
							'menu_path' => '',
							'url' => '',
							'new_window' => (string) ZBX_SCRIPT_URL_NEW_WINDOW_YES
						]
					],
					'host_macros' => [
						'plain_r' => [
							'{HOST.ID}' => 'plain_r',
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.HOST}' => 'api_test_host_plain_r',
							'{HOST.NAME}' => 'API test host - plain, read',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => '{HOST.NAME1}',
							'{HOST.NAME2}' => '{HOST.NAME2}',
							'{EVENT.ID}' => '{EVENT.ID}',
							'{EVENT.NAME}' => '{EVENT.NAME}',
							'{EVENT.NSEVERITY}' => '{EVENT.NSEVERITY}',
							'{EVENT.SEVERITY}' => '{EVENT.SEVERITY}',
							'{USER.FULLNAME}' => 'API Two Tester Two (api_test_user)',
							'{USER.NAME}' => 'API Two',
							'{USER.SURNAME}' => 'Tester Two',
							'{USER.USERNAME}' => 'api_test_user',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '{INVENTORY.OS1}',
							'{INVENTORY.OS2}' => '{INVENTORY.OS2}',
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}'
						],
						'macros_rw_1' => [
							'{HOST.ID}' => 'macros_rw_1',
							'{$HOST_MACRO}' => 'host macro value - 1',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.HOST}' => 'api_test_host_macros_rw_1',
							'{HOST.NAME}' => 'API test host - macros 1, read-write',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => '{HOST.NAME1}',
							'{HOST.NAME2}' => '{HOST.NAME2}',
							'{EVENT.ID}' => '{EVENT.ID}',
							'{EVENT.NAME}' => '{EVENT.NAME}',
							'{EVENT.NSEVERITY}' => '{EVENT.NSEVERITY}',
							'{EVENT.SEVERITY}' => '{EVENT.SEVERITY}',
							'{USER.FULLNAME}' => 'API Two Tester Two (api_test_user)',
							'{USER.NAME}' => 'API Two',
							'{USER.SURNAME}' => 'Tester Two',
							'{USER.USERNAME}' => 'api_test_user',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '{INVENTORY.OS1}',
							'{INVENTORY.OS2}' => '{INVENTORY.OS2}',
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}'
						],
						'macros_r_2' => [
							'{HOST.ID}' => 'macros_r_2',
							'{$HOST_MACRO}' => 'host macro value - 2',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.HOST}' => 'api_test_host_macros_r_2',
							'{HOST.NAME}' => 'API test host - macros 2, read',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => '{HOST.NAME1}',
							'{HOST.NAME2}' => '{HOST.NAME2}',
							'{EVENT.ID}' => '{EVENT.ID}',
							'{EVENT.NAME}' => '{EVENT.NAME}',
							'{EVENT.NSEVERITY}' => '{EVENT.NSEVERITY}',
							'{EVENT.SEVERITY}' => '{EVENT.SEVERITY}',
							'{USER.FULLNAME}' => 'API Two Tester Two (api_test_user)',
							'{USER.NAME}' => 'API Two',
							'{USER.SURNAME}' => 'Tester Two',
							'{USER.USERNAME}' => 'api_test_user',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '{INVENTORY.OS1}',
							'{INVENTORY.OS2}' => '{INVENTORY.OS2}',
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}'
						],
						'macros_rw_3' => [
							'{HOST.ID}' => 'macros_rw_3',
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => 'host macro other value',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.HOST}' => 'api_test_host_macros_rw_3',
							'{HOST.NAME}' => 'API test host - macros 3, read-write',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => '{HOST.NAME1}',
							'{HOST.NAME2}' => '{HOST.NAME2}',
							'{EVENT.ID}' => '{EVENT.ID}',
							'{EVENT.NAME}' => '{EVENT.NAME}',
							'{EVENT.NSEVERITY}' => '{EVENT.NSEVERITY}',
							'{EVENT.SEVERITY}' => '{EVENT.SEVERITY}',
							'{USER.FULLNAME}' => 'API Two Tester Two (api_test_user)',
							'{USER.NAME}' => 'API Two',
							'{USER.SURNAME}' => 'Tester Two',
							'{USER.USERNAME}' => 'api_test_user',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '{INVENTORY.OS1}',
							'{INVENTORY.OS2}' => '{INVENTORY.OS2}',
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}'
						],
						'interface_rw_1' => [
							'{HOST.ID}' => 'interface_rw_1',
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.HOST}' => 'api_test_host_interface_rw_1',
							'{HOST.NAME}' => 'API test host - interface (read-write) 1',
							'{HOST.CONN}' => '1.1.1.1',
							'{HOST.IP}' => '1.1.1.1',
							'{HOST.DNS}' => '',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => '{HOST.NAME1}',
							'{HOST.NAME2}' => '{HOST.NAME2}',
							'{EVENT.ID}' => '{EVENT.ID}',
							'{EVENT.NAME}' => '{EVENT.NAME}',
							'{EVENT.NSEVERITY}' => '{EVENT.NSEVERITY}',
							'{EVENT.SEVERITY}' => '{EVENT.SEVERITY}',
							'{USER.FULLNAME}' => 'API Two Tester Two (api_test_user)',
							'{USER.NAME}' => 'API Two',
							'{USER.SURNAME}' => 'Tester Two',
							'{USER.USERNAME}' => 'api_test_user',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '{INVENTORY.OS1}',
							'{INVENTORY.OS2}' => '{INVENTORY.OS2}',
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}'
						],
						'interface_rw_2' => [
							'{HOST.ID}' => 'interface_rw_2',
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.HOST}' => 'api_test_host_interface_rw_2',
							'{HOST.NAME}' => 'API test host - interface (read-write) 2',
							'{HOST.CONN}' => 'dns_name',
							'{HOST.IP}' => '',
							'{HOST.DNS}' => 'dns_name',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => '{HOST.NAME1}',
							'{HOST.NAME2}' => '{HOST.NAME2}',
							'{EVENT.ID}' => '{EVENT.ID}',
							'{EVENT.NAME}' => '{EVENT.NAME}',
							'{EVENT.NSEVERITY}' => '{EVENT.NSEVERITY}',
							'{EVENT.SEVERITY}' => '{EVENT.SEVERITY}',
							'{USER.FULLNAME}' => 'API Two Tester Two (api_test_user)',
							'{USER.NAME}' => 'API Two',
							'{USER.SURNAME}' => 'Tester Two',
							'{USER.USERNAME}' => 'api_test_user',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '{INVENTORY.OS1}',
							'{INVENTORY.OS2}' => '{INVENTORY.OS2}',
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}'
						],
						'inventory_rw_1' => [
							'{HOST.ID}' => 'inventory_rw_1',
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.HOST}' => 'api_test_host_inventory_rw_1',
							'{HOST.NAME}' => 'API test host - inventory (read-write) 1',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => '{HOST.NAME1}',
							'{HOST.NAME2}' => '{HOST.NAME2}',
							'{EVENT.ID}' => '{EVENT.ID}',
							'{EVENT.NAME}' => '{EVENT.NAME}',
							'{EVENT.NSEVERITY}' => '{EVENT.NSEVERITY}',
							'{EVENT.SEVERITY}' => '{EVENT.SEVERITY}',
							'{USER.FULLNAME}' => 'API Two Tester Two (api_test_user)',
							'{USER.NAME}' => 'API Two',
							'{USER.SURNAME}' => 'Tester Two',
							'{USER.USERNAME}' => 'api_test_user',
							'{INVENTORY.ALIAS}' => '',
							'{INVENTORY.OS}' => 'Windows',
							'{INVENTORY.TYPE}' => '',
							'{INVENTORY.CONTACT}' => '',
							'{INVENTORY.OS1}' => '{INVENTORY.OS1}',
							'{INVENTORY.OS2}' => '{INVENTORY.OS2}',
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}'
						],
						'inventory_rw_2' => [
							'{HOST.ID}' => 'inventory_rw_2',
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.HOST}' => 'api_test_host_inventory_rw_2',
							'{HOST.NAME}' => 'API test host - inventory (read-write) 2',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => '{HOST.NAME1}',
							'{HOST.NAME2}' => '{HOST.NAME2}',
							'{EVENT.ID}' => '{EVENT.ID}',
							'{EVENT.NAME}' => '{EVENT.NAME}',
							'{EVENT.NSEVERITY}' => '{EVENT.NSEVERITY}',
							'{EVENT.SEVERITY}' => '{EVENT.SEVERITY}',
							'{USER.FULLNAME}' => 'API Two Tester Two (api_test_user)',
							'{USER.NAME}' => 'API Two',
							'{USER.SURNAME}' => 'Tester Two',
							'{USER.USERNAME}' => 'api_test_user',
							'{INVENTORY.ALIAS}' => 'Inventory Alias',
							'{INVENTORY.OS}' => '',
							'{INVENTORY.TYPE}' => '',
							'{INVENTORY.CONTACT}' => '',
							'{INVENTORY.OS1}' => '{INVENTORY.OS1}',
							'{INVENTORY.OS2}' => '{INVENTORY.OS2}',
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}'
						]
					]
				],
				'expected_error' => null
			]
		];
	}

	/**
	 * Test script.getScriptsByHosts with various users. Checks if result has host IDs keys, script IDs, resolves macros
	 * for script and compares results.
	 *
	 * @dataProvider getScriptsByHostsDataInvalid
	 * @dataProvider getScriptsByHostsDataValid
	 */
	public function testScripts_GetScriptsByHosts($request, $expected_result, $expected_error) {
		if (array_key_exists('login', $request)) {
			$this->authorize($request['login']['user'], $request['login']['password']);
		}

		$request = self::resolveIds($request);

		if ($expected_error === null) {
			foreach ($expected_result['scripts'] as &$script) {
				$script = self::resolveIds($script);
			}
			unset($script);

			$expected_result = self::resolveMacros($expected_result);
			$expected_result = self::resolveComplexIds($expected_result);
		}

		$result = $this->call('script.getScriptsByHosts', $request['hostids'], $expected_error);

		if ($expected_error === null) {
			if (array_key_exists('has.hostid:scriptid', $expected_result)) {
				foreach ($expected_result['has.hostid:scriptid'] as $hostid => $scriptids) {
					$this->assertTrue(array_key_exists($hostid, $result['result']), 'expected host ID '.$hostid);
					$ids = array_column($result['result'][$hostid], 'scriptid');
					$this->assertEmpty(array_diff($scriptids, $ids), 'Expected ids: '.implode(',', $scriptids));
				}
			}

			if (array_key_exists('!has.hostid:scriptid', $expected_result)) {
				foreach ($expected_result['!has.hostid:scriptid'] as $hostid => $scriptids) {
					$this->assertTrue(array_key_exists($hostid, $result['result']), 'expected host ID '.$hostid);
					$ids = array_column($result['result'][$hostid], 'scriptid');
					$this->assertEquals($scriptids, array_diff($scriptids, $ids));
				}
			}

			foreach ($result['result'] as $hostid => $result_scripts) {
				foreach ($result_scripts as $result_script) {
					foreach ($expected_result['scripts'] as $expected_script) {
						if (bccomp($result_script['scriptid'], $expected_script['scriptid']) == 0) {

							$expected_script['url'] = strtr($expected_script['url'],
								$expected_result['host_macros'][$hostid]
							);
							$expected_script['confirmation'] = strtr($expected_script['confirmation'],
								$expected_result['host_macros'][$hostid]
							);

							$this->assertEquals($expected_script, $result_script);
						}
					}
				}
			}
		}
	}

	/**
	 * Data provider for script.getScriptsByEvents. Array contains invalid data.
	 *
	 * @return array
	 */
	public static function getScriptsByEventsDataInvalid() {
		return [
			'Test script.getScriptsByEvents invalid fields' => [
				'request' => [
					'eventids' => ['']
				],
				'expected_result' => [],
				'expected_error' => 'Invalid parameter "/1": a number is expected.'
			],
			'Test script.getScriptsByEvents identical IDs given' => [
				'request' => [
					'eventids' => [0, 0]
				],
				'expected_result' => [],
				'expected_error' => 'Invalid parameter "/2": value (0) already exists.'
			]
		];
	}

	/**
	 * Data provider for script.getScriptsByEvents. Array contains valid data. Checks if result contains cetrain scripts
	 * and those scripts contain fields with resolved macros. Some macros cannot be resolved. They either resolve to
	 * *UNKNOWN* or do not resolve at all. Each event and request can have different macros.
	 *
	 * @return array
	 */
	public static function getScriptsByEventsDataValid() {
		return [
			'Test script.getScriptsByEvents with superadmin' => [
				'request' => [
					'eventids' => [
						'plain_rw_single_d', 'plain_r_single_d', 'plain_d_single_d', 'plain_rw_r_dual_d',
						'macros_rw_single_1_h', 'macros_rw_r_dual_1_2_h', 'macros_rw_dual_1_3_h', 'interface_rw_dual_a',
						'inventory_rw_dual_a', 'macros_d_cause', 'macros_rw_symptom'
					]
				],
				'expected_result' => [
					'has.eventid:scriptid' => [
						// Superadmin has all scripts available.
						'plain_rw_single_d' => ['get_events_url', 'get_events_ipmi', 'get_events_webhook',
							'get_events_ssh', 'get_events_url_cause'
						],
						'plain_r_single_d' => ['get_events_url', 'get_events_ipmi', 'get_events_webhook',
							'get_events_ssh', 'get_events_url_cause'
						],
						'plain_d_single_d' => ['get_events_url', 'get_events_ipmi', 'get_events_webhook',
							'get_events_ssh', 'get_events_url_cause'
						],
						'plain_rw_r_dual_d' => ['get_events_url', 'get_events_ipmi', 'get_events_webhook',
							'get_events_ssh', 'get_events_url_cause'
						],
						'macros_rw_single_1_h' => ['get_events_url', 'get_events_ipmi', 'get_events_webhook',
							'get_events_ssh', 'get_events_url_cause'
						],
						'macros_rw_r_dual_1_2_h' => ['get_events_url', 'get_events_ipmi', 'get_events_webhook',
							'get_events_ssh', 'get_events_url_cause'
						],
						'macros_rw_dual_1_3_h' => ['get_events_url', 'get_events_ipmi', 'get_events_webhook',
							'get_events_ssh', 'get_events_url_cause'
						],
						'interface_rw_dual_a' => ['get_events_url', 'get_events_ipmi', 'get_events_webhook',
							'get_events_ssh', 'get_events_url_cause'
						],
						'inventory_rw_dual_a' => ['get_events_url', 'get_events_ipmi', 'get_events_webhook',
							'get_events_ssh', 'get_events_url_cause'
						],
						'macros_d_cause' => ['get_events_url', 'get_events_ipmi', 'get_events_webhook',
							'get_events_ssh', 'get_events_url_cause'
						],
						'macros_rw_symptom' => ['get_events_url', 'get_events_ipmi', 'get_events_webhook',
							'get_events_ssh', 'get_events_url_cause'
						]
					],
					'scripts' => [
						[
							'scriptid' => 'get_events_url',
							'name' => 'API test script.getScriptsByEvents - URL',
							'command' => '',
							'host_access' => (string) PERM_READ,
							'usrgrpid' => '0',
							'groupid' => '0',
							'description' => '',
							'confirmation' => 'Confirmation macros: {$HOST_MACRO}, {$HOST_MACRO_OTHER},'.
								' {$GLOBAL_MACRO}, {$DOESNOTEXIST}, {HOST.ID}, {HOST.HOST}, {HOST.NAME}, {HOST.CONN},'.
								' {HOST.DNS}, {HOST.PORT}, {HOST.NAME1}, {HOST.NAME2}, {EVENT.ID}, {EVENT.NAME},'.
								' {EVENT.NSEVERITY}, {EVENT.SEVERITY}',
							'type' => (string) ZBX_SCRIPT_TYPE_URL,
							'execute_on' => (string) ZBX_SCRIPT_EXECUTE_ON_PROXY,
							'timeout' => '30s',
							'scope' => (string) ZBX_SCRIPT_SCOPE_EVENT,
							'port' => '',
							'authtype' => (string) ITEM_AUTHTYPE_PASSWORD,
							'username' => '',
							'password' => '',
							'publickey' => '',
							'privatekey' => '',
							'menu_path' => '',
							'url' => 'http://zabbix/ui/zabbix.php?action=host.edit&hostid={HOST.ID}',
							'new_window' => (string) ZBX_SCRIPT_URL_NEW_WINDOW_YES
						],
						[
							'scriptid' => 'get_events_ipmi',
							'name' => 'API test script.getScriptsByEvents - IPMI',
							'command' => 'reboot server',
							'host_access' => (string) PERM_READ,
							'usrgrpid' => 'admin',
							'groupid' => '0',
							'description' => '',
							'confirmation' => 'Confirmation macros: {USER.FULLNAME}, {USER.NAME}, {USER.SURNAME},'.
								' {USER.USERNAME}, {INVENTORY.ALIAS}, {INVENTORY.OS}, {INVENTORY.TYPE},'.
								' {INVENTORY.CONTACT}, {INVENTORY.OS1}, {INVENTORY.OS2}, {EVENT.STATUS},'.
								' {EVENT.VALUE}, {HOSTGROUP.ID}',
							'type' => (string) ZBX_SCRIPT_TYPE_IPMI,
							'execute_on' => (string) ZBX_SCRIPT_EXECUTE_ON_PROXY,
							'timeout' => '30s',
							'scope' => (string) ZBX_SCRIPT_SCOPE_EVENT,
							'port' => '',
							'authtype' => (string) ITEM_AUTHTYPE_PASSWORD,
							'username' => '',
							'password' => '',
							'publickey' => '',
							'privatekey' => '',
							'menu_path' => '',
							'url' => '',
							'new_window' => (string) ZBX_SCRIPT_URL_NEW_WINDOW_YES
						],
						[
							'scriptid' => 'get_events_webhook',
							'name' => 'API test script.getScriptsByEvents - Webhook',
							'command' => 'reboot server',
							'host_access' => (string) PERM_READ,
							'usrgrpid' => 'user',
							'groupid' => '0',
							'description' => '',
							'confirmation' => 'Confirmation macros: {$GLOBAL_MACRO}, {HOST.HOST}, {USER.FULLNAME},'.
								' {HOST.CONN}, {HOST.IP}, {HOST.DNS}, {HOST.PORT}',
							'type' => (string) ZBX_SCRIPT_TYPE_WEBHOOK,
							'execute_on' => (string) ZBX_SCRIPT_EXECUTE_ON_PROXY,
							'timeout' => '30s',
							'scope' => (string) ZBX_SCRIPT_SCOPE_EVENT,
							'port' => '',
							'authtype' => (string) ITEM_AUTHTYPE_PASSWORD,
							'username' => '',
							'password' => '',
							'publickey' => '',
							'privatekey' => '',
							'menu_path' => '',
							'url' => '',
							'new_window' => (string) ZBX_SCRIPT_URL_NEW_WINDOW_YES
						],
						[
							'scriptid' => 'get_events_ssh',
							'name' => 'API test script.getScriptsByEvents - SSH password',
							'command' => 'reboot server',
							'host_access' => (string) PERM_READ_WRITE,
							'usrgrpid' => '0',
							'groupid' => '0',
							'description' => '',
							'confirmation' => 'Confirmation macros: {$GLOBAL_MACRO}, {HOST.HOST}, {USER.FULLNAME},'.
								' {HOST.CONN}, {HOST.IP}, {HOST.DNS}, {HOST.PORT}, {INVENTORY.ALIAS}, {INVENTORY.OS},'.
								' {INVENTORY.TYPE}',
							'type' => (string) ZBX_SCRIPT_TYPE_SSH,
							'execute_on' => (string) ZBX_SCRIPT_EXECUTE_ON_PROXY,
							'timeout' => '30s',
							'scope' => (string) ZBX_SCRIPT_SCOPE_EVENT,
							'port' => '',
							'authtype' => (string) ITEM_AUTHTYPE_PASSWORD,
							'username' => 'user',
							'password' => '',
							'publickey' => '',
							'privatekey' => '',
							'menu_path' => '',
							'url' => '',
							'new_window' => (string) ZBX_SCRIPT_URL_NEW_WINDOW_YES
						],
						[
							'scriptid' => 'get_events_url_cause',
							'name' => 'API test script.getScriptsByEvents - URL cause',
							'command' => '',
							'host_access' => (string) PERM_READ,
							'usrgrpid' => '0',
							'groupid' => '0',
							'description' => '',
							'confirmation' => 'Confirmation macros: {EVENT.CAUSE.ID}, {EVENT.CAUSE.NAME},'.
								' {EVENT.CAUSE.NSEVERITY}, {EVENT.CAUSE.SEVERITY}, {EVENT.CAUSE.STATUS},'.
								' {EVENT.CAUSE.VALUE}',
							'type' => (string) ZBX_SCRIPT_TYPE_URL,
							'execute_on' => (string) ZBX_SCRIPT_EXECUTE_ON_PROXY,
							'timeout' => '30s',
							'scope' => (string) ZBX_SCRIPT_SCOPE_EVENT,
							'port' => '',
							'authtype' => (string) ITEM_AUTHTYPE_PASSWORD,
							'username' => '',
							'password' => '',
							'publickey' => '',
							'privatekey' => '',
							'menu_path' => '',
							'url' => 'http://zabbix/ui/tr_events.php?eventid={EVENT.ID}',
							'new_window' => (string) ZBX_SCRIPT_URL_NEW_WINDOW_YES
						]
					],
					'event_macros' => [
						'plain_rw_single_d' => [
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'plain_rw',
							'{HOST.HOST}' => 'api_test_host_plain_rw',
							'{HOST.NAME}' => 'API test host - plain, read-write',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - plain, read-write',
							'{HOST.NAME2}' => '*UNKNOWN*',
							'{EVENT.ID}' => 'plain_rw_single_d',
							'{EVENT.NAME}' => 'API test trigger - plain, single, read-write, disaster',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_DISASTER,
							'{EVENT.SEVERITY}' => 'Disaster',
							'{USER.FULLNAME}' => 'Zabbix Administrator (Admin)',
							'{USER.NAME}' => 'Zabbix',
							'{USER.SURNAME}' => 'Administrator',
							'{USER.USERNAME}' => 'Admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '*UNKNOWN*',
							'{INVENTORY.OS2}' => '*UNKNOWN*',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						],
						'plain_r_single_d' => [
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'plain_r',
							'{HOST.HOST}' => 'api_test_host_plain_r',
							'{HOST.NAME}' => 'API test host - plain, read',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - plain, read',
							'{HOST.NAME2}' => '*UNKNOWN*',
							'{EVENT.ID}' => 'plain_r_single_d',
							'{EVENT.NAME}' => 'API test trigger - plain, single, read, disaster',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_DISASTER,
							'{EVENT.SEVERITY}' => 'Disaster',
							'{USER.FULLNAME}' => 'Zabbix Administrator (Admin)',
							'{USER.NAME}' => 'Zabbix',
							'{USER.SURNAME}' => 'Administrator',
							'{USER.USERNAME}' => 'Admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '*UNKNOWN*',
							'{INVENTORY.OS2}' => '*UNKNOWN*',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						],
						'plain_d_single_d' => [
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'plain_d',
							'{HOST.HOST}' => 'api_test_host_plain_d',
							'{HOST.NAME}' => 'API test host - plain, deny',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - plain, deny',
							'{HOST.NAME2}' => '*UNKNOWN*',
							'{EVENT.ID}' => 'plain_d_single_d',
							'{EVENT.NAME}' => 'API test trigger - plain, single, deny, disaster',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_DISASTER,
							'{EVENT.SEVERITY}' => 'Disaster',
							'{USER.FULLNAME}' => 'Zabbix Administrator (Admin)',
							'{USER.NAME}' => 'Zabbix',
							'{USER.SURNAME}' => 'Administrator',
							'{USER.USERNAME}' => 'Admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '*UNKNOWN*',
							'{INVENTORY.OS2}' => '*UNKNOWN*',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						],
						'plain_rw_r_dual_d' => [
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'plain_rw',
							'{HOST.HOST}' => 'api_test_host_plain_rw',
							'{HOST.NAME}' => 'API test host - plain, read-write',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - plain, read-write',
							'{HOST.NAME2}' => 'API test host - plain, read',
							'{EVENT.ID}' => 'plain_rw_r_dual_d',
							'{EVENT.NAME}' => 'API test trigger - plain, dual, read-write & read, disaster',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_DISASTER,
							'{EVENT.SEVERITY}' => 'Disaster',
							'{USER.FULLNAME}' => 'Zabbix Administrator (Admin)',
							'{USER.NAME}' => 'Zabbix',
							'{USER.SURNAME}' => 'Administrator',
							'{USER.USERNAME}' => 'Admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '*UNKNOWN*',
							'{INVENTORY.OS2}' => '*UNKNOWN*',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						],
						'macros_rw_single_1_h' => [
							'{$HOST_MACRO}' => 'host macro value - 1',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'macros_rw_1',
							'{HOST.HOST}' => 'api_test_host_macros_rw_1',
							'{HOST.NAME}' => 'API test host - macros 1, read-write',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - macros 1, read-write',
							'{HOST.NAME2}' => '*UNKNOWN*',
							'{EVENT.ID}' => 'macros_rw_single_1_h',
							'{EVENT.NAME}' => 'API test trigger - macros, single, read-write, high',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_HIGH,
							'{EVENT.SEVERITY}' => 'High',
							'{USER.FULLNAME}' => 'Zabbix Administrator (Admin)',
							'{USER.NAME}' => 'Zabbix',
							'{USER.SURNAME}' => 'Administrator',
							'{USER.USERNAME}' => 'Admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '*UNKNOWN*',
							'{INVENTORY.OS2}' => '*UNKNOWN*',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						],
						'macros_rw_r_dual_1_2_h' => [
							'{$HOST_MACRO}' => 'host macro value - 1',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'macros_rw_1',
							'{HOST.HOST}' => 'api_test_host_macros_rw_1',
							'{HOST.NAME}' => 'API test host - macros 1, read-write',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - macros 1, read-write',
							'{HOST.NAME2}' => 'API test host - macros 2, read',
							'{EVENT.ID}' => 'macros_rw_r_dual_1_2_h',
							'{EVENT.NAME}' => 'API test trigger - macros, dual, read-write & read, (1 & 2), high',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_HIGH,
							'{EVENT.SEVERITY}' => 'High',
							'{USER.FULLNAME}' => 'Zabbix Administrator (Admin)',
							'{USER.NAME}' => 'Zabbix',
							'{USER.SURNAME}' => 'Administrator',
							'{USER.USERNAME}' => 'Admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '*UNKNOWN*',
							'{INVENTORY.OS2}' => '*UNKNOWN*',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						],
						'macros_rw_dual_1_3_h' => [
							'{$HOST_MACRO}' => 'host macro value - 1',
							'{$HOST_MACRO_OTHER}' => 'host macro other value',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'macros_rw_1',
							'{HOST.HOST}' => 'api_test_host_macros_rw_1',
							'{HOST.NAME}' => 'API test host - macros 1, read-write',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - macros 1, read-write',
							'{HOST.NAME2}' => 'API test host - macros 3, read-write',
							'{EVENT.ID}' => 'macros_rw_dual_1_3_h',
							'{EVENT.NAME}' => 'API test trigger - macros, dual, read-write, (1 & 3), high',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_HIGH,
							'{EVENT.SEVERITY}' => 'High',
							'{USER.FULLNAME}' => 'Zabbix Administrator (Admin)',
							'{USER.NAME}' => 'Zabbix',
							'{USER.SURNAME}' => 'Administrator',
							'{USER.USERNAME}' => 'Admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '*UNKNOWN*',
							'{INVENTORY.OS2}' => '*UNKNOWN*',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						],
						'interface_rw_dual_a' => [
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'interface_rw_1',
							'{HOST.HOST}' => 'api_test_host_interface_rw_1',
							'{HOST.NAME}' => 'API test host - interface (read-write) 1',
							'{HOST.CONN}' => '1.1.1.1',
							'{HOST.IP}' => '1.1.1.1',
							'{HOST.DNS}' => '',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - interface (read-write) 1',
							'{HOST.NAME2}' => 'API test host - interface (read-write) 2',
							'{EVENT.ID}' => 'interface_rw_dual_a',
							'{EVENT.NAME}' => 'API test trigger - interface, dual, average',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_AVERAGE,
							'{EVENT.SEVERITY}' => 'Average',
							'{USER.FULLNAME}' => 'Zabbix Administrator (Admin)',
							'{USER.NAME}' => 'Zabbix',
							'{USER.SURNAME}' => 'Administrator',
							'{USER.USERNAME}' => 'Admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '*UNKNOWN*',
							'{INVENTORY.OS2}' => '*UNKNOWN*',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						],
						'inventory_rw_dual_a' => [
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'inventory_rw_1',
							'{HOST.HOST}' => 'api_test_host_inventory_rw_1',
							'{HOST.NAME}' => 'API test host - inventory (read-write) 1',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - inventory (read-write) 1',
							'{HOST.NAME2}' => 'API test host - inventory (read-write) 2',
							'{EVENT.ID}' => 'inventory_rw_dual_a',
							'{EVENT.NAME}' => 'API test trigger - inventory, dual, average',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_AVERAGE,
							'{EVENT.SEVERITY}' => 'Average',
							'{USER.FULLNAME}' => 'Zabbix Administrator (Admin)',
							'{USER.NAME}' => 'Zabbix',
							'{USER.SURNAME}' => 'Administrator',
							'{USER.USERNAME}' => 'Admin',
							'{INVENTORY.ALIAS}' => '',
							'{INVENTORY.OS}' => 'Windows',
							'{INVENTORY.TYPE}' => '',
							'{INVENTORY.CONTACT}' => '',
							'{INVENTORY.OS1}' => 'Windows',
							'{INVENTORY.OS2}' => '',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						],
						'macros_d_cause' => [
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'cause_d',
							'{HOST.HOST}' => 'api_test_host_cause_d',
							'{HOST.NAME}' => 'API test host - cause, deny',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - cause, deny',
							'{HOST.NAME2}' => '*UNKNOWN*',
							'{EVENT.ID}' => 'macros_d_cause',
							'{EVENT.NAME}' => 'API test trigger - macros, cause, disaster',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_DISASTER,
							'{EVENT.SEVERITY}' => 'Disaster',
							'{USER.FULLNAME}' => 'Zabbix Administrator (Admin)',
							'{USER.NAME}' => 'Zabbix',
							'{USER.SURNAME}' => 'Administrator',
							'{USER.USERNAME}' => 'Admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '*UNKNOWN*',
							'{INVENTORY.OS2}' => '*UNKNOWN*',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						],
						'macros_rw_symptom' => [
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'symptom_rw',
							'{HOST.HOST}' => 'api_test_host_symptom_rw',
							'{HOST.NAME}' => 'API test host - symptom, read-write',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - symptom, read-write',
							'{HOST.NAME2}' => '*UNKNOWN*',
							'{EVENT.ID}' => 'macros_rw_symptom',
							'{EVENT.NAME}' => 'API test trigger - macros, symptom, high',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_HIGH,
							'{EVENT.SEVERITY}' => 'High',
							'{USER.FULLNAME}' => 'Zabbix Administrator (Admin)',
							'{USER.NAME}' => 'Zabbix',
							'{USER.SURNAME}' => 'Administrator',
							'{USER.USERNAME}' => 'Admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '*UNKNOWN*',
							'{INVENTORY.OS2}' => '*UNKNOWN*',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => 'macros_d_cause',
							'{EVENT.CAUSE.NAME}' => 'API test trigger - macros, cause, disaster',
							'{EVENT.CAUSE.NSEVERITY}' => (string) TRIGGER_SEVERITY_DISASTER,
							'{EVENT.CAUSE.SEVERITY}' => 'Disaster',
							'{EVENT.CAUSE.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.CAUSE.VALUE}' => (string) TRIGGER_VALUE_TRUE
						]
					]
				],
				'expected_error' => null
			],
			'Test script.getScriptsByEvents with admin' => [
				'request' => [
					'login' => ['user' => 'api_test_admin', 'password' => '4P1T3$tEr'],
					'eventids' => [
						'plain_rw_single_d', 'plain_r_single_d', 'plain_d_single_d', 'plain_rw_r_dual_d',
						'macros_rw_single_1_h', 'macros_rw_r_dual_1_2_h', 'macros_rw_dual_1_3_h', 'interface_rw_dual_a',
						'inventory_rw_dual_a', 'macros_d_cause', 'macros_rw_symptom'
					]
				],
				'expected_result' => [
					'has.eventid:scriptid' => [
						// Regular admin does not have all scripts available.
						'plain_rw_single_d' => ['get_events_url', 'get_events_ipmi', 'get_events_ssh',
							'get_events_url_cause'
						],
						'plain_d_single_d' => [],
						'plain_r_single_d' => ['get_events_url', 'get_events_ipmi', 'get_events_url_cause'],
						'plain_rw_r_dual_d' => ['get_events_url', 'get_events_ipmi', 'get_events_ssh',
							'get_events_url_cause'
						],
						'macros_rw_single_1_h' => ['get_events_url', 'get_events_ipmi', 'get_events_ssh',
							'get_events_url_cause'
						],
						'macros_rw_r_dual_1_2_h' => ['get_events_url', 'get_events_ipmi', 'get_events_ssh',
							'get_events_url_cause'
						],
						'macros_rw_dual_1_3_h' => ['get_events_url', 'get_events_ipmi', 'get_events_ssh',
							'get_events_url_cause'
						],
						'interface_rw_dual_a' => ['get_events_url', 'get_events_ipmi', 'get_events_ssh',
							'get_events_url_cause'
						],
						'inventory_rw_dual_a' => ['get_events_url', 'get_events_ipmi', 'get_events_ssh',
							'get_events_url_cause'
						],
						'macros_d_cause' => [],
						'macros_rw_symptom' => ['get_events_url', 'get_events_ipmi', 'get_events_ssh',
							'get_events_url_cause'
						]
					],
					'!has.eventid:scriptid' => [
						'plain_rw_single_d' => ['get_events_webhook'],
						'plain_d_single_d' => ['get_events_url', 'get_events_ipmi', 'get_events_webhook',
							'get_events_ssh', 'get_events_url_cause'
						],
						'plain_r_single_d' => ['get_events_webhook', 'get_events_ssh'],
						'plain_rw_r_dual_d' => ['get_events_webhook'],
						'macros_rw_single_1_h' => ['get_events_webhook'],
						'macros_rw_r_dual_1_2_h' => ['get_events_webhook'],
						'macros_rw_dual_1_3_h' => ['get_events_webhook'],
						'interface_rw_dual_a' => ['get_events_webhook'],
						'inventory_rw_dual_a' => ['get_events_webhook'],
						'macros_d_cause' => ['get_events_url', 'get_events_ipmi', 'get_events_webhook',
							'get_events_ssh', 'get_events_url_cause'
						],
						'macros_rw_symptom' => ['get_events_webhook']
					],
					'scripts' => [
						[
							'scriptid' => 'get_events_url',
							'name' => 'API test script.getScriptsByEvents - URL',
							'command' => '',
							'host_access' => (string) PERM_READ,
							'usrgrpid' => '0',
							'groupid' => '0',
							'description' => '',
							'confirmation' => 'Confirmation macros: {$HOST_MACRO}, {$HOST_MACRO_OTHER},'.
								' {$GLOBAL_MACRO}, {$DOESNOTEXIST}, {HOST.ID}, {HOST.HOST}, {HOST.NAME}, {HOST.CONN},'.
								' {HOST.DNS}, {HOST.PORT}, {HOST.NAME1}, {HOST.NAME2}, {EVENT.ID}, {EVENT.NAME},'.
								' {EVENT.NSEVERITY}, {EVENT.SEVERITY}',
							'type' => (string) ZBX_SCRIPT_TYPE_URL,
							'execute_on' => (string) ZBX_SCRIPT_EXECUTE_ON_PROXY,
							'timeout' => '30s',
							'scope' => (string) ZBX_SCRIPT_SCOPE_EVENT,
							'port' => '',
							'authtype' => (string) ITEM_AUTHTYPE_PASSWORD,
							'username' => '',
							'password' => '',
							'publickey' => '',
							'privatekey' => '',
							'menu_path' => '',
							'url' => 'http://zabbix/ui/zabbix.php?action=host.edit&hostid={HOST.ID}',
							'new_window' => (string) ZBX_SCRIPT_URL_NEW_WINDOW_YES
						],
						[
							'scriptid' => 'get_events_ipmi',
							'name' => 'API test script.getScriptsByEvents - IPMI',
							'command' => 'reboot server',
							'host_access' => (string) PERM_READ,
							'usrgrpid' => 'admin',
							'groupid' => '0',
							'description' => '',
							'confirmation' => 'Confirmation macros: {USER.FULLNAME}, {USER.NAME}, {USER.SURNAME},'.
								' {USER.USERNAME}, {INVENTORY.ALIAS}, {INVENTORY.OS}, {INVENTORY.TYPE},'.
								' {INVENTORY.CONTACT}, {INVENTORY.OS1}, {INVENTORY.OS2}, {EVENT.STATUS},'.
								' {EVENT.VALUE}, {HOSTGROUP.ID}',
							'type' => (string) ZBX_SCRIPT_TYPE_IPMI,
							'execute_on' => (string) ZBX_SCRIPT_EXECUTE_ON_PROXY,
							'timeout' => '30s',
							'scope' => (string) ZBX_SCRIPT_SCOPE_EVENT,
							'port' => '',
							'authtype' => (string) ITEM_AUTHTYPE_PASSWORD,
							'username' => '',
							'password' => '',
							'publickey' => '',
							'privatekey' => '',
							'menu_path' => '',
							'url' => '',
							'new_window' => (string) ZBX_SCRIPT_URL_NEW_WINDOW_YES
						],
						[
							'scriptid' => 'get_events_ssh',
							'name' => 'API test script.getScriptsByEvents - SSH password',
							'command' => 'reboot server',
							'host_access' => (string) PERM_READ_WRITE,
							'usrgrpid' => '0',
							'groupid' => '0',
							'description' => '',
							'confirmation' => 'Confirmation macros: {$GLOBAL_MACRO}, {HOST.HOST}, {USER.FULLNAME},'.
								' {HOST.CONN}, {HOST.IP}, {HOST.DNS}, {HOST.PORT}, {INVENTORY.ALIAS}, {INVENTORY.OS},'.
								' {INVENTORY.TYPE}',
							'type' => (string) ZBX_SCRIPT_TYPE_SSH,
							'execute_on' => (string) ZBX_SCRIPT_EXECUTE_ON_PROXY,
							'timeout' => '30s',
							'scope' => (string) ZBX_SCRIPT_SCOPE_EVENT,
							'port' => '',
							'authtype' => (string) ITEM_AUTHTYPE_PASSWORD,
							'username' => 'user',
							'password' => '',
							'publickey' => '',
							'privatekey' => '',
							'menu_path' => '',
							'url' => '',
							'new_window' => (string) ZBX_SCRIPT_URL_NEW_WINDOW_YES
						],
						[
							'scriptid' => 'get_events_url_cause',
							'name' => 'API test script.getScriptsByEvents - URL cause',
							'command' => '',
							'host_access' => (string) PERM_READ,
							'usrgrpid' => '0',
							'groupid' => '0',
							'description' => '',
							'confirmation' => 'Confirmation macros: {EVENT.CAUSE.ID}, {EVENT.CAUSE.NAME},'.
								' {EVENT.CAUSE.NSEVERITY}, {EVENT.CAUSE.SEVERITY}, {EVENT.CAUSE.STATUS},'.
								' {EVENT.CAUSE.VALUE}',
							'type' => (string) ZBX_SCRIPT_TYPE_URL,
							'execute_on' => (string) ZBX_SCRIPT_EXECUTE_ON_PROXY,
							'timeout' => '30s',
							'scope' => (string) ZBX_SCRIPT_SCOPE_EVENT,
							'port' => '',
							'authtype' => (string) ITEM_AUTHTYPE_PASSWORD,
							'username' => '',
							'password' => '',
							'publickey' => '',
							'privatekey' => '',
							'menu_path' => '',
							'url' => 'http://zabbix/ui/tr_events.php?eventid={EVENT.ID}',
							'new_window' => (string) ZBX_SCRIPT_URL_NEW_WINDOW_YES
						]
					],
					'event_macros' => [
						'plain_rw_single_d' => [
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'plain_rw',
							'{HOST.HOST}' => 'api_test_host_plain_rw',
							'{HOST.NAME}' => 'API test host - plain, read-write',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - plain, read-write',
							'{HOST.NAME2}' => '*UNKNOWN*',
							'{EVENT.ID}' => 'plain_rw_single_d',
							'{EVENT.NAME}' => 'API test trigger - plain, single, read-write, disaster',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_DISASTER,
							'{EVENT.SEVERITY}' => 'Disaster',
							'{USER.FULLNAME}' => 'API One Tester One (api_test_admin)',
							'{USER.NAME}' => 'API One',
							'{USER.SURNAME}' => 'Tester One',
							'{USER.USERNAME}' => 'api_test_admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '*UNKNOWN*',
							'{INVENTORY.OS2}' => '*UNKNOWN*',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						],
						'plain_r_single_d' => [
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'plain_r',
							'{HOST.HOST}' => 'api_test_host_plain_r',
							'{HOST.NAME}' => 'API test host - plain, read',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - plain, read',
							'{HOST.NAME2}' => '*UNKNOWN*',
							'{EVENT.ID}' => 'plain_r_single_d',
							'{EVENT.NAME}' => 'API test trigger - plain, single, read, disaster',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_DISASTER,
							'{EVENT.SEVERITY}' => 'Disaster',
							'{USER.FULLNAME}' => 'API One Tester One (api_test_admin)',
							'{USER.NAME}' => 'API One',
							'{USER.SURNAME}' => 'Tester One',
							'{USER.USERNAME}' => 'api_test_admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '*UNKNOWN*',
							'{INVENTORY.OS2}' => '*UNKNOWN*',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						],
						'plain_rw_r_dual_d' => [
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'plain_rw',
							'{HOST.HOST}' => 'api_test_host_plain_rw',
							'{HOST.NAME}' => 'API test host - plain, read-write',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - plain, read-write',
							'{HOST.NAME2}' => 'API test host - plain, read',
							'{EVENT.ID}' => 'plain_rw_r_dual_d',
							'{EVENT.NAME}' => 'API test trigger - plain, dual, read-write & read, disaster',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_DISASTER,
							'{EVENT.SEVERITY}' => 'Disaster',
							'{USER.FULLNAME}' => 'API One Tester One (api_test_admin)',
							'{USER.NAME}' => 'API One',
							'{USER.SURNAME}' => 'Tester One',
							'{USER.USERNAME}' => 'api_test_admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '*UNKNOWN*',
							'{INVENTORY.OS2}' => '*UNKNOWN*',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						],
						'macros_rw_single_1_h' => [
							'{$HOST_MACRO}' => 'host macro value - 1',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'macros_rw_1',
							'{HOST.HOST}' => 'api_test_host_macros_rw_1',
							'{HOST.NAME}' => 'API test host - macros 1, read-write',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - macros 1, read-write',
							'{HOST.NAME2}' => '*UNKNOWN*',
							'{EVENT.ID}' => 'macros_rw_single_1_h',
							'{EVENT.NAME}' => 'API test trigger - macros, single, read-write, high',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_HIGH,
							'{EVENT.SEVERITY}' => 'High',
							'{USER.FULLNAME}' => 'API One Tester One (api_test_admin)',
							'{USER.NAME}' => 'API One',
							'{USER.SURNAME}' => 'Tester One',
							'{USER.USERNAME}' => 'api_test_admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '*UNKNOWN*',
							'{INVENTORY.OS2}' => '*UNKNOWN*',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						],
						'macros_rw_r_dual_1_2_h' => [
							'{$HOST_MACRO}' => 'host macro value - 1',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'macros_rw_1',
							'{HOST.HOST}' => 'api_test_host_macros_rw_1',
							'{HOST.NAME}' => 'API test host - macros 1, read-write',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - macros 1, read-write',
							'{HOST.NAME2}' => 'API test host - macros 2, read',
							'{EVENT.ID}' => 'macros_rw_r_dual_1_2_h',
							'{EVENT.NAME}' => 'API test trigger - macros, dual, read-write & read, (1 & 2), high',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_HIGH,
							'{EVENT.SEVERITY}' => 'High',
							'{USER.FULLNAME}' => 'API One Tester One (api_test_admin)',
							'{USER.NAME}' => 'API One',
							'{USER.SURNAME}' => 'Tester One',
							'{USER.USERNAME}' => 'api_test_admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '*UNKNOWN*',
							'{INVENTORY.OS2}' => '*UNKNOWN*',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						],
						'macros_rw_dual_1_3_h' => [
							'{$HOST_MACRO}' => 'host macro value - 1',
							'{$HOST_MACRO_OTHER}' => 'host macro other value',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'macros_rw_1',
							'{HOST.HOST}' => 'api_test_host_macros_rw_1',
							'{HOST.NAME}' => 'API test host - macros 1, read-write',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - macros 1, read-write',
							'{HOST.NAME2}' => 'API test host - macros 3, read-write',
							'{EVENT.ID}' => 'macros_rw_dual_1_3_h',
							'{EVENT.NAME}' => 'API test trigger - macros, dual, read-write, (1 & 3), high',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_HIGH,
							'{EVENT.SEVERITY}' => 'High',
							'{USER.FULLNAME}' => 'API One Tester One (api_test_admin)',
							'{USER.NAME}' => 'API One',
							'{USER.SURNAME}' => 'Tester One',
							'{USER.USERNAME}' => 'api_test_admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '*UNKNOWN*',
							'{INVENTORY.OS2}' => '*UNKNOWN*',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						],
						'interface_rw_dual_a' => [
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'interface_rw_1',
							'{HOST.HOST}' => 'api_test_host_interface_rw_1',
							'{HOST.NAME}' => 'API test host - interface (read-write) 1',
							'{HOST.CONN}' => '1.1.1.1',
							'{HOST.IP}' => '1.1.1.1',
							'{HOST.DNS}' => '',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - interface (read-write) 1',
							'{HOST.NAME2}' => 'API test host - interface (read-write) 2',
							'{EVENT.ID}' => 'interface_rw_dual_a',
							'{EVENT.NAME}' => 'API test trigger - interface, dual, average',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_AVERAGE,
							'{EVENT.SEVERITY}' => 'Average',
							'{USER.FULLNAME}' => 'API One Tester One (api_test_admin)',
							'{USER.NAME}' => 'API One',
							'{USER.SURNAME}' => 'Tester One',
							'{USER.USERNAME}' => 'api_test_admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '*UNKNOWN*',
							'{INVENTORY.OS2}' => '*UNKNOWN*',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						],
						'inventory_rw_dual_a' => [
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'inventory_rw_1',
							'{HOST.HOST}' => 'api_test_host_inventory_rw_1',
							'{HOST.NAME}' => 'API test host - inventory (read-write) 1',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - inventory (read-write) 1',
							'{HOST.NAME2}' => 'API test host - inventory (read-write) 2',
							'{EVENT.ID}' => 'inventory_rw_dual_a',
							'{EVENT.NAME}' => 'API test trigger - inventory, dual, average',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_AVERAGE,
							'{EVENT.SEVERITY}' => 'Average',
							'{USER.FULLNAME}' => 'API One Tester One (api_test_admin)',
							'{USER.NAME}' => 'API One',
							'{USER.SURNAME}' => 'Tester One',
							'{USER.USERNAME}' => 'api_test_admin',
							'{INVENTORY.ALIAS}' => '',
							'{INVENTORY.OS}' => 'Windows',
							'{INVENTORY.TYPE}' => '',
							'{INVENTORY.CONTACT}' => '',
							'{INVENTORY.OS1}' => 'Windows',
							'{INVENTORY.OS2}' => '',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						],
						// Cause event is restricted, so macros resolve to empty string.
						'macros_rw_symptom' => [
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'symptom_rw',
							'{HOST.HOST}' => 'api_test_host_symptom_rw',
							'{HOST.NAME}' => 'API test host - symptom, read-write',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - symptom, read-write',
							'{HOST.NAME2}' => '*UNKNOWN*',
							'{EVENT.ID}' => 'macros_rw_symptom',
							'{EVENT.NAME}' => 'API test trigger - macros, symptom, high',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_HIGH,
							'{EVENT.SEVERITY}' => 'High',
							'{USER.FULLNAME}' => 'API One Tester One (api_test_admin)',
							'{USER.NAME}' => 'API One',
							'{USER.SURNAME}' => 'Tester One',
							'{USER.USERNAME}' => 'api_test_admin',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '*UNKNOWN*',
							'{INVENTORY.OS2}' => '*UNKNOWN*',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => 'macros_d_cause',
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						]
					]
				],
				'expected_error' => null
			],
			'Test script.getScriptsByEvents with user' => [
				'request' => [
					'login' => ['user' => 'api_test_user', 'password' => '4P1T3$tEr'],
					'eventids' => [
						'plain_rw_single_d', 'plain_r_single_d', 'plain_d_single_d', 'plain_rw_r_dual_d',
						'macros_rw_single_1_h', 'macros_rw_r_dual_1_2_h', 'macros_rw_dual_1_3_h', 'interface_rw_dual_a',
						'inventory_rw_dual_a',  'macros_d_cause', 'macros_rw_symptom'
					]
				],
				'expected_result' => [
					'has.eventid:scriptid' => [
						// Regular user does not have all scripts available.
						'plain_rw_single_d' => ['get_events_url', 'get_events_webhook', 'get_events_ssh',
							'get_events_url_cause'
						],
						'plain_d_single_d' => [],
						'plain_r_single_d' => ['get_events_url', 'get_events_webhook', 'get_events_url_cause'],
						'plain_rw_r_dual_d' => ['get_events_url', 'get_events_webhook', 'get_events_ssh',
							'get_events_url_cause'
						],
						'macros_rw_single_1_h' => ['get_events_url', 'get_events_webhook', 'get_events_ssh',
							'get_events_url_cause'
						],
						'macros_rw_r_dual_1_2_h' => ['get_events_url', 'get_events_webhook', 'get_events_ssh',
							'get_events_url_cause'
						],
						'macros_rw_dual_1_3_h' => ['get_events_url', 'get_events_webhook', 'get_events_ssh',
							'get_events_url_cause'
						],
						'interface_rw_dual_a' => ['get_events_url', 'get_events_webhook', 'get_events_ssh',
							'get_events_url_cause'
						],
						'inventory_rw_dual_a' => ['get_events_url', 'get_events_webhook', 'get_events_ssh',
							'get_events_url_cause'
						],
						'macros_d_cause' => [],
						'macros_rw_symptom' => ['get_events_url', 'get_events_webhook', 'get_events_ssh',
							'get_events_url_cause'
						]
					],
					'!has.eventid:scriptid' => [
						'plain_rw_single_d' => ['get_events_ipmi'],
						'plain_d_single_d' => ['get_events_url', 'get_events_ipmi', 'get_events_webhook',
							'get_events_ssh', 'get_events_url_cause'
						],
						'plain_r_single_d' => ['get_events_ipmi', 'get_events_ssh'],
						'plain_rw_r_dual_d' => ['get_events_ipmi'],
						'macros_rw_single_1_h' => ['get_events_ipmi'],
						'macros_rw_r_dual_1_2_h' => ['get_events_ipmi'],
						'macros_rw_dual_1_3_h' => ['get_events_ipmi'],
						'interface_rw_dual_a' => ['get_events_ipmi'],
						'inventory_rw_dual_a' => ['get_events_ipmi'],
						'macros_d_cause' => ['get_events_url', 'get_events_ipmi', 'get_events_webhook',
							'get_events_ssh', 'get_events_url_cause'
						],
						'macros_rw_symptom' => ['get_events_ipmi']
					],
					'scripts' => [
						[
							'scriptid' => 'get_events_url',
							'name' => 'API test script.getScriptsByEvents - URL',
							'command' => '',
							'host_access' => (string) PERM_READ,
							'usrgrpid' => '0',
							'groupid' => '0',
							'description' => '',
							'confirmation' => 'Confirmation macros: {$HOST_MACRO}, {$HOST_MACRO_OTHER},'.
								' {$GLOBAL_MACRO}, {$DOESNOTEXIST}, {HOST.ID}, {HOST.HOST}, {HOST.NAME}, {HOST.CONN},'.
								' {HOST.DNS}, {HOST.PORT}, {HOST.NAME1}, {HOST.NAME2}, {EVENT.ID}, {EVENT.NAME},'.
								' {EVENT.NSEVERITY}, {EVENT.SEVERITY}',
							'type' => (string) ZBX_SCRIPT_TYPE_URL,
							'execute_on' => (string) ZBX_SCRIPT_EXECUTE_ON_PROXY,
							'timeout' => '30s',
							'scope' => (string) ZBX_SCRIPT_SCOPE_EVENT,
							'port' => '',
							'authtype' => (string) ITEM_AUTHTYPE_PASSWORD,
							'username' => '',
							'password' => '',
							'publickey' => '',
							'privatekey' => '',
							'menu_path' => '',
							'url' => 'http://zabbix/ui/zabbix.php?action=host.edit&hostid={HOST.ID}',
							'new_window' => (string) ZBX_SCRIPT_URL_NEW_WINDOW_YES
						],
						[
							'scriptid' => 'get_events_webhook',
							'name' => 'API test script.getScriptsByEvents - Webhook',
							'command' => 'reboot server',
							'host_access' => (string) PERM_READ,
							'usrgrpid' => 'user',
							'groupid' => '0',
							'description' => '',
							'confirmation' => 'Confirmation macros: {$GLOBAL_MACRO}, {HOST.HOST}, {USER.FULLNAME},'.
								' {HOST.CONN}, {HOST.IP}, {HOST.DNS}, {HOST.PORT}',
							'type' => (string) ZBX_SCRIPT_TYPE_WEBHOOK,
							'execute_on' => (string) ZBX_SCRIPT_EXECUTE_ON_PROXY,
							'timeout' => '30s',
							'scope' => (string) ZBX_SCRIPT_SCOPE_EVENT,
							'port' => '',
							'authtype' => (string) ITEM_AUTHTYPE_PASSWORD,
							'username' => '',
							'password' => '',
							'publickey' => '',
							'privatekey' => '',
							'menu_path' => '',
							'url' => '',
							'new_window' => (string) ZBX_SCRIPT_URL_NEW_WINDOW_YES
						],
						[
							'scriptid' => 'get_events_ssh',
							'name' => 'API test script.getScriptsByEvents - SSH password',
							'command' => 'reboot server',
							'host_access' => (string) PERM_READ_WRITE,
							'usrgrpid' => '0',
							'groupid' => '0',
							'description' => '',
							'confirmation' => 'Confirmation macros: {$GLOBAL_MACRO}, {HOST.HOST}, {USER.FULLNAME},'.
								' {HOST.CONN}, {HOST.IP}, {HOST.DNS}, {HOST.PORT}, {INVENTORY.ALIAS}, {INVENTORY.OS},'.
								' {INVENTORY.TYPE}',
							'type' => (string) ZBX_SCRIPT_TYPE_SSH,
							'execute_on' => (string) ZBX_SCRIPT_EXECUTE_ON_PROXY,
							'timeout' => '30s',
							'scope' => (string) ZBX_SCRIPT_SCOPE_EVENT,
							'port' => '',
							'authtype' => (string) ITEM_AUTHTYPE_PASSWORD,
							'username' => 'user',
							'password' => '',
							'publickey' => '',
							'privatekey' => '',
							'menu_path' => '',
							'url' => '',
							'new_window' => (string) ZBX_SCRIPT_URL_NEW_WINDOW_YES
						],
						[
							'scriptid' => 'get_events_url_cause',
							'name' => 'API test script.getScriptsByEvents - URL cause',
							'command' => '',
							'host_access' => (string) PERM_READ,
							'usrgrpid' => '0',
							'groupid' => '0',
							'description' => '',
							'confirmation' => 'Confirmation macros: {EVENT.CAUSE.ID}, {EVENT.CAUSE.NAME},'.
								' {EVENT.CAUSE.NSEVERITY}, {EVENT.CAUSE.SEVERITY}, {EVENT.CAUSE.STATUS},'.
								' {EVENT.CAUSE.VALUE}',
							'type' => (string) ZBX_SCRIPT_TYPE_URL,
							'execute_on' => (string) ZBX_SCRIPT_EXECUTE_ON_PROXY,
							'timeout' => '30s',
							'scope' => (string) ZBX_SCRIPT_SCOPE_EVENT,
							'port' => '',
							'authtype' => (string) ITEM_AUTHTYPE_PASSWORD,
							'username' => '',
							'password' => '',
							'publickey' => '',
							'privatekey' => '',
							'menu_path' => '',
							'url' => 'http://zabbix/ui/tr_events.php?eventid={EVENT.ID}',
							'new_window' => (string) ZBX_SCRIPT_URL_NEW_WINDOW_YES
						]
					],
					// CSeverityHelper cannot be used here. Use untranslated plain text.
					'event_macros' => [
						'plain_rw_single_d' => [
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'plain_rw',
							'{HOST.HOST}' => 'api_test_host_plain_rw',
							'{HOST.NAME}' => 'API test host - plain, read-write',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - plain, read-write',
							'{HOST.NAME2}' => '*UNKNOWN*',
							'{EVENT.ID}' => 'plain_rw_single_d',
							'{EVENT.NAME}' => 'API test trigger - plain, single, read-write, disaster',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_DISASTER,
							'{EVENT.SEVERITY}' => 'Disaster',
							'{USER.FULLNAME}' => 'API Two Tester Two (api_test_user)',
							'{USER.NAME}' => 'API Two',
							'{USER.SURNAME}' => 'Tester Two',
							'{USER.USERNAME}' => 'api_test_user',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '*UNKNOWN*',
							'{INVENTORY.OS2}' => '*UNKNOWN*',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						],
						'plain_r_single_d' => [
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'plain_r',
							'{HOST.HOST}' => 'api_test_host_plain_r',
							'{HOST.NAME}' => 'API test host - plain, read',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - plain, read',
							'{HOST.NAME2}' => '*UNKNOWN*',
							'{EVENT.ID}' => 'plain_r_single_d',
							'{EVENT.NAME}' => 'API test trigger - plain, single, read, disaster',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_DISASTER,
							'{EVENT.SEVERITY}' => 'Disaster',
							'{USER.FULLNAME}' => 'API Two Tester Two (api_test_user)',
							'{USER.NAME}' => 'API Two',
							'{USER.SURNAME}' => 'Tester Two',
							'{USER.USERNAME}' => 'api_test_user',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '*UNKNOWN*',
							'{INVENTORY.OS2}' => '*UNKNOWN*',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						],
						'plain_rw_r_dual_d' => [
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'plain_rw',
							'{HOST.HOST}' => 'api_test_host_plain_rw',
							'{HOST.NAME}' => 'API test host - plain, read-write',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - plain, read-write',
							'{HOST.NAME2}' => 'API test host - plain, read',
							'{EVENT.ID}' => 'plain_rw_r_dual_d',
							'{EVENT.NAME}' => 'API test trigger - plain, dual, read-write & read, disaster',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_DISASTER,
							'{EVENT.SEVERITY}' => 'Disaster',
							'{USER.FULLNAME}' => 'API Two Tester Two (api_test_user)',
							'{USER.NAME}' => 'API Two',
							'{USER.SURNAME}' => 'Tester Two',
							'{USER.USERNAME}' => 'api_test_user',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '*UNKNOWN*',
							'{INVENTORY.OS2}' => '*UNKNOWN*',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						],
						'macros_rw_single_1_h' => [
							'{$HOST_MACRO}' => 'host macro value - 1',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'macros_rw_1',
							'{HOST.HOST}' => 'api_test_host_macros_rw_1',
							'{HOST.NAME}' => 'API test host - macros 1, read-write',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - macros 1, read-write',
							'{HOST.NAME2}' => '*UNKNOWN*',
							'{EVENT.ID}' => 'macros_rw_single_1_h',
							'{EVENT.NAME}' => 'API test trigger - macros, single, read-write, high',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_HIGH,
							'{EVENT.SEVERITY}' => 'High',
							'{USER.FULLNAME}' => 'API Two Tester Two (api_test_user)',
							'{USER.NAME}' => 'API Two',
							'{USER.SURNAME}' => 'Tester Two',
							'{USER.USERNAME}' => 'api_test_user',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '*UNKNOWN*',
							'{INVENTORY.OS2}' => '*UNKNOWN*',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						],
						'macros_rw_r_dual_1_2_h' => [
							'{$HOST_MACRO}' => 'host macro value - 1',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'macros_rw_1',
							'{HOST.HOST}' => 'api_test_host_macros_rw_1',
							'{HOST.NAME}' => 'API test host - macros 1, read-write',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - macros 1, read-write',
							'{HOST.NAME2}' => 'API test host - macros 2, read',
							'{EVENT.ID}' => 'macros_rw_r_dual_1_2_h',
							'{EVENT.NAME}' => 'API test trigger - macros, dual, read-write & read, (1 & 2), high',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_HIGH,
							'{EVENT.SEVERITY}' => 'High',
							'{USER.FULLNAME}' => 'API Two Tester Two (api_test_user)',
							'{USER.NAME}' => 'API Two',
							'{USER.SURNAME}' => 'Tester Two',
							'{USER.USERNAME}' => 'api_test_user',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '*UNKNOWN*',
							'{INVENTORY.OS2}' => '*UNKNOWN*',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						],
						'macros_rw_dual_1_3_h' => [
							'{$HOST_MACRO}' => 'host macro value - 1',
							'{$HOST_MACRO_OTHER}' => 'host macro other value',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'macros_rw_1',
							'{HOST.HOST}' => 'api_test_host_macros_rw_1',
							'{HOST.NAME}' => 'API test host - macros 1, read-write',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - macros 1, read-write',
							'{HOST.NAME2}' => 'API test host - macros 3, read-write',
							'{EVENT.ID}' => 'macros_rw_dual_1_3_h',
							'{EVENT.NAME}' => 'API test trigger - macros, dual, read-write, (1 & 3), high',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_HIGH,
							'{EVENT.SEVERITY}' => 'High',
							'{USER.FULLNAME}' => 'API Two Tester Two (api_test_user)',
							'{USER.NAME}' => 'API Two',
							'{USER.SURNAME}' => 'Tester Two',
							'{USER.USERNAME}' => 'api_test_user',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '*UNKNOWN*',
							'{INVENTORY.OS2}' => '*UNKNOWN*',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						],
						'interface_rw_dual_a' => [
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'interface_rw_1',
							'{HOST.HOST}' => 'api_test_host_interface_rw_1',
							'{HOST.NAME}' => 'API test host - interface (read-write) 1',
							'{HOST.CONN}' => '1.1.1.1',
							'{HOST.IP}' => '1.1.1.1',
							'{HOST.DNS}' => '',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - interface (read-write) 1',
							'{HOST.NAME2}' => 'API test host - interface (read-write) 2',
							'{EVENT.ID}' => 'interface_rw_dual_a',
							'{EVENT.NAME}' => 'API test trigger - interface, dual, average',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_AVERAGE,
							'{EVENT.SEVERITY}' => 'Average',
							'{USER.FULLNAME}' => 'API Two Tester Two (api_test_user)',
							'{USER.NAME}' => 'API Two',
							'{USER.SURNAME}' => 'Tester Two',
							'{USER.USERNAME}' => 'api_test_user',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '*UNKNOWN*',
							'{INVENTORY.OS2}' => '*UNKNOWN*',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						],
						'inventory_rw_dual_a' => [
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'inventory_rw_1',
							'{HOST.HOST}' => 'api_test_host_inventory_rw_1',
							'{HOST.NAME}' => 'API test host - inventory (read-write) 1',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - inventory (read-write) 1',
							'{HOST.NAME2}' => 'API test host - inventory (read-write) 2',
							'{EVENT.ID}' => 'inventory_rw_dual_a',
							'{EVENT.NAME}' => 'API test trigger - inventory, dual, average',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_AVERAGE,
							'{EVENT.SEVERITY}' => 'Average',
							'{USER.FULLNAME}' => 'API Two Tester Two (api_test_user)',
							'{USER.NAME}' => 'API Two',
							'{USER.SURNAME}' => 'Tester Two',
							'{USER.USERNAME}' => 'api_test_user',
							'{INVENTORY.ALIAS}' => '',
							'{INVENTORY.OS}' => 'Windows',
							'{INVENTORY.TYPE}' => '',
							'{INVENTORY.CONTACT}' => '',
							'{INVENTORY.OS1}' => 'Windows',
							'{INVENTORY.OS2}' => '',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						],
						// Cause event is restricted, so macros resolve to empty string.
						'macros_rw_symptom' => [
							'{$HOST_MACRO}' => '{$HOST_MACRO}',
							'{$HOST_MACRO_OTHER}' => '{$HOST_MACRO_OTHER}',
							'{$GLOBAL_MACRO}' => 'Global Macro Value',
							'{$DOESNOTEXIST}' => '{$DOESNOTEXIST}',
							'{HOST.ID}' => 'symptom_rw',
							'{HOST.HOST}' => 'api_test_host_symptom_rw',
							'{HOST.NAME}' => 'API test host - symptom, read-write',
							'{HOST.CONN}' => '*UNKNOWN*',
							'{HOST.IP}' => '*UNKNOWN*',
							'{HOST.DNS}' => '*UNKNOWN*',
							'{HOST.PORT}' => '{HOST.PORT}',
							'{HOST.NAME1}' => 'API test host - symptom, read-write',
							'{HOST.NAME2}' => '*UNKNOWN*',
							'{EVENT.ID}' => 'macros_rw_symptom',
							'{EVENT.NAME}' => 'API test trigger - macros, symptom, high',
							'{EVENT.NSEVERITY}' => (string) TRIGGER_SEVERITY_HIGH,
							'{EVENT.SEVERITY}' => 'High',
							'{USER.FULLNAME}' => 'API Two Tester Two (api_test_user)',
							'{USER.NAME}' => 'API Two',
							'{USER.SURNAME}' => 'Tester Two',
							'{USER.USERNAME}' => 'api_test_user',
							'{INVENTORY.ALIAS}' => '*UNKNOWN*',
							'{INVENTORY.OS}' => '*UNKNOWN*',
							'{INVENTORY.TYPE}' => '*UNKNOWN*',
							'{INVENTORY.CONTACT}' => '*UNKNOWN*',
							'{INVENTORY.OS1}' => '*UNKNOWN*',
							'{INVENTORY.OS2}' => '*UNKNOWN*',
							'{EVENT.STATUS}' => trigger_value2str(TRIGGER_VALUE_TRUE),
							'{EVENT.VALUE}' => (string) TRIGGER_VALUE_TRUE,
							'{HOSTGROUP.ID}' => '{HOSTGROUP.ID}',
							'{EVENT.CAUSE.ID}' => 'macros_d_cause',
							'{EVENT.CAUSE.NAME}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.NSEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.SEVERITY}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.STATUS}' => UNRESOLVED_MACRO_STRING,
							'{EVENT.CAUSE.VALUE}' => UNRESOLVED_MACRO_STRING
						]
					]
				],
				'expected_error' => null
			]
		];
	}

	/**
	 * Test script.getScriptsByHEvents with various users. Checks if result has host IDs keys, script IDs, resolves
	 * macros for script and compares results.
	 *
	 * @dataProvider getScriptsByEventsDataInvalid
	 * @dataProvider getScriptsByEventsDataValid
	 */
	public function testScripts_GetScriptsByEvents($request, $expected_result, $expected_error) {
		if (array_key_exists('login', $request)) {
			$this->authorize($request['login']['user'], $request['login']['password']);
		}

		// Replace ID placeholders with real IDs.
		$request = self::resolveIds($request);

		if ($expected_error === null) {
			foreach ($expected_result['scripts'] as &$script) {
				$script = self::resolveIds($script);
			}
			unset($script);

			$expected_result = self::resolveMacros($expected_result);
			$expected_result = self::resolveComplexIds($expected_result);
		}

		$result = $this->call('script.getScriptsByEvents', $request['eventids'], $expected_error);

		if ($expected_error === null) {
			if (array_key_exists('has.eventid:scriptid', $expected_result)) {
				foreach ($expected_result['has.eventid:scriptid'] as $eventid => $scriptids) {
					$this->assertTrue(array_key_exists($eventid, $result['result']), 'expected eventid ID '.$eventid);
					$ids = array_column($result['result'][$eventid], 'scriptid');
					$this->assertEmpty(array_diff($scriptids, $ids), 'Expected ids: '.implode(',', $scriptids));
				}
			}

			if (array_key_exists('!has.eventid:scriptid', $expected_result)) {
				foreach ($expected_result['!has.eventid:scriptid'] as $eventid => $scriptids) {
					$this->assertTrue(array_key_exists($eventid, $result['result']), 'expected eventid ID '.$eventid);
					$ids = array_column($result['result'][$eventid], 'scriptid');
					$this->assertEquals($scriptids, array_diff($scriptids, $ids));
				}
			}

			foreach ($result['result'] as $eventid => $result_scripts) {
				foreach ($result_scripts as $result_script) {
					foreach ($expected_result['scripts'] as $expected_script) {
						if (bccomp($result_script['scriptid'], $expected_script['scriptid']) == 0) {
							$expected_script['url'] = strtr($expected_script['url'],
								$expected_result['event_macros'][$eventid]
							);
							$expected_script['confirmation'] = strtr($expected_script['confirmation'],
								$expected_result['event_macros'][$eventid]
							);

							$this->assertEquals($expected_script, $result_script);
						}
					}
				}
			}
		}
	}

	/**
	 * Get the original scripts before update.
	 *
	 * @param array $scriptids
	 *
	 * @return array
	 */
	private function getScripts(array $scriptids): array {
		$response = $this->call('script.get', [
			'output' => ['scriptid', 'name', 'command', 'host_access', 'usrgrpid', 'groupid', 'description',
				'confirmation', 'type', 'execute_on', 'timeout', 'scope', 'port', 'authtype', 'username', 'password',
				'publickey', 'privatekey', 'menu_path', 'url', 'new_window', 'parameters'
			],
			'scriptids' => $scriptids,
			'preservekeys' => true,
			'nopermissions' => true
		]);

		return $response['result'];
	}

	/**
	 * Restore scripts to original state depending on each type.
	 *
	 * @param array $scripts
	 */
	private function restoreScripts(array $scripts): void {
		foreach ($scripts as &$script) {
			switch ($script['type']) {
				case ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT:
					unset($script['timeout'], $script['port'], $script['authtype'], $script['username'],
						$script['password'], $script['publickey'], $script['privatekey'], $script['parameters'],
						$script['url'], $script['new_window']
					);
					break;

				case ZBX_SCRIPT_TYPE_IPMI:
					unset($script['execute_on'], $script['timeout'], $script['port'], $script['authtype'],
						$script['username'], $script['password'], $script['publickey'], $script['privatekey'],
						$script['parameters'], $script['url'], $script['new_window']
					);
					break;

				case ZBX_SCRIPT_TYPE_SSH:
					unset($script['execute_on'], $script['timeout'], $script['parameters'], $script['url'],
						$script['new_window']
					);

					if ($script['authtype'] == ITEM_AUTHTYPE_PASSWORD) {
						unset($script['publickey'], $script['privatekey']);
					}
					break;

				case ZBX_SCRIPT_TYPE_TELNET:
					unset($script['execute_on'], $script['timeout'], $script['authtype'], $script['publickey'],
						$script['privatekey'], $script['parameters'], $script['url'], $script['new_window']
					);
					break;

				case ZBX_SCRIPT_TYPE_WEBHOOK:
					unset($script['execute_on'], $script['authtype'], $script['port'], $script['publickey'],
						$script['username'], $script['password'], $script['privatekey'], $script['url'],
						$script['new_window']
					);
					break;

				case ZBX_SCRIPT_TYPE_URL:
					unset($script['execute_on'], $script['timeout'], $script['port'], $script['authtype'],
						$script['username'], $script['password'], $script['publickey'], $script['privatekey'],
						$script['parameters'], $script['command']
					);
					break;
			}

			if ($script['scope'] == ZBX_SCRIPT_SCOPE_ACTION) {
				unset($script['menu_path'], $script['usrgrpid'], $script['host_access'], $script['confirmation']);
			}
		}
		unset($script);

		$this->call('script.update', $scripts, null);
	}

	/**
	 * Delete all created data after test.
	 */
	public static function clearData() {
		// Delete actions.
		CDataHelper::call('action.delete', self::$data['actionids']);

		// Delete scripts.
		$scriptids = array_values(self::$data['scriptids']);
		$scriptids = array_merge($scriptids, self::$data['created']);
		CDataHelper::call('script.delete', $scriptids);

		// Delete users.
		CDataHelper::call('user.delete', self::$data['userids']);

		// Delete user groups.
		CDataHelper::call('usergroup.delete', self::$data['usrgrpids']);

		// Delete global macro.
		CDataHelper::call('usermacro.deleteglobal', [self::$data['usermacroid']]);

		// Delete hosts (items, triggers are deleted as well).
		CDataHelper::call('host.delete', self::$data['hostids']);

		// All events have to be deleted manually.
		DB::delete('event_symptom', ['eventid' => array_values(self::$data['eventids'])]);
		DB::delete('events', ['eventid' => array_values(self::$data['eventids'])]);

		// Delete hosts groups.
		CDataHelper::call('hostgroup.delete', self::$data['groupids']);

		// The "ids" table should be restored using the standart backup after tests are complete.
	}

	/**
	 * Helper function to convert placeholders to real IDs.
	 *
	 * @param array $request
	 *
	 * @return array
	 */
	private static function resolveIds(array $request): array {
		// For script.get, script.update and script.execute methods. Same fields are checked in "filter" as well.
		$request_ = array_key_exists('filter', $request) ? $request['filter'] : $request;

		foreach (['scriptid', 'hostid', 'eventid', 'usrgrpid', 'groupid'] as $field) {
			// Do not compare != 0 (it will not work) or !== 0 or !== '0' (avoid type check here).
			if (is_array($request_) && array_key_exists($field, $request_) && $request_[$field] !== ''
					&& $request_[$field] != '0' && $request_[$field] != 999999 && $request_[$field] !== null
					&& !is_array($request_[$field])) {
				$request_[$field] = self::$data[$field.'s'][$request_[$field]];
			}
		}

		if (array_key_exists('filter', $request)) {
			$request['filter'] = $request_;
		}
		else {
			$request = $request_;
		}

		// For script.get method and getScriptsByHosts/Events.
		foreach (['scriptids', 'groupids', 'eventids', 'hostids', 'usrgrpids', 'actionids'] as $field) {
			if (array_key_exists($field, $request)) {
				if (is_array($request[$field]) && $request[$field]) {
					foreach ($request[$field] as &$id) {
						// Do not compare != 0 (it will not work) or !== 0 or !== '0' (avoid type check here).
						if ($id != '0' && $id !== '' && $id !== null && !is_array($id)) {
							$id = self::$data[$field][$id];
						}
					}
					unset($id);
				}
				else {
					// Do not compare != 0 (it will not work) or !== 0 or !== '0' (avoid type check here).
					if ($request[$field] != '0' && $request[$field] !== '' && $request[$field] !== null
							&& !is_array($request_[$field])) {
						$request[$field] = self::$data[$field][$request[$field]];
					}
				}
			}
		}

		return $request;
	}

	/**
	 * Helper function to convert placeholder for host and event macro IDs to real IDs.
	 *
	 * @param array $request
	 *
	 * @return array
	 */
	private static function resolveMacros(array $request): array {
		// For script.getScriptsByHosts and script.getScriptsByEvents methods.
		foreach (['host_macros', 'event_macros'] as $field) {
			if (array_key_exists($field, $request)) {
				foreach ($request[$field] as $key => $macros) {
					$new_key = ($field === 'host_macros')
						? self::$data['hostids'][$key]
						: self::$data['eventids'][$key];

					// Currently only two ID types are supported.
					foreach ($macros as $macro => &$id) {
						if (preg_match('/^\{(HOST|EVENT)\.(CAUSE\.)?ID[1-9]?\}$/', $macro, $match) && $id !== ''
								&& $id !== '*UNKNOWN*' && $id !== $macro) {
							$id = self::$data[strtolower($match[1]).'ids'][$id];
						}
					}
					unset($id);

					$request[$field][$new_key] = $macros;
					unset($request[$field][$key]);
				}
			}
		}

		return $request;
	}

	/**
	 * Helper function to resolve complex IDs. Processes, for example, "has.scriptid" or "!has.scriptid:hostid" keys.
	 *
	 * @param array $request
	 *
	 * @return array
	 */
	private static function resolveComplexIds(array $request): array {
		foreach ($request as $key => &$result) {
			if (preg_match('/^(!?[a-z]+)\.([a-z]+)\:?([a-z]+)?/', $key, $match)) {
				if (count($match) == 3) {
					foreach ($result as &$id) {
						$id = self::$data[$match[2].'s'][$id];
					}
					unset($id);
				}
				elseif (count($match) == 4) {
					$new_result = [];

					foreach ($result as $id1 => &$ids2) {
						$new_result[self::$data[$match[2].'s'][$id1]] = [];

						if ($ids2) {
							foreach ($ids2 as &$id2) {
								$id2 = self::$data[$match[3].'s'][$id2];
							}
							unset($id2);

							$new_result[self::$data[$match[2].'s'][$id1]] = $ids2;
						}
					}
					unset($ids2);

					$result = $new_result;
				}
			}
		}
		unset($result);

		return $request;
	}
}
