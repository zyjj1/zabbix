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

/**
 * @backup userdirectory, userdirectory_ldap, userdirectory_saml, userdirectory_idpgroup, userdirectory_usrgrp, userdirectory_media, config, usrgrp
 */
class testAuthentication extends CAPITest {

	public const TEST_DATA_TO_RESOLVE = [
		'disabled_usrgrpid' => 'Disabled user group for API tests'
	];

	public static $data = [
		'disabled_usrgrpid' => null
	];

	public static function authentication_get_data() {
		return [
			'Test getting authentication general data' => [
				'authentication' => [
					'output' => ['authentication_type', 'http_auth_enabled', 'http_login_form', 'http_strip_domains',
						'http_case_sensitive', 'ldap_auth_enabled', 'ldap_case_sensitive', 'saml_auth_enabled',
						'saml_case_sensitive', 'passwd_min_length', 'passwd_check_rules', 'jit_provision_interval',
						'saml_jit_status', 'ldap_jit_status', 'disabled_usrgrpid'
					]
				],
				'get_result' => [
					// General fields.
					'authentication_type' => [ZBX_AUTH_INTERNAL, ZBX_AUTH_LDAP],
					'passwd_min_length' => ['min' => 1, 'max' => 70],
					'passwd_check_rules' => [
						'min' => 0x00,
						'max' => (PASSWD_CHECK_CASE | PASSWD_CHECK_DIGITS | PASSWD_CHECK_SPECIAL | PASSWD_CHECK_SIMPLE)
					],

					// HTTP auth fields.
					'http_auth_enabled' => [ZBX_AUTH_HTTP_DISABLED, ZBX_AUTH_HTTP_ENABLED],
					'http_login_form' => [ZBX_AUTH_FORM_ZABBIX, ZBX_AUTH_FORM_HTTP],
					'http_strip_domains' => '',
					'http_case_sensitive' => [ZBX_AUTH_CASE_INSENSITIVE, ZBX_AUTH_CASE_SENSITIVE],

					// LDAP fields.
					'ldap_auth_enabled' =>	[ZBX_AUTH_LDAP_DISABLED, ZBX_AUTH_LDAP_ENABLED],
					'ldap_case_sensitive' => [ZBX_AUTH_CASE_INSENSITIVE, ZBX_AUTH_CASE_SENSITIVE],
					'ldap_jit_status' => [JIT_PROVISIONING_DISABLED, JIT_PROVISIONING_ENABLED],
					'jit_provision_interval' => '1h',

					// SAML fields.
					'saml_auth_enabled' => [ZBX_AUTH_SAML_DISABLED, ZBX_AUTH_SAML_ENABLED],
					'saml_case_sensitive' => [ZBX_AUTH_CASE_INSENSITIVE, ZBX_AUTH_CASE_SENSITIVE],
					'saml_jit_status' => [JIT_PROVISIONING_DISABLED, JIT_PROVISIONING_ENABLED]
				],
				'expected_error' => null
			]
		];
	}

	/**
	 * @dataProvider authentication_get_data
	 */
	public function testAuthentication_Get($authentication, $get_result, $expected_error) {
		$result = $this->call('authentication.get', $authentication);

		if ($expected_error === null) {
			$result = $result['result'];

			// General fields.
			$this->assertContains($result['authentication_type'], $get_result['authentication_type']);
			$this->assertGreaterThanOrEqual($get_result['passwd_min_length']['min'], $result['passwd_min_length']);
			$this->assertLessThanOrEqual($get_result['passwd_min_length']['max'], $result['passwd_min_length']);
			$this->assertGreaterThanOrEqual($get_result['passwd_check_rules']['min'], $result['passwd_check_rules']);
			$this->assertLessThanOrEqual($get_result['passwd_check_rules']['max'], $result['passwd_check_rules']);

			// HTTP auth fields.
			$this->assertContains($result['http_auth_enabled'], $get_result['http_auth_enabled']);
			$this->assertContains($result['http_login_form'], $get_result['http_login_form']);
			$this->assertContains('http_strip_domains', array_keys($result));
			$this->assertContains($result['http_case_sensitive'], $get_result['http_case_sensitive']);

			// LDAP fields.
			$this->assertContains($result['ldap_auth_enabled'], $get_result['ldap_auth_enabled']);
			$this->assertContains($result['ldap_case_sensitive'], $get_result['ldap_case_sensitive']);
			$this->assertContains($result['ldap_jit_status'], $get_result['ldap_jit_status']);
			$this->assertEquals($get_result['jit_provision_interval'], $result['jit_provision_interval']);

			// SAML fields.
			$this->assertContains($result['saml_auth_enabled'], $get_result['saml_auth_enabled']);
			$this->assertContains($result['saml_case_sensitive'], $get_result['saml_case_sensitive']);
			$this->assertContains($result['saml_jit_status'], $get_result['saml_jit_status']);
		}
	}

