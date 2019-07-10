<?php
/*
** Zabbix
** Copyright (C) 2001-2019 Zabbix SIA
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

require_once dirname(__FILE__).'/../include/CWebTest.php';

/**
 * @backup users
 */

class testFormUserMedia extends CWebTest {

	public function getCreateData() {
		return [
			// User media with multiple e-mails - all fields specified.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Type' => 'Email',
						'When active' => '1-5,09:00-18:00',
						'Enabled' => true
					],
					'Use if severity' => [
						'Average',
						'High',
						'Disaster'
					],
					'emails' => [
						'123@456.ttt',
						'Mr Email <bestEmail@zabbix.com>',
						'∑Ω-symbols <utf-8@zabbix.coom>',
						'"Zabbix\@\<H(comment)Q\>" <zabbix@company.com>'
					]
				]
			],
			// User media with only mandatory fields specified.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Type' => 'SMS',
						'Send to' => '+371 66600666'
					],
					'additional media' => [
						[
							'Type' => 'Jabber',
							'Send to' => 'Jabber channel 666'
						],
						[
							'Type' => 'SMS via IP',
							'Send to' => '192.168.256.256'
						],
						[
							'Type' => 'Test script',
							'Send to' => 'Path to test script'
						]
					]
				]
			],
			// User with multiple "When active" periods.
			[
				[
					'expected' => TEST_GOOD,
					'fields' => [
						'Type' => 'SMS',
						'Send to' => '+371 66600666',
						'When active' => '{$DATE.TIME};6-7,09:00-15:00',
						'Enabled' => false
					]
				]
			],
			// Empty email address.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Type' => 'Email'
					],
					'emails' => [
						' '
					],
					'error_message' => 'Incorrect value for field "sendto_emails": cannot be empty.'
				]
			],
			// Email address without the "@" symbol.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Type' => 'Email'
					],
					'emails' => [
						'no_at.zabbix.com'
					],
					'error_message' => 'Invalid email address "no_at.zabbix.com".'
				]
			],
			// Email address without the domain.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Type' => 'Email'
					],
					'emails' => [
						'no_domain@zabbix'
					],
					'error_message' => 'Invalid email address "no_domain@zabbix".'
				]
			],
			// Email address with numbers in domain.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Type' => 'Email'
					],
					'emails' => [
						'number_in_domain@zabbix.2u'
					],
					'error_message' => 'Invalid email address "number_in_domain@zabbix.2u".'
				]
			],
			// Email address with name and missing "<" and ">" symbols.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Type' => 'Email'
					],
					'emails' => [
						'Mr Person person@zabbix.com'
					],
					'error_message' => 'Invalid email address "Mr Person person@zabbix.com".'
				]
			],
			// Email address without the recepient specified - just the domain.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Type' => 'Email'
					],
					'emails' => [
						'@zabbix.com'
					],
					'error_message' => 'Invalid email address "@zabbix.com".'
				]
			],
			// Email address that contains a space.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Type' => 'Email'
					],
					'emails' => [
						'person @zabbix.com'
					],
					'error_message' => 'Invalid email address "person @zabbix.com".'
				]
			],
			// Empty Jabber channel name.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Type' => 'Jabber',
						'Send to' => ''
					],
					'error_message' => 'Incorrect value for field "sendto": cannot be empty.'
				]
			],
			// Empty SMS recipient.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Type' => 'SMS',
						'Send to' => ''
					],
					'error_message' => 'Incorrect value for field "sendto": cannot be empty.'
				]
			],
			// Empty Test script recipient.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Type' => 'Test script',
						'Send to' => ''
					],
					'error_message' => 'Incorrect value for field "sendto": cannot be empty.'
				]
			],
			// String in when active.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Type' => 'SMS',
						'Send to' => '192.168.0.1',
						'When active' => 'allways'
					],
					'error_message' => 'Field "When active" is not correct: a time period is expected'
				]
			],
			// Only time period in when active.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Type' => 'SMS',
						'Send to' => '12345678',
						'When active' => '00:00-24:00'
					],
					'error_message' => 'Field "When active" is not correct: a time period is expected'
				]
			],
			// Only days in when active.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Type' => 'SMS',
						'Send to' => '12345678',
						'When active' => '1-5'
					],
					'error_message' => 'Field "When active" is not correct: a time period is expected'
				]
			],
			// When active value is set to a speciffic moment.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Type' => 'SMS',
						'Send to' => '12345678',
						'When active' => '1-5,15:00-15:00'
					],
					'error_message' => 'Field "When active" is not correct: a time period is expected'
				]
			],
			// When active defined with incorrect order.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Type' => 'SMS',
						'Send to' => '12345678',
						'When active' => '15:00-18:00,1-5'
					],
					'error_message' => 'Field "When active" is not correct: a time period is expected'
				]
			],
			// When active defined using a regular macro.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Type' => 'SMS',
						'Send to' => '12345678',
						'When active' => '{TIME}'
					],
					'error_message' => 'Field "When active" is not correct: a time period is expected'
				]
			],
			// When active defined using a LLD marco.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Type' => 'SMS',
						'Send to' => '12345678',
						'When active' => '{#DATE.TIME}'
					],
					'error_message' => 'Field "When active" is not correct: a time period is expected'
				]
			],
			// Multiple When active periods separated by coma.
			[
				[
					'expected' => TEST_BAD,
					'fields' => [
						'Type' => 'SMS',
						'Send to' => '12345678',
						'When active' => '1-5,09:00-18:00,6-7,12:00-15:00'
					],
					'error_message' => 'Field "When active" is not correct: a time period is expected'
				]
			]
		];
	}

	/**
	 * @dataProvider getCreateData
	 */
	public function testFormUserMedia_Validation($data) {
		$sql = 'SELECT * FROM media';
		$old_hash = CDBHelper::getHash($sql);

		// Navigate to Media tab for user 'user-zabbix'.
		$this->page->login()->open('users.php?ddreset=1');
		$this->query('link:user-zabbix')->waitUntilVisible()->one()->click();
		$user_form = $this->query('xpath://form[@name="userForm"]')->asForm()->waitUntilPresent()->one();
		$user_form->selectTab('Media');
		// Check that no medias are configured.
		$this->assertTrue($user_form->getField('Media')->asTable()->getRows()->count() == 0);
		// Add media.
		$this->query('button', 'Add')->one()->click();
		$this->setAndSubmitMediaValues($data);

		// Verify if media was added and its configuration
		if ($data['expected'] === TEST_GOOD) {
			$this->verifyMediaConfiguration($data,'1-7,00:00-24:00',null);
			// Add other media if the flag is set
			if (array_key_exists('additional media', $data)) {
				$i = 1;
				foreach ($data['additional media'] as $media) {
					$this->query('button', 'Add')->one()->click();
					$dialog = $this->query('id:overlay_dialogue')->asOverlayDialog()->one()->waitUntilReady();
					$form = $dialog->asForm();
					$form->fill($media);
					$form->submit();
					$this->page->waitUntilReady();
					$this->assertTrue($this->query("xpath://div[@id='mediaTab']//tbody/tr[@id='user_medias_".$i."']")->waitUntilVisible()->count() == 1);
					$i++;
				}
				$media_list = $this->query("xpath://div[@id='mediaTab']//table")->asTable()->waitUntilReady()->one();
				$this->assertEquals($media_list->getRows()->count(), count($data['additional media'])+1);
			}
		}
		else {
			$this->verifyMediaUpdateFailure($data);
			$this->assertEquals($old_hash, CDBHelper::getHash($sql));
		}
	}

	/**
	 * @dataProvider getCreateData
	 */
	public function testFormUserMedia_Edit($data) {
		$sql = 'SELECT * FROM media';
		$old_hash = CDBHelper::getHash($sql);

		//Open a user with defined medias and select a row.
		$this->page->login()->open('users.php?ddreset=1');
		$this->query('link:Admin')->waitUntilVisible()->one()->click();
		$user_form = $this->query('xpath://form[@name="userForm"]')->asForm()->waitUntilPresent()->one();
		$user_form->selectTab('Media');
		$table = $user_form->getField('Media')->asTable();

		//Edit selected media
		$edit_send_to = $this->query("xpath://tr[@id='user_medias_0']/td[2]")->waitUntilPresent()->one()->getText();
		$edit_row = $table->findRow('Send to',$edit_send_to);
		$original_period = $edit_row->getColumn('When active')->getText();
		$edit_row->query('button:Edit')->one()->click();
		$this->setAndSubmitMediaValues($data);

		// Verify if media was updated and its configuration.
		if ($data['expected'] === TEST_GOOD) {
			$this->verifyMediaConfiguration($data,$original_period,$edit_send_to);
		}
		else {
			$this->verifyMediaUpdateFailure($data);
			$this->assertEquals($old_hash, CDBHelper::getHash($sql));
		}
	}

	public function testFormUserMedia_StatusChangeAndRemove() {
		$actions = [
			'Cancel',
			'Update'
		];
		foreach ($actions as $action) {
			if ($action == 'Cancel') {
				$sql = 'SELECT * FROM media';
				$old_hash = CDBHelper::getHash($sql);
			}
			$this->page->login()->open('users.php?form=update&userid=1');
			$this->query("xpath://a[@id='tab_mediaTab']")->waitUntilVisible()->one()->click();
			$table = $this->query("xpath://ul[@id='userMediaFormList']//table")->asTable()->one();

			// Change status of one of the medias.
			$row = $table->findRow('Send to','test@zabbix.com');
			$this->assertEquals($row->getColumn('Status')->getText(),'Enabled');
			$row->getColumn('Status')->click();
			$this->assertEquals($row->getColumn('Status')->getText(),'Disabled');

			// Remove one of the medias.
			$row->getColumn('Action')->query('button:Remove')->one()->click();
			$this->assertTrue($table->findRow('Send to','test@zabbix.com') == 0);

			if ($action == 'Update') {
				$this->query('button:Update')->one()->click();
			}
			else {
				$this->query('button:Cancel')->one()->click();
			}
			$this->query('link', 'Admin')->waitUntilVisible()->one()->click();
			$this->query("xpath://a[@id='tab_mediaTab']")->waitUntilVisible()->one()->click();
			$table = $this->query("xpath://ul[@id='userMediaFormList']//table")->asTable()->one();
			if ($action == 'Update') {
				$this->assertTrue($table->findRow('Send to','test@zabbix.com') == 0);
			}
			else {
				$this->assertEquals($old_hash, CDBHelper::getHash($sql));
			}
		}
	}

	public function testFormUserMedia_EmailRemoval() {
		$media = [
			'Type' => 'Email',
			'emails' => [
				'1@zabbix.com',
				'2@zabbix.com',
				'3@zabbix.com',
				'4@zabbix.com'
			]
		];
		$this->page->login()->open('users.php?form=update&userid=5');
		$this->query("xpath://a[@id='tab_mediaTab']")->waitUntilVisible()->one()->click();

		// Add media with multiple emails.
		$this->query('button', 'Add')->one()->click();
		$this->query("xpath://select[@id='mediatypeid']")->waitUntilVisible()->one()->fill($media['Type']);
		for ($i = 0; $i < count($media['emails']); $i++)  {
			$this->query('id:sendto_emails_'.$i)->one()->fill($media['emails'][$i]);
			if ($i !== count($media['emails'])-1) {
				$this->query('id:email_send_to_add')->waitUntilVisible()->one()->click();
			}
		}
		// Verify that all emails are entered in media configuration form.
		$rows = $this->query("xpath://table[@id='email_send_to']//tr[@class='form_row']")->asTableRow()->all()->count();
		$this->assertEquals($rows, count($media['emails']));
		$this->removeEmailFromListAndVerify($media,false);
		// Edit the media to remove the last email in the list.
		$this->query('button:Edit')->one()->click();
		$this->removeEmailFromListAndVerify($media,true);
	}

		public function getUserData() {
		return [
			// Create a user with media.
			[
				[
					'action' => 'create',
					'user_fields' => [
						'Alias' => 'created-user',
						'Groups' => 'Zabbix administrators',
						'Password' => 'zabbix',
						'Password (once again)' => 'zabbix'
					],
					'media_fields' => [
						'Type' => 'SMS',
						'Send to' => '+371 74661x'
					],
					'expected_message' => 'User added'
				]
			],
			// Update a user with media.
			[
				[
					'action' => 'update',
					'username' => 'user-zabbix',
					'media_fields' => [
						'Type' => 'Jabber',
						'Send to' => 'zabbix channel'
					],
					'expected_message' => 'User updated'
				]
			],
			// Delete a user with media.
			[
				[
					'action' => 'delete',
					'username' => 'test-user',
					'expected_message' => 'User deleted'
				]
			]
		];
	}

	/**
	 * @dataProvider getUserData
	 */
	public function testFormUserMedia_UserWithMediaActions($data) {
		$this->page->login()->open('users.php?ddreset=1');
		// Get userid for of the user to be deleted to verify media deletion along with the user.
		if ($data['action'] === 'delete') {
			$userid = CDBHelper::getValue('SELECT userid FROM users WHERE alias =' . zbx_dbstr($data['username']));
		}
		// Fill in user form for the created user or just open an existing one.
		if ($data['action'] === 'create') {
			$this->page->query('button:Create user')->one()->click();
			$user_form = $this->query('name:userForm')->asForm()->waitUntilVisible()->one();
			$user_form->fill($data['user_fields']);
		}
		else {
			$this->query('link', $data['username'])->waitUntilVisible()->one()->click();
		}
		// Fill in and submit user media form.
		if ($data['action'] !== 'delete') {
			$this->query("xpath://a[@id='tab_mediaTab']")->waitUntilVisible()->one()->click();
			$this->query('button', 'Add')->one()->click();
			$media_form = $this->query('id:media_form')->asForm()->waitUntilVisible()->one();
			$media_form->fill($data['media_fields']);
			$media_form->submit();
		}
		switch ($data['action']) {
			case 'create':
				$user_form->submit();
				break;
			case 'update':
				$this->query('button:Update') -> one()->click();
				break;
			case 'delete':
				$this->query('button:Delete') -> one()->click();
				$this->page->acceptAlert();
				break;
		}
		// Check that the action took place.
		$message = CMessageElement::find()->one();
		$this->assertTrue($message->isGood());
		$this->assertEquals($data['expected_message'], $message->getTitle());
		if ($data['action'] === 'delete'){
			$this->assertEquals(0, CDBHelper::getCount('SELECT mediaid FROM media WHERE userid ='.zbx_dbstr($userid)));
		}
		else {
			if (array_key_exists('user_fields', $data) ){
				$this->query('link', $data['user_fields']['Alias'])->waitUntilVisible()->one()->click();
			}
			else {
				$this->query('link', $data['username'])->waitUntilVisible()->one()->click();
			}
		$user_form = $this->query('xpath://form[@name="userForm"]')->asForm()->waitUntilVisible()->one();
		$media_field = $user_form->getField('Media')->asTable();
		$this->assertTrue($media_field->getRows()->count() == 1);
		$row = $media_field->getRow(0);
		//Verify the values of "Type" and "Send to" for the created / updated media.
		$this->assertEquals($row->getColumn('Type')->getText(),$data['media_fields']['Type']);
		$this->assertEquals($row->getColumn('Send to')->getText(),$data['media_fields']['Send to']);
		}
	}

	public function removeEmailFromListAndVerify($media,$second_removal) {
		// Remove one of the emails.
		$this->query("xpath://button[@id='sendto_emails_2_remove']")->waitUntilVisible()->one()->click();
		$this->assertTrue($this->query("xpath://input[@id='sendto_emails_2']")->all()->count() == 0);
		// Add/update the media.
		$this->query("xpath://div[@class='overlay-dialogue-footer']/button[1]")->one()->click();
		$this->page->waitUntilReady();
		// Check that the removed emails are not listed in the media.
		$send_to = $this->query("xpath://tr[@id='user_medias_0']/td[2]")->waitUntilVisible()->one()->getText();
		$this->assertNotContains($media['emails'][2], $send_to);
		unset($media['emails'][2]);
		if ($second_removal == true) {
			$this->assertNotContains($media['emails'][3], $send_to);
			unset($media['emails'][3]);
		}
		foreach ($media['emails'] as $email) {
			$this->assertContains($email, $send_to);
		}
	}

	public function setAndSubmitMediaValues($data) {
		$form = COverlayDialogElement::find()->waitUntilPresent()->asForm()->one();
		$form->fill($data['fields']);
		// Check that there is posibility to add only multiple emails to media.
		if ($data['expected'] === TEST_GOOD) {
			if ($data['fields']['Type'] == 'Email') {
				$this->assertTrue($form->query('id:email_send_to_add')->one()->isClickable());
				$this->assertTrue($form->query('button:Remove')->one()->isClickable());
			}
			else {
				$this->assertFalse($form->query('id:email_send_to_add')->one()->isClickable());
				$this->assertFalse($form->query('button:Remove')->one()->isClickable());
			}
		}
		// Specify severities for the media to be created.
		if (array_key_exists('Use if severity', $data)) {
			$severities = $form->getField('Use if severity')->fill($data['Use if severity']);
		}
		// Fill in e-mails if such exist.
		if (array_key_exists('emails', $data)) {
			$form->query('id:sendto_emails_0')->one()->fill($data['emails'][0]);
			if (count($data['emails']) > 1) {
				for ($i = 1; $i < count($data['emails']); $i++)  {
					$form->query('id:email_send_to_add')->waitUntilVisible()->one()->click();
					$form->query('id:sendto_emails_'.$i)->one()->fill($data['emails'][$i]);
				}
			}
		}
		$form->submit();
		$this->page->waitUntilReady();
	}


	public function verifyMediaConfiguration($data,$original_period,$edit_send_to) {
		// Verify media type.
		$user_form = $this->query('xpath://form[@name="userForm"]')->asForm()->waitUntilVisible()->one();
		$media_field = $user_form->getField('Media')->asTable();
		if ($edit_send_to == null) {
			$this->assertTrue($media_field->getRows()->count() == 1);
			$row = $media_field->getRow(0);
		}
		else {
			foreach ($media_field->getRows() as $table_row) {
				if ($table_row->getColumn('Send to')->getText() == $this->query("xpath://tr[@id='user_medias_0']/td[2]")->one()->getText()) {
					$row = $table_row;
				}
			}
		}
		$this->assertEquals($row->getColumn('Type')->getText(),$data['fields']['Type']);
		// Verify the value of the "Send to" field.
		if ($this->query("xpath://tr[@id='user_medias_0']/td[2]/div[@class='hint-box']")->count() == 0) {
			$send_to = $row->getColumn('Send to')->getText();
		}
		else {
			$send_to = $this->query("xpath://tr[@id='user_medias_0']/td[2]/div[@class='hint-box']")->one()->getText();
		}
		if (array_key_exists('emails', $data)) {
			foreach ($data['emails'] as $email) {
				$this->assertContains($email, $send_to);
			}
		}
		else {
			$this->assertEquals($send_to, $data['fields']['Send to']);
		}
		// Verify media active period.
		$when_active = $row->getColumn('When active')->getText();
		if (array_key_exists('When active', $data['fields'])) {
			$this->assertEquals($when_active, $data['fields']['When active']);
		}
		else {
			$this->assertEquals($when_active, $original_period);
		}
		// Verify media status.
		$status = $row->getColumn('Status')->getText();
		if (!array_key_exists('Enabled',$data['fields']) or $data['fields']['Enabled'] === true) {
			$this->assertEquals($status, 'Enabled');
		}
		else {
			$this->assertEquals($status, 'Disabled');
		}
		// Verify selected severities.
		$reference_severities = [
			'Not classified' => '1',
			'Information' => '2',
			'Warning' => '3',
			'Average' => '4',
			'High' => '5',
			'Disaster' => '6'
		];
		if (array_key_exists('Use if severity', $data)) {
			// Verify that the passed severities are turned on
			foreach($data['Use if severity'] as $used_severity) {
				$actual_severity = $this->query("xpath://tr[@id='user_medias_0']/td[4]/div/div[".$reference_severities[$used_severity]."]")->one()->getText();
				$this->assertEquals($actual_severity, $used_severity.' (on)');
				unset($reference_severities[$used_severity]);
			}
			// Verify that other severities are turned off
			foreach($reference_severities as $name => $unused_severity) {
				$actual_severity = $this->query("xpath://tr[@id='user_medias_0']/td[4]/div/div[".$unused_severity."]")->one()->getText();
				$this->assertEquals($name.' (off)', $actual_severity);
			}
		}
		else {
			// Verify that when no severities are passed - they all are turned on by default
			for ($i = 1; $i < 7; $i++) {
				$severity = $this->query("xpath://tr[@id='user_medias_0']/td[4]/div/div[".$i."]")->one()->getText();
				$this->assertContains('(on)', $severity);
			}
		}
	}

	public function verifyMediaUpdateFailure($data) {
		$message = $this->query('class:msg-bad')->waitUntilVisible()->asMessage()->one();
		$this->assertTrue($message->isBad());
		$this->assertTrue($message->hasLine($data['error_message']));
		$this->assertTrue($this->query('id:overlay_dialogue')->count() === 1);
	}
}
