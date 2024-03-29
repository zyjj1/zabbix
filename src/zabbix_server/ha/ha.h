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

#ifndef ZABBIX_HA_H
#define ZABBIX_HA_H

#include "zbxrtc.h"
#include "zbx_rtc_constants.h"
#include "zbxipcservice.h"

#define ZBX_IPC_SERVICE_HA			"haservice"

#define ZBX_IPC_SERVICE_HA_PAUSE		1
#define ZBX_IPC_SERVICE_HA_STOP			2
#define ZBX_IPC_SERVICE_HA_STATUS		3
#define ZBX_IPC_SERVICE_HA_GET_NODES		4
#define ZBX_IPC_SERVICE_HA_REMOVE_NODE		5
#define ZBX_IPC_SERVICE_HA_SET_FAILOVER_DELAY	6
#define ZBX_IPC_SERVICE_HA_GET_FAILOVER_DELAY	7
#define ZBX_IPC_SERVICE_HA_LOGLEVEL_INCREASE	8
#define ZBX_IPC_SERVICE_HA_LOGLEVEL_DECREASE	9

#define ZBX_IPC_SERVICE_HA_RTC_FIRST		(ZBX_IPC_RTC_MAX + 1)

#define ZBX_IPC_SERVICE_HA_REGISTER		ZBX_IPC_SERVICE_HA_RTC_FIRST
#define ZBX_IPC_SERVICE_HA_HEARTBEAT		(ZBX_IPC_SERVICE_HA_RTC_FIRST + 1)
#define ZBX_IPC_SERVICE_HA_STATUS_UPDATE	(ZBX_IPC_SERVICE_HA_RTC_FIRST + 2)

#define ZBX_HA_SERVICE_TIMEOUT			10

typedef struct
{
	char	*ha_node_name;
	char	*ha_node_address;
	char	*default_node_ip;
	int	default_node_port;
	int	ha_status;
}
zbx_ha_config_t;

void	zbx_init_library_ha(void);

int	zbx_ha_start(zbx_rtc_t *rtc, zbx_ha_config_t *ha_config, char **error);
int	zbx_ha_pause(char **error);
int	zbx_ha_stop(char **error);
void	zbx_ha_kill(void);
int	zbx_ha_get_status(const char *ha_node_name, int *ha_status, int *ha_failover_delay, char **error);
int	zbx_ha_dispatch_message(const char *ha_node_name, zbx_ipc_message_t *message, int *ha_status,
		int *ha_failover_delay, char **error);

int	zbx_ha_get_nodes(char **nodes, char **error);
int	zbx_ha_remove_node(const char *node, char **result, char **error);
int	zbx_ha_set_failover_delay(int delay, char **error);
int	zbx_ha_get_failover_delay(int *delay, char **error);
int	zbx_ha_change_loglevel(int direction, char **error);
const char	*zbx_ha_status_str(int ha_status);

#endif