	public static function authentication_update_data_invalid() {
		return [
			// Invalid general auth tests.
			'Test invalid authentication type' => [
				'authentication' => [
					'authentication_type' => 999
				],
				'expected_error' => 'Invalid parameter "/authentication_type": value must be one of '.
					implode(', ', [ZBX_AUTH_INTERNAL, ZBX_AUTH_LDAP]).'.'
			],
			'Test invalid password min length' => [
				'authentication' => [
					'passwd_min_length' => 999
				],
				'expected_error' => 'Invalid parameter "/passwd_min_length": value must be one of 1-70.'
			],
			'Test invalid password rules' => [
				'authentication' => [
					'passwd_check_rules' => 999
				],
				'expected_error' => 'Invalid parameter "/passwd_check_rules": value must be one of 0-'.
					(PASSWD_CHECK_CASE | PASSWD_CHECK_DIGITS | PASSWD_CHECK_SPECIAL | PASSWD_CHECK_SIMPLE).'.'
			],
			'Test authentication set to LDAP but having LDAP disabled at the same time' => [
				'authentication' => [
					'authentication_type' => ZBX_AUTH_LDAP,
					'ldap_auth_enabled' => ZBX_AUTH_LDAP_DISABLED
				],
				'expected_error' => 'Incorrect value for field "/authentication_type": LDAP must be enabled.'
			],

			// Invalid HTTP auth tests.
			'Test invalid HTTP auth' => [
				'authentication' => [
					'http_auth_enabled' => 999
				],
				'expected_error' => 'Invalid parameter "/http_auth_enabled": value must be one of '.
					implode(', ', [ZBX_AUTH_HTTP_DISABLED, ZBX_AUTH_HTTP_ENABLED]).'.'
			],
			'Test invalid HTTP form' => [
				'authentication' => [
					'http_login_form' => 999
				],
				'expected_error' => 'Invalid parameter "/http_login_form": value must be one of '.
					implode(', ', [ZBX_AUTH_FORM_ZABBIX, ZBX_AUTH_FORM_HTTP]).'.'
			],
			'Test invalid case sensitive for HTTP auth' => [
				'authentication' => [
					'http_case_sensitive' => 999
				],
				'expected_error' => 'Invalid parameter "/http_case_sensitive": value must be one of '.
					implode(', ', [ZBX_AUTH_CASE_INSENSITIVE, ZBX_AUTH_CASE_SENSITIVE]).'.'
			],

			// Invalid LDAP auth tests.
			'Test invalid LDAP auth' => [
				'authentication' => [
					'ldap_auth_enabled' => 999
				],
				'expected_error' => 'Invalid parameter "/ldap_auth_enabled": value must be one of '.
					implode(', ', [ZBX_AUTH_LDAP_DISABLED, ZBX_AUTH_LDAP_ENABLED]).'.'
			],
			'Test invalid userdirectoryid' => [
				'authentication' => [
					'ldap_userdirectoryid' => 'userdirectory_invalidid_1'
				],
				'expected_error' => 'Invalid parameter "/ldap_userdirectoryid": a number is expected.'
			],
			'Cannot set default authentication ldap when ldap is disabled' => [
				'authentication' => [
					'authentication_type' => ZBX_AUTH_LDAP,
					'ldap_auth_enabled' => ZBX_AUTH_LDAP_DISABLED
				],
				'expected_error' => 'Incorrect value for field "/authentication_type": LDAP must be enabled.'
			],

			// Invalid SAML auth tests.
			'Test invalid SAML auth' => [
				'authentication' => [
					'saml_auth_enabled' => 999
				],
				'expected_error' => 'Invalid parameter "/saml_auth_enabled": value must be one of '.
					implode(', ', [ZBX_AUTH_SAML_DISABLED, ZBX_AUTH_SAML_ENABLED]).'.'
			],
			'Test invalid case sensitive for SAML auth' => [
				'authentication' => [
					'saml_case_sensitive' => 999
				],
				'expected_error' => 'Invalid parameter "/saml_case_sensitive": value must be one of '.
					implode(', ', [ZBX_AUTH_CASE_INSENSITIVE, ZBX_AUTH_CASE_SENSITIVE]).'.'
			],
			'Test setting up the SAML JIT status without specifying deprovisioned user group' => [
				'authentication' => [
					'saml_jit_status' => JIT_PROVISIONING_ENABLED,
					'disabled_usrgrpid' => 0
				],
				'expected_error' => 'Deprovisioned users group cannot be empty.'
			]
		];
	}

