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


require_once dirname(__FILE__) . '/../include/CWebTest.php';

/**
 * Test checks link from trigger URL field on different pages.
 *
 * @onBefore prepareTriggerData
 *
 * @backup profiles, problem
 */
class testPageTriggerUrl extends CWebTest {

	private static $custom_name = 'URL name for menu';

	/**
	 * Add URL name for trigger.
	 */
	public function prepareTriggerData() {
		$response = CDataHelper::call('trigger.update', [
			[
				'triggerid' => '100032',
				'url_name' => 'URL name for menu'
			]
		]);
	}

	public function getTriggerLinkData() {
		return [
			[
				[
					'trigger' => '1_trigger_High',
					'links' => [
						'Problems' => 'zabbix.php?action=problem.view&filter_name=&triggerids%5B%5D=100035',
						'History' => ['1_item' => 'history.php?action=showgraph&itemids%5B%5D=99086'],
						'Trigger' => 'triggers.php?form=update&triggerid=100035&context=host',
						'Items' => ['1_item' => 'items.php?form=update&itemid=99086&context=host'],
						'Mark as cause' => '',
						'Mark selected as symptoms' => '',
						'Trigger URL' => 'tr_events.php?triggerid=100035&eventid=9003',
						'Unique webhook url' => 'zabbix.php?action=mediatype.list&ddreset=1',
						'Webhook url for all' => 'zabbix.php?action=mediatype.edit&mediatypeid=101'
					],
					'background' => "high-bg"
				]
			],
			[
				[
					'trigger' => '1_trigger_Not_classified',
					'links' => [
						'Problems' => 'zabbix.php?action=problem.view&filter_name=&triggerids%5B%5D=100032',
						'History' => ['1_item' => 'history.php?action=showgraph&itemids%5B%5D=99086'],
						'Trigger' => 'triggers.php?form=update&triggerid=100032&context=host',
						'Items' => ['1_item' => 'items.php?form=update&itemid=99086&context=host'],
						'Mark as cause' => '',
						'Mark selected as symptoms' => '',
						'URL name for menu' => 'tr_events.php?triggerid=100032&eventid=9000',
						'Webhook url for all' => 'zabbix.php?action=mediatype.edit&mediatypeid=101'
					],
					'background' => 'na-bg'
				]
			]
		];
	}

	/**
	 * Check trigger url in Problems widget.
	 *
	 * @dataProvider getTriggerLinkData
	 */
	public function testPageTriggerUrl_ProblemsWidget($data) {
		// Prepare data provider.
		unset($data['links']['Mark selected as symptoms']);

		$this->page->login()->open('zabbix.php?action=dashboard.view&dashboardid=1');
		$dashboard = CDashboardElement::find()->one();
		$widget = $dashboard->getWidget('Current problems');
		$table = $widget->getContent()->asTable();

		// Find trigger and open trigger overlay dialogue.
		$table->query('link', $data['trigger'])->one()->click();
		$this->checkTriggerUrl($data);
	}

	/**
	 * Check trigger url in Trigger overview widget.
	 *
	 * @dataProvider getTriggerLinkData
	 */
	public function testPageTriggerUrl_TriggerOverviewWidget($data) {
		// Add 'Acknowledge' menu link to data provider.
		$array = $data['links'];
		array_shift($array);
		$data['links'] = ['Problems' => $data['links']['Problems'],	'Acknowledge' => ''] + $array;

		// Remove 'cause and symptoms' from data provider.
		unset($data['links']['Mark as cause']);
		unset($data['links']['Mark selected as symptoms']);

		$this->page->login()->open('zabbix.php?action=dashboard.view&dashboardid=1020');
		$dashboard = CDashboardElement::find()->one();
		$widget = $dashboard->getWidget('Group to check Overview');

		// Get row of trigger "1_trigger_Not_classified".
		$row = $widget->getContent()->asTable()->findRow('Triggers', $data['trigger']);

		// Open trigger context menu.
		$row->query('xpath://td[contains(@class, "'.$data['background'].'")]')->one()->click();
		$this->checkTriggerUrl($data, ['VIEW', 'CONFIGURATION', 'LINKS']);
	}

	/**
	 * Check trigger url on Problems page.
	 *
	 * @dataProvider getTriggerLinkData
	 */
	public function testPageTriggerUrl_ProblemsPage($data) {
		$this->page->login()->open('zabbix.php?action=problem.view');

		// Open trigger context menu.
		$this->query('class:list-table')->asTable()->one()->query('link', $data['trigger'])->one()->click();
		$this->checkTriggerUrl($data);
	}

	public function resetFilter() {
		DBexecute('DELETE FROM profiles WHERE idx LIKE \'%web.overview.filter%\'');
	}

	/**
	 * Check trigger url on Event details page.
	 *
	 * @dataProvider getTriggerLinkData
	 */
	public function testPageTriggerUrl_EventDetails($data) {
		// Prepare data provider.
		unset($data['links']['Mark selected as symptoms']);
		$option = array_key_exists('Trigger URL', $data['links']) ? 'Trigger URL' : self::$custom_name;

		$this->page->login()->open($data['links'][$option]);
		$this->query('link', $data['trigger'])->waitUntilPresent()->one()->click();
		$this->checkTriggerUrl($data);
	}

	/**
	 * Follow trigger url and check opened page.
	 *
	 * @param array $data		data provider with fields values
	 * @param array $titles		titles in context menu
	 */
	private function checkTriggerUrl($data, $titles = ['VIEW', 'CONFIGURATION', 'PROBLEM', 'LINKS']) {
		$option = array_key_exists('Trigger URL', $data['links']) ? 'Trigger URL' : self::$custom_name;

		// Check trigger popup menu.
		$trigger_popup = CPopupMenuElement::find()->waitUntilVisible()->one();
		$this->assertTrue($trigger_popup->hasTitles($titles));
		$this->assertEquals(array_keys($data['links']), $trigger_popup->getItems()->asText());

		foreach ($data['links'] as $menu => $links) {
			// Check 2-level menu links.
			if (is_array($links)) {
				$item_link = $trigger_popup->getItem($menu)->query('xpath:./../ul//a')->one();
				$this->assertEquals(array_keys($links), [$item_link->getText()]);
				$this->assertStringContainsString(array_values($links)[0], $item_link->getAttribute('href'));
			}
			else {
				// Check 1-level menu links.
				if ($links !== '') {
					$this->assertStringContainsString($links, $trigger_popup->getItem($menu)->getAttribute('href'));
				}
			}
		}

		// Open trigger link.
		$trigger_popup->fill($option);

		// Check opened page.
		$this->assertEquals('Event details', $this->query('tag:h1')->waitUntilVisible()->one()->getText());
		$this->assertStringContainsString($data['links'][$option], $this->page->getCurrentUrl());
	}
}
