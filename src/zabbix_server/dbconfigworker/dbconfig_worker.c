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

#include "zbxdbconfigworker.h"
#include "zbx_host_constants.h"

#include "zbxlog.h"
#include "zbxself.h"
#include "zbxipcservice.h"
#include "zbxnix.h"
#include "zbxnum.h"
#include "zbxtime.h"
#include "zbxcacheconfig.h"
#include "zbxalgo.h"
#include "zbxdbhigh.h"
#include "zbxtypes.h"

static void	dbsync_item_rtname(zbx_vector_uint64_t *hostids, int *processed_num, int *updated_num,
		int *macro_used)
{
#define ZBX_DBCONFIG_BATCH_SIZE	1000
	zbx_db_result_t		result;
	zbx_db_row_t		row;
	zbx_dc_um_handle_t	*um_handle;
	char			*sql = NULL;
	size_t			sql_alloc = 0, sql_offset = 0;
	char			*sql_select = NULL;
	size_t			sql_select_alloc = 0;

	zabbix_log(LOG_LEVEL_DEBUG, "In %s()", __func__);

	if (0 == hostids->values[0])
	{
		if (0 == *macro_used)
			zbx_vector_uint64_remove(hostids, 0);
		else
			hostids->values_num = 1;

		if (0 == hostids->values_num)
			goto out;
	}

	um_handle = zbx_dc_open_user_macros();

	zbx_db_begin();
	zbx_db_begin_multiple_update(&sql, &sql_alloc, &sql_offset);

	for (zbx_uint64_t *batch = hostids->values; batch < hostids->values + hostids->values_num;
			batch += ZBX_DBCONFIG_BATCH_SIZE)
	{
		int	batch_size = MIN(ZBX_DBCONFIG_BATCH_SIZE, hostids->values + hostids->values_num - batch);
		size_t	sql_select_offset = 0;

		zbx_strcpy_alloc(&sql_select, &sql_select_alloc, &sql_select_offset,
				"select i.itemid,i.hostid,i.name,ir.name_resolved"
				" from items i,item_rtname ir"
				" where i.name like '%%{$%%' and ir.itemid=i.itemid");
		if (0 != batch[0])
		{
			zbx_strcpy_alloc(&sql_select, &sql_select_alloc, &sql_select_offset, " and");
			zbx_db_add_condition_alloc(&sql_select, &sql_select_alloc, &sql_select_offset, "i.hostid",
					batch, batch_size);
		}
		zbx_strcpy_alloc(&sql_select, &sql_select_alloc, &sql_select_offset, " order by i.itemid");

		result = zbx_db_select("%s", sql_select);

		zabbix_log(LOG_LEVEL_DEBUG, "fetch started");

		while (NULL != (row = zbx_db_fetch(result)))
		{
			zbx_uint64_t	hostid;
			char		*name;

			(*processed_num)++;

			ZBX_STR2UINT64(hostid, row[1]);
			name = zbx_strdup(NULL, row[2]);

			(void)zbx_dc_expand_user_and_func_macros(um_handle, &name, &hostid, 1, NULL);

			if (0 != strcmp(row[3], name))
			{
				char	*name_esc;

				name_esc = zbx_db_dyn_escape_string(name);

				zbx_snprintf_alloc(&sql, &sql_alloc, &sql_offset, "update item_rtname set"
						" name_resolved='%s',name_resolved_upper=upper('%s')"
						" where itemid=%s;\n",
						name_esc, name_esc, row[0]);
				zbx_db_execute_overflowed_sql(&sql, &sql_alloc, &sql_offset);

				zbx_free(name_esc);
				(*updated_num)++;
			}

			zbx_free(name);
		}
		zbx_db_free_result(result);
	}

	zbx_db_end_multiple_update(&sql, &sql_alloc, &sql_offset);

	if (sql_offset > 16)	/* In ORACLE always present begin..end; */
		zbx_db_execute("%s", sql);

	zbx_free(sql_select);
	zbx_free(sql);
	zbx_dc_close_user_macros(um_handle);
	zbx_db_commit();

	if (0 == hostids->values[0] || 0 != *processed_num)
		*macro_used = *processed_num;
out:
	zabbix_log(LOG_LEVEL_DEBUG, "End of %s()", __func__);
#undef ZBX_DBCONFIG_BATCH_SIZE
}