	public static function authentication_update_data_valid() {
		return [
			// Cannot test valid authentication change, because that will log out the current user.

			// Valid general auth tests.
			'Test valid password min length' => [
				'authentication' => [
					'passwd_min_length' => 32
				],
				'expected_error' => null
			],
			'Test valid password rules' => [
				'authentication' => [
					'passwd_check_rules' => (PASSWD_CHECK_DIGITS | PASSWD_CHECK_SPECIAL)
				],
				'expected_error' => null
			],
			'Test valid deprovisioning group setup' => [
				'authentication' => [
					'disabled_usrgrpid' => self::TEST_DATA_TO_RESOLVE['disabled_usrgrpid']
				],
				'expected_error' => null
			],

			// Valid HTTP auth tests.
			'Test valid HTTP auth' => [
				'authentication' => [
					'http_auth_enabled' => ZBX_AUTH_HTTP_ENABLED
				],
				'expected_error' => null
			],
			'Test valid HTTP form' => [
				'authentication' => [
					'http_login_form' => ZBX_AUTH_FORM_HTTP
				],
				'expected_error' => null
			],
			'Test update remove domains' => [
				'authentication' => [
					'http_strip_domains' => 'text.string'
				],
				'expected_error' => null
			],
			'Test valid case sensitive for HTTP auth' => [
				'authentication' => [
					'http_case_sensitive' => ZBX_AUTH_CASE_SENSITIVE
				],
				'expected_error' => null
			],

			// Valid LDAP auth tests.
			'Test valid LDAP enabled' => [
				'authentication' => [
					'ldap_auth_enabled' => ZBX_AUTH_LDAP_ENABLED
				],
				'expected_error' => null
			],
			'Test valid LDAP JIT status' => [
				'authentication' => [
					'ldap_jit_status' => JIT_PROVISIONING_ENABLED,
					'disabled_usrgrpid' => self::TEST_DATA_TO_RESOLVE['disabled_usrgrpid']
				],
				'expected_error' => null
			],
			'Test valid LDAP JIT interval' => [
				'authentication' => [
					'jit_provision_interval' => '3h'
				],
				'expected_error' => null
			],
			'Test valid case sensitive for LDAP auth' => [
				'authentication' => [
					'ldap_case_sensitive' => ZBX_AUTH_CASE_SENSITIVE
				],
				'expected_error' => null
			],

			// Valid SAML auth tests.
			'Test valid SAML auth' => [
				'authentication' => [
					'saml_auth_enabled' => ZBX_AUTH_SAML_ENABLED
				],
				'expected_error' => null
			],
			'Test valid case sensitive for SAML auth' => [
				'authentication' => [
					'saml_case_sensitive' => ZBX_AUTH_CASE_SENSITIVE
				],
				'expected_error' => null
			],
			'Test valid SAML JIT status' => [
				'authentication' => [
					'saml_jit_status' => JIT_PROVISIONING_ENABLED,
					'disabled_usrgrpid' => self::TEST_DATA_TO_RESOLVE['disabled_usrgrpid']
				],
				'expected_error' => null
			]
		];
	}

	/**
	 * @dataProvider authentication_update_data_invalid
	 * @dataProvider authentication_update_data_valid
	 */
	public function testAuthentication_Update($authentication, $expected_error) {
		$authentication = self::resolveInstanceData($authentication);

		if ($expected_error === null) {
			// Before updating, collect old authentication data.
			$fields = '';
			foreach ($authentication as $field => $value) {
				$fields = 'c.'.$field.',';
			}
			$fields = substr($fields, 0, -1);
			$sql = 'SELECT '.$fields.' FROM config c WHERE c.configid=1';

			$db_authentication = CDBHelper::getAll($sql)[0];
			$this->call('authentication.update', $authentication, $expected_error);
			$db_upd_authentication = CDBHelper::getAll($sql)[0];

			$updated = array_intersect_key($authentication, $db_upd_authentication);
			$unchanged = array_diff_key($db_upd_authentication, $authentication);

			// Check if field values have been updated.
			foreach ($updated as $field => $value) {
				if (is_numeric($value)) {
					$this->assertEquals($value, $db_upd_authentication[$field]);
				}
				else {
					$this->assertSame($value, $db_upd_authentication[$field]);
				}
			}

			// Check if fields that were not given, remain the same.
			foreach ($unchanged as $field => $value) {
				if (is_numeric($value)) {
					$this->assertEquals($value, $db_authentication[$field]);
				}
				else {
					$this->assertSame($value, $db_authentication[$field]);
				}
			}
		}
		else {
			// Call method and make sure it really returns the error.
			$this->call('authentication.update', $authentication, $expected_error);
		}
	}

	public static function resolveInstanceData(array $test_data): array {

		foreach (self::TEST_DATA_TO_RESOLVE as $field => $value_not_set) {
			if (array_key_exists($field, $test_data) && $test_data[$field] === $value_not_set) {
				switch ($field) {
					case 'disabled_usrgrpid':
						if (!self::$data['disabled_usrgrpid']) {
							$params = [[
								'name' => 'Disabled user group for API tests',
								'users_status' => GROUP_STATUS_DISABLED
							]];
							$response = CDataHelper::call('usergroup.create', $params);
							self::$data['disabled_usrgrpid'] = reset($response['usrgrpids']);
						}

						$test_data['disabled_usrgrpid'] = self::$data['disabled_usrgrpid'];

						break;
				}
			}
		}

		return $test_data;
	}
}
