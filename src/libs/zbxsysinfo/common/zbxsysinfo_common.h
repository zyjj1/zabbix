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

#ifndef ZABBIX_SYSINFO_COMMON_H
#define ZABBIX_SYSINFO_COMMON_H

#include "module.h"

zbx_metric_t	*get_parameters_common(void);
zbx_metric_t	*get_parameters_common_local(void);

int	execute_user_parameter(AGENT_REQUEST *request, AGENT_RESULT *result);
int	execute_str(const char *command, AGENT_RESULT *result, int timeout);
int	execute_dbl(const char *command, AGENT_RESULT *result, int timeout);
int	execute_int(const char *command, AGENT_RESULT *result, int timeout);

#endif