ZBX_THREAD_ENTRY(zbx_dbconfig_worker_thread, args)
{
#define ZBX_DBCONFIG_WORKER_DELAY		1
	zbx_ipc_service_t	service;
	char			*error = NULL;
	zbx_ipc_client_t	*client;
	zbx_ipc_message_t	*message;
	int			processed_num = 0, updated_num = 0, macro_used = 1;
	double			sec = 0;
	zbx_timespec_t		timeout = {ZBX_DBCONFIG_WORKER_DELAY, 0};
	const zbx_thread_info_t	*info = &((zbx_thread_args_t *)args)->info;
	int			server_num = ((zbx_thread_args_t *)args)->info.server_num;
	int			process_num = ((zbx_thread_args_t *)args)->info.process_num;
	unsigned char		process_type = ((zbx_thread_args_t *)args)->info.process_type;
	zbx_vector_uint64_t	hostids;

	zabbix_log(LOG_LEVEL_INFORMATION, "%s #%d started [%s #%d]", get_program_type_string(info->program_type),
				server_num, get_process_type_string(process_type), process_num);

	zbx_setproctitle("%s [connecting to the database]", get_process_type_string(process_type));
	zbx_db_connect(ZBX_DB_CONNECT_NORMAL);

	if (FAIL == zbx_ipc_service_start(&service, ZBX_IPC_SERVICE_DBCONFIG_WORKER, &error))
	{
		zabbix_log(LOG_LEVEL_CRIT, "cannot start %s service: %s", get_process_type_string(process_type), error);
		zbx_free(error);
		exit(EXIT_FAILURE);
	}

	zbx_setproctitle("%s #%d started", get_process_type_string(process_type), process_num);

	zbx_vector_uint64_create(&hostids);
	zbx_vector_uint64_append(&hostids, 0);

	while (ZBX_IS_RUNNING())
	{
		double	time_now;

		zbx_update_selfmon_counter(info, ZBX_PROCESS_STATE_IDLE);
		(void)zbx_ipc_service_recv(&service, &timeout, &client, &message);
		zbx_update_selfmon_counter(info, ZBX_PROCESS_STATE_BUSY);

		if (NULL != message)
		{
			switch (message->code)
			{
				case ZBX_IPC_DBCONFIG_WORKER_REQUEST:
					zbx_dbconfig_worker_deserialize_ids(message->data, message->size, &hostids);
					break;
				default:
					THIS_SHOULD_NEVER_HAPPEN;
			}

			zbx_ipc_message_free(message);
		}

		if (NULL != client)
			zbx_ipc_client_release(client);

		time_now = zbx_time();

		zbx_update_env(get_process_type_string(process_type), time_now);

		if (0 != hostids.values_num)
		{
			zbx_setproctitle("%s [synced %d, updated %d item names in " ZBX_FS_DBL " sec, syncing]",
					get_process_type_string(process_type), processed_num, updated_num, sec);
			processed_num = 0;
			updated_num = 0;
			sec = time_now;

			zbx_vector_uint64_sort(&hostids, ZBX_DEFAULT_UINT64_COMPARE_FUNC);
			zbx_vector_uint64_uniq(&hostids, ZBX_DEFAULT_UINT64_COMPARE_FUNC);

			dbsync_item_rtname(&hostids, &processed_num, &updated_num, &macro_used);

			zbx_vector_uint64_clear(&hostids);

			sec = zbx_time() - sec;

			zbx_setproctitle("%s [synced %d, updated %d item names in " ZBX_FS_DBL " sec, idle]",
					get_process_type_string(process_type), processed_num, updated_num, sec);
		}
	}

	zbx_vector_uint64_destroy(&hostids);
	zbx_ipc_service_close(&service);

	exit(EXIT_SUCCESS);
#undef ZBX_DBCONFIG_WORKER_DELAY
}
