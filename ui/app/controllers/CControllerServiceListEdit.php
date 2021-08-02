<?php declare(strict_types = 1);
/*
** Zabbix
** Copyright (C) 2001-2021 Zabbix SIA
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


class CControllerServiceListEdit extends CControllerServiceListGeneral {

	protected function init(): void {
		$this->disableSIDValidation();
	}

	protected function checkInput(): bool {
		$fields = [
			'uncheck' =>						'in 1',
			'serviceid' =>						'db services.serviceid',
			'path' =>							'array',
			'filter_name' =>					'string',
			'filter_status' =>					'in '.implode(',', [SERVICE_STATUS_ANY, SERVICE_STATUS_OK, SERVICE_STATUS_PROBLEM]),
			'filter_without_children' =>		'in 0,1',
			'filter_without_problem_tags' =>	'in 0,1',
			'filter_tag_source' =>				'in '.implode(',', [ZBX_SERVICE_FILTER_TAGS_ANY, ZBX_SERVICE_FILTER_TAGS_SERVICE, ZBX_SERVICE_FILTER_TAGS_PROBLEM]),
			'filter_evaltype' =>				'in '.TAG_EVAL_TYPE_AND_OR.','.TAG_EVAL_TYPE_OR,
			'filter_tags' =>					'array',
			'page' =>							'ge 1'
		];

		$ret = $this->validateInput($fields);

		if (!$ret) {
			$this->setResponse(new CControllerResponseFatal());
		}

		return $ret;
	}

	protected function checkPermissions(): bool {
		return $this->checkAccess(CRoleHelper::UI_MONITORING_SERVICES)
			&& $this->checkAccess(CRoleHelper::ACTIONS_MANAGE_SERVICES);
	}

	protected function doAction(): void {
		parent::doAction();

		$profile_serviceid = $this->service !== null
			? $this->service['serviceid']
			: (string) self::WITHOUT_PARENTS_SERVICEID;

		$uncheck = $this->hasInput('uncheck') || CProfile::get('web.service.list.serviceid') !== $profile_serviceid;

		CProfile::update('web.service.list.serviceid', $profile_serviceid, PROFILE_TYPE_ID);

		$path = $this->getPath();

		$filter = [
			'serviceid' => $this->service !== null ? $this->service['serviceid'] : self::WITHOUT_PARENTS_SERVICEID,
			'name' => $this->getInput('filter_name', self::FILTER_DEFAULT_NAME),
			'status' => $this->getInput('filter_status', self::FILTER_DEFAULT_STATUS),
			'without_children' => (bool) $this->getInput('filter_without_children',
				self::FILTER_DEFAULT_WITHOUT_CHILDREN ? 1 : 0
			),
			'without_problem_tags' => (bool) $this->getInput('filter_without_problem_tags',
				self::FILTER_DEFAULT_WITHOUT_PROBLEM_TAGS ? 1 : 0
			),
			'tag_source' => $this->getInput('filter_tag_source', self::FILTER_DEFAULT_TAG_SOURCE),
			'evaltype' => $this->getInput('filter_evaltype', self::FILTER_DEFAULT_EVALTYPE),
			'tags' => []
		];

		foreach ($this->getInput('filter_tags', []) as $tag) {
			if (!array_key_exists('tag', $tag) || !array_key_exists('value', $tag)
				|| ($tag['tag'] === '' && $tag['value'] === '')) {
				continue;
			}
			$filter['tags'][] = $tag;
		}

		$is_filtered = !$this->isDefaultFilter($filter);

		$reset_curl = (new CUrl('zabbix.php'))
			->setArgument('action', 'service.list.edit')
			->setArgument('path', $path ?: null)
			->setArgument('serviceid', $this->service !== null ? $this->service['serviceid'] : null);

		$paging_curl = clone $reset_curl;

		if ($is_filtered) {
			$paging_curl
				->setArgument('filter_name', $filter['name'])
				->setArgument('filter_status', $filter['status'])
				->setArgument('filter_without_children', $filter['without_children'] ? 1 : 0)
				->setArgument('filter_without_problem_tags', $filter['without_problem_tags'] ? 1 : 0)
				->setArgument('filter_tag_source', $filter['tag_source'])
				->setArgument('filter_evaltype', $filter['evaltype'])
				->setArgument('filter_tags', $filter['tags']);
		}

		$view_mode_curl = (clone $paging_curl)
			->setArgument('action', 'service.list')
			->removeArgument('filter_without_children')
			->removeArgument('filter_without_problem_tags')
			->removeArgument('filter_tag_source');

		$back_curl = (clone $paging_curl)
			->setArgument('action', 'service.list.edit')
			->setArgument('page', $this->hasInput('page') ? $this->getInput('page') : null);

		$refresh_curl = (clone $paging_curl)
			->setArgument('action', 'service.list.edit.refresh')
			->setArgument('page', $this->hasInput('page') ? $this->getInput('page') : null);

		$data = [
			'can_monitor_problems' => CWebUser::checkAccess(CRoleHelper::UI_MONITORING_PROBLEMS),
			'uncheck' => $uncheck,
			'path' => $path,
			'breadcrumbs' => $this->getBreadcrumbs($path, $is_filtered),
			'filter' => $filter,
			'is_filtered' => $is_filtered,
			'active_tab' => CProfile::get('web.service.filter.active', 1),
			'reset_curl' => $reset_curl,
			'view_mode_url' => $view_mode_curl->getUrl(),
			'back_url' => $back_curl->getUrl(),
			'refresh_url' => $refresh_curl->getUrl(),
			'refresh_interval' => CWebUser::getRefresh() * 1000,
			'max_in_table' => CSettingsHelper::get(CSettingsHelper::MAX_IN_TABLE),
			'service' => $this->service
		];

		$db_serviceids = $this->prepareData($filter, $is_filtered);

		$page_num = $this->getInput('page', 1);
		CPagerHelper::savePage('service.list.edit', $page_num);
		$data['paging'] = CPagerHelper::paginate($page_num, $db_serviceids, ZBX_SORT_UP, $paging_curl);

		$data['services'] = API::Service()->get([
			'output' => ['serviceid', 'name', 'status', 'goodsla', 'showsla'],
			'selectParents' => $is_filtered ? ['serviceid', 'name'] : null,
			'selectChildren' => API_OUTPUT_COUNT,
			'selectTags' => ['tag', 'value'],
			'serviceids' => $db_serviceids,
			'sortfield' => ['sortorder', 'name'],
			'sortorder' => ZBX_SORT_UP,
			'preservekeys' => true
		]);

		$data['events'] = $this->getProblemEvents($db_serviceids);

		$data['tags'] = makeTags($data['services'], true, 'serviceid', ZBX_TAG_COUNT_DEFAULT, $filter['tags']);

		$response = new CControllerResponseData($data);
		$response->setTitle(_('Services'));
		$this->setResponse($response);
	}
}
