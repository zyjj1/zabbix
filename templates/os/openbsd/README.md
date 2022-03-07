
# OpenBSD by Zabbix agent

## Overview

For Zabbix version: 6.0 and higher  
Official OpenBSD template. Requires agent of Zabbix 6.0 and newer.


## Setup

> See [Zabbix template operation](https://www.zabbix.com/documentation/6.0/manual/config/templates_out_of_the_box/zabbix_agent) for basic instructions.

Install Zabbix agent on OpenBSD according to Zabbix documentation.


## Zabbix configuration

No specific Zabbix configuration is required.

### Macros used

|Name|Description|Default|
|----|-----------|-------|
|{$AGENT.TIMEOUT} |<p>Timeout after which agent is considered unavailable. Works only for agents reachable from Zabbix server/proxy (passive mode).</p> |`3m` |

## Template links

There are no template links in this template.

## Discovery rules

|Name|Description|Type|Key and additional info|
|----|-----------|----|----|
|Mounted filesystem discovery |<p>Discovery of file systems of different types as defined in global regular expression "File systems for discovery".</p> |ZABBIX_PASSIVE |vfs.fs.discovery<p>**Filter**:</p> <p>- {#FSTYPE} MATCHES_REGEX `@File systems for discovery`</p> |
|Network interface discovery |<p>Discovery of network interfaces as defined in global regular expression "Network interfaces for discovery".</p> |ZABBIX_PASSIVE |net.if.discovery<p>**Filter**:</p> <p>- {#IFNAME} MATCHES_REGEX `@Network interfaces for discovery`</p> |

## Items collected

|Group|Name|Description|Type|Key and additional info|
|-----|----|-----------|----|---------------------|
|CPU |Interrupts per second |<p>-</p> |ZABBIX_PASSIVE |system.cpu.intr<p>**Preprocessing**:</p><p>- CHANGE_PER_SECOND: ``</p> |
|CPU |Processor load (1 min average per core) |<p>The processor load is calculated as system CPU load divided by number of CPU cores.</p> |ZABBIX_PASSIVE |system.cpu.load[percpu,avg1] |
|CPU |Processor load (5 min average per core) |<p>The processor load is calculated as system CPU load divided by number of CPU cores.</p> |ZABBIX_PASSIVE |system.cpu.load[percpu,avg5] |
|CPU |Processor load (15 min average per core) |<p>The processor load is calculated as system CPU load divided by number of CPU cores.</p> |ZABBIX_PASSIVE |system.cpu.load[percpu,avg15] |
|CPU |Context switches per second |<p>-</p> |ZABBIX_PASSIVE |system.cpu.switches<p>**Preprocessing**:</p><p>- CHANGE_PER_SECOND: ``</p> |
|CPU |CPU idle time |<p>The time the CPU has spent doing nothing.</p> |ZABBIX_PASSIVE |system.cpu.util[,idle] |
|CPU |CPU interrupt time |<p>The amount of time the CPU has been servicing hardware interrupts.</p> |ZABBIX_PASSIVE |system.cpu.util[,interrupt] |
|CPU |CPU nice time |<p>The time the CPU has spent running users' processes that have been niced.</p> |ZABBIX_PASSIVE |system.cpu.util[,nice] |
|CPU |CPU system time |<p>The time the CPU has spent running the kernel and its processes.</p> |ZABBIX_PASSIVE |system.cpu.util[,system] |
|CPU |CPU user time |<p>The time the CPU has spent running users' processes that are not niced.</p> |ZABBIX_PASSIVE |system.cpu.util[,user] |
|Filesystems |Filesystems: Free inodes on {#FSNAME} (percentage) |<p>-</p> |ZABBIX_PASSIVE |vfs.fs.inode[{#FSNAME},pfree] |
|Filesystems |Filesystems: Free disk space on {#FSNAME} |<p>-</p> |ZABBIX_PASSIVE |vfs.fs.size[{#FSNAME},free] |
|Filesystems |Filesystems: Free disk space on {#FSNAME} (percentage) |<p>-</p> |ZABBIX_PASSIVE |vfs.fs.size[{#FSNAME},pfree] |
|Filesystems |Filesystems: Total disk space on {#FSNAME} |<p>-</p> |ZABBIX_PASSIVE |vfs.fs.size[{#FSNAME},total] |
|Filesystems |Filesystems: Used disk space on {#FSNAME} |<p>-</p> |ZABBIX_PASSIVE |vfs.fs.size[{#FSNAME},used] |
|General |Host boot time |<p>-</p> |ZABBIX_PASSIVE |system.boottime |
|General |Host name |<p>System host name.</p> |ZABBIX_PASSIVE |system.hostname<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `1d`</p> |
|General |Host local time |<p>-</p> |ZABBIX_PASSIVE |system.localtime |
|General |System information |<p>The information as normally returned by 'uname -a'.</p> |ZABBIX_PASSIVE |system.uname<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `1d`</p> |
|General |System uptime |<p>-</p> |ZABBIX_PASSIVE |system.uptime |
|Memory |Free swap space |<p>-</p> |ZABBIX_PASSIVE |system.swap.size[,free] |
|Memory |Free swap space in % |<p>-</p> |ZABBIX_PASSIVE |system.swap.size[,pfree] |
|Memory |Total swap space |<p>-</p> |ZABBIX_PASSIVE |system.swap.size[,total] |
|Memory |Available memory |<p>Available memory is defined as free+cached+buffers memory.</p> |ZABBIX_PASSIVE |vm.memory.size[available] |
|Memory |Total memory |<p>-</p> |ZABBIX_PASSIVE |vm.memory.size[total] |
|Monitoring agent |Version of Zabbix agent running |<p>-</p> |ZABBIX_PASSIVE |agent.version<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `1d`</p> |
|Monitoring agent |Host name of Zabbix agent running |<p>-</p> |ZABBIX_PASSIVE |agent.hostname<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `1d`</p> |
|Monitoring agent |Zabbix agent ping |<p>The agent always returns 1 for this item. It could be used in combination with nodata() for availability check.</p> |ZABBIX_PASSIVE |agent.ping |
|Network interfaces |Network interfaces: Incoming network traffic on {#IFNAME} |<p>-</p> |ZABBIX_PASSIVE |net.if.in[{#IFNAME}]<p>**Preprocessing**:</p><p>- CHANGE_PER_SECOND: ``</p><p>- MULTIPLIER: `8`</p> |
|Network interfaces |Network interfaces: Outgoing network traffic on {#IFNAME} |<p>-</p> |ZABBIX_PASSIVE |net.if.out[{#IFNAME}]<p>**Preprocessing**:</p><p>- CHANGE_PER_SECOND: ``</p><p>- MULTIPLIER: `8`</p> |
|OS |Maximum number of opened files |<p>It could be increased by using sysctl utility or modifying file /etc/sysctl.conf.</p> |ZABBIX_PASSIVE |kernel.maxfiles |
|OS |Maximum number of processes |<p>It could be increased by using sysctl utility or modifying file /etc/sysctl.conf.</p> |ZABBIX_PASSIVE |kernel.maxproc |
|OS |Number of logged in users |<p>Number of users who are currently logged in.</p> |ZABBIX_PASSIVE |system.users.num |
|Processes |Number of running processes |<p>Number of processes in running state.</p> |ZABBIX_PASSIVE |proc.num[,,run] |
|Processes |Number of processes |<p>Total number of processes in any state.</p> |ZABBIX_PASSIVE |proc.num[] |
|Security |Checksum of /etc/passwd |<p>-</p> |ZABBIX_PASSIVE |vfs.file.cksum[/etc/passwd,sha256] |
|Status |Zabbix agent availability |<p>Monitoring agent availability status</p> |INTERNAL |zabbix[host,agent,available] |

## Triggers

|Name|Description|Expression|Severity|Dependencies and additional info|
|----|-----------|----|----|----|
|Processor load is too high on {HOST.NAME} |<p>-</p> |`avg(/OpenBSD by Zabbix agent/system.cpu.load[percpu,avg1],5m)>5` |WARNING | |
|Filesystems: Free inodes is less than 20% on volume {#FSNAME} |<p>-</p> |`last(/OpenBSD by Zabbix agent/vfs.fs.inode[{#FSNAME},pfree])<20` |WARNING | |
|Filesystems: Free disk space is less than 20% on volume {#FSNAME} |<p>-</p> |`last(/OpenBSD by Zabbix agent/vfs.fs.size[{#FSNAME},pfree])<20` |WARNING | |
|Hostname was changed on {HOST.NAME} |<p>-</p> |`last(/OpenBSD by Zabbix agent/system.hostname,#1)<>last(/OpenBSD by Zabbix agent/system.hostname,#2)` |INFO | |
|Host information was changed on {HOST.NAME} |<p>-</p> |`last(/OpenBSD by Zabbix agent/system.uname,#1)<>last(/OpenBSD by Zabbix agent/system.uname,#2)` |INFO | |
|{HOST.NAME} has just been restarted |<p>-</p> |`change(/OpenBSD by Zabbix agent/system.uptime)<0` |INFO | |
|Lack of free swap space on {HOST.NAME} |<p>It probably means that the systems requires more physical memory.</p> |`last(/OpenBSD by Zabbix agent/system.swap.size[,pfree])<50` |WARNING | |
|Lack of available memory on server {HOST.NAME} |<p>-</p> |`last(/OpenBSD by Zabbix agent/vm.memory.size[available])<20M` |AVERAGE | |
|Configured max number of opened files is too low on {HOST.NAME} |<p>-</p> |`last(/OpenBSD by Zabbix agent/kernel.maxfiles)<1024` |INFO | |
|Configured max number of processes is too low on {HOST.NAME} |<p>-</p> |`last(/OpenBSD by Zabbix agent/kernel.maxproc)<256` |INFO | |
|Too many processes running on {HOST.NAME} |<p>-</p> |`avg(/OpenBSD by Zabbix agent/proc.num[,,run],5m)>30` |WARNING | |
|Too many processes on {HOST.NAME} |<p>-</p> |`avg(/OpenBSD by Zabbix agent/proc.num[],5m)>300` |WARNING | |
|/etc/passwd has been changed on {HOST.NAME} |<p>-</p> |`last(/OpenBSD by Zabbix agent/vfs.file.cksum[/etc/passwd,sha256],#1)<>last(/OpenBSD by Zabbix agent/vfs.file.cksum[/etc/passwd,sha256],#2)` |WARNING | |
|Zabbix agent is not available (for {$AGENT.TIMEOUT}) |<p>For passive only agents, host availability is used with {$AGENT.TIMEOUT} as time threshold.</p> |`max(/OpenBSD by Zabbix agent/zabbix[host,agent,available],{$AGENT.TIMEOUT})=0` |AVERAGE |<p>Manual close: YES</p> |

## Feedback

Please report any issues with the template at https://support.zabbix.com

You can also provide feedback, discuss the template or ask for help with it at [ZABBIX forums](https://www.zabbix.com/forum/zabbix-suggestions-and-feedback/).

