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

#ifndef ZABBIX_ZBX_SCRIPTS_CONSTANTS_H
#define ZABBIX_ZBX_SCRIPTS_CONSTANTS_H

#define ZBX_SCRIPT_TYPE_CUSTOM_SCRIPT	0
#define ZBX_SCRIPT_TYPE_IPMI		1
#define ZBX_SCRIPT_TYPE_SSH		2
#define ZBX_SCRIPT_TYPE_TELNET		3
#define ZBX_SCRIPT_TYPE_WEBHOOK		5

#define ZBX_SCRIPT_SCOPE_ACTION		1
#define ZBX_SCRIPT_SCOPE_HOST		2
#define ZBX_SCRIPT_SCOPE_EVENT		4

#define ZBX_SCRIPT_EXECUTE_ON_AGENT	0
#define ZBX_SCRIPT_EXECUTE_ON_SERVER	1
#define ZBX_SCRIPT_EXECUTE_ON_PROXY	2	/* fall back to execution on server if target not monitored by proxy */

#define ZBX_SCRIPT_MANUALINPUT_NO	0
#define ZBX_SCRIPT_MANUALINPUT_YES	1

#define ZBX_SCRIPT_MANUALINPUT_VALIDATOR_TYPE_REGEX	0
#define ZBX_SCRIPT_MANUALINPUT_VALIDATOR_TYPE_LIST	1

#endif
