/*
** Zabbix
** Copyright (C) 2001-2023 Zabbix SIA
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

#ifndef ZABBIX_DISCOVERER_TASKPREP_H_
#define ZABBIX_DISCOVERER_TASKPREP_H_

#include "discoverer_int.h"

void	process_rule(zbx_dc_drule_t *drule, zbx_uint64_t *queue_capacity, zbx_hashset_t *tasks,
		zbx_hashset_t *check_counts, zbx_vector_dc_dcheck_ptr_t *dchecks_common, zbx_vector_iprange_t *ipranges);

#endif /* ZABBIX_DISCOVERER_TASKPREP_H_ */
