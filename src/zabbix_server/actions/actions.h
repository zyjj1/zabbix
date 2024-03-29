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

#ifndef ZABBIX_ACTIONS_H
#define ZABBIX_ACTIONS_H

#include "zbxdbhigh.h"
#include "zbxcacheconfig.h"
#include "zbxalgo.h"

#define ZBX_ACTION_RECOVERY_NONE	0
#define ZBX_ACTION_RECOVERY_OPERATIONS	1

typedef struct
{
	zbx_uint64_t	eventid;
	zbx_uint64_t	acknowledgeid;
	zbx_uint64_t	taskid;
	int		old_severity;
	int		new_severity;
}
zbx_ack_task_t;

ZBX_PTR_VECTOR_DECL(ack_task_ptr, zbx_ack_task_t *)
ZBX_PTR_VECTOR_DECL(db_action_ptr, zbx_db_action*)

void	zbx_ack_task_free(zbx_ack_task_t *ack_task);

int	check_action_condition(zbx_db_event *event, zbx_condition_t *condition);
void	process_actions(zbx_vector_db_event_t *events, const zbx_vector_uint64_pair_t *closed_events);
int	process_actions_by_acknowledgments(const zbx_vector_ack_task_ptr_t *ack_tasks);
void	get_db_actions_info(zbx_vector_uint64_t *actionids, zbx_vector_db_action_ptr_t *actions);
void	free_db_action(zbx_db_action *action);

#endif
