<?php declare(strict_types=1);
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


class CControllerTokenUpdate extends CController {

	protected function init(): void {
		$this->setPostContentType(self::POST_CONTENT_TYPE_JSON);
	}

	protected function checkInput() {
		$fields = [
			'tokenid'       => 'db token.tokenid|required|fatal',
			'name'          => 'db token.name|required|not_empty',
			'description'   => 'db token.description',
			'expires_state' => 'in 0,1|required',
			'expires_at'    => 'range_time',
			'status'        => 'db token.status|required|in '.ZBX_AUTH_TOKEN_ENABLED.','.ZBX_AUTH_TOKEN_DISABLED,
			'admin_mode'    => 'required|in 0,1',
			'regenerate'    => 'in 1'
		];

		$ret = $this->validateInput($fields);

		if (!$ret) {
			$this->setResponse(
				new CControllerResponseData(['main_block' => json_encode([
					'error' => [
						'title' => _('Cannot update API token'),
						'messages' => array_column(get_and_clear_messages(), 'message')
					]
				])])
			);
		}

		return $ret;
	}

	protected function checkPermissions() {
		if (CWebUser::isGuest()) {
			return false;
		}

		return $this->checkAccess(CRoleHelper::ACTIONS_MANAGE_API_TOKENS);
	}

	protected function doAction() {
		$this->getInputs($token, ['tokenid', 'name', 'description', 'expires_at', 'status']);

		$token['expires_at'] = $this->getInput('expires_state')
			? (new DateTime($token['expires_at']))->getTimestamp()
			: 0;
		$result = API::Token()->update($token);

		$output = [];

		if ($result) {
			$success = ['title' => _('API token updated')];

			if ($messages = get_and_clear_messages()) {
				$success['messages'] = array_column($messages, 'message');
			}

			$output['success'] = $success;

			if ($this->hasInput('regenerate')) {

				['tokenids' => $tokenids] = $result;
				[['userid' => $userid]] = API::Token()->get([
					'output' => ['userid'],
					'tokenids' => $tokenids
				]);
				[['token' => $auth_token]] = API::Token()->generate($tokenids);

				[$user] = (CWebUser::$data['userid'] != $userid)
					? API::User()->get([
						'output' => ['username', 'name', 'surname'],
						'userids' => $userid
					])
					: [CWebUser::$data];

				$data = [
					'name' => $token['name'],
					'user_name' => getUserFullname($user),
					'auth_token' => $auth_token,
					'expires_at' => $token['expires_at'],
					'description' => $token['description'],
					'status' => $token['status'],
					'message' => _('API token updated'),
					'admin_mode' => $this->getInput('admin_mode')
				];

				$output['data'] = $data;
			}
		}
		else {
			$output['error'] = [
				'title' => _('Cannot update API token'),
				'messages' => array_column(get_and_clear_messages(), 'message')
			];
		}

		$this->setResponse(new CControllerResponseData(['main_block' => json_encode($output)]));
	}
}
