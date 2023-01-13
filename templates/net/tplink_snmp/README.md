
# TP-LINK by SNMP

## Overview

For Zabbix version: 6.4 and higher.
Link to MIBs: https://www.tp-link.com/en/support/download/t2600g-28ts/#MIBs_Files
Sample device overview page: https://www.tp-link.com/en/business-networking/managed-switch/t2600g-28ts/#overview
Emulation page (web): https://emulator.tp-link.com/T2600G-28TS(UN)_1.0/Index.htm


This template was tested on:

- T2600G-28TS revision 2.0, version 2.0.0 Build 20170628 Rel.55184 (Beta)

## Setup

Refer to the vendor documentation.

## Zabbix configuration

No specific Zabbix configuration is required.

### Macros used

|Name|Description|Default|
|----|-----------|-------|
|{$CPU.UTIL.CRIT} |<p>-</p> |`90` |
|{$ICMP_LOSS_WARN} |<p>-</p> |`20` |
|{$ICMP_RESPONSE_TIME_WARN} |<p>-</p> |`0.15` |
|{$IF.ERRORS.WARN} |<p>-</p> |`2` |
|{$IF.UTIL.MAX} |<p>-</p> |`95` |
|{$IFCONTROL} |<p>-</p> |`1` |
|{$MEMORY.UTIL.MAX} |<p>-</p> |`90` |
|{$NET.IF.IFADMINSTATUS.MATCHES} |<p>Ignore notPresent(6)</p> |`^.*` |
|{$NET.IF.IFADMINSTATUS.NOT_MATCHES} |<p>Ignore down(2) administrative status</p> |`^2$` |
|{$NET.IF.IFDESCR.MATCHES} |<p>-</p> |`.*` |
|{$NET.IF.IFDESCR.NOT_MATCHES} |<p>-</p> |`CHANGE_IF_NEEDED` |
|{$NET.IF.IFNAME.MATCHES} |<p>-</p> |`^.*$` |
|{$NET.IF.IFNAME.NOT_MATCHES} |<p>Filter out loopbacks, nulls, docker veth links and docker0 bridge by default</p> |`(^Software Loopback Interface|^NULL[0-9.]*$|^[Ll]o[0-9.]*$|^[Ss]ystem$|^Nu[0-9.]*$|^veth[0-9a-z]+$|docker[0-9]+|br-[a-z0-9]{12})` |
|{$NET.IF.IFOPERSTATUS.MATCHES} |<p>-</p> |`^.*$` |
|{$NET.IF.IFOPERSTATUS.NOT_MATCHES} |<p>Ignore notPresent(6)</p> |`^6$` |
|{$NET.IF.IFTYPE.MATCHES} |<p>-</p> |`.*` |
|{$NET.IF.IFTYPE.NOT_MATCHES} |<p>-</p> |`CHANGE_IF_NEEDED` |
|{$SNMP.TIMEOUT} |<p>-</p> |`5m` |

## Template links

There are no template links in this template.

## Discovery rules

|Name|Description|Type|Key and additional info|
|----|-----------|----|----|
|CPU Discovery |<p>Discovering TPLINK-SYSMONITOR-MIB::tpSysMonitorCpuTable, displays the CPU utilization of all UNITs.</p> |SNMP |cpu.discovery |
|Memory Discovery |<p>Discovering TPLINK-SYSMONITOR-MIB::tpSysMonitorMemoryTable, displays the memory utilization of all UNITs.</p> |SNMP |memory.discovery |
|Network interfaces discovery |<p>Discovering interfaces from IF-MIB.</p> |SNMP |net.if.discovery<p>**Filter**:</p>AND <p>- {#IFADMINSTATUS} MATCHES_REGEX `{$NET.IF.IFADMINSTATUS.MATCHES}`</p><p>- {#IFADMINSTATUS} NOT_MATCHES_REGEX `{$NET.IF.IFADMINSTATUS.NOT_MATCHES}`</p><p>- {#IFOPERSTATUS} MATCHES_REGEX `{$NET.IF.IFOPERSTATUS.MATCHES}`</p><p>- {#IFOPERSTATUS} NOT_MATCHES_REGEX `{$NET.IF.IFOPERSTATUS.NOT_MATCHES}`</p><p>- {#IFNAME} MATCHES_REGEX `{$NET.IF.IFNAME.MATCHES}`</p><p>- {#IFNAME} NOT_MATCHES_REGEX `{$NET.IF.IFNAME.NOT_MATCHES}`</p><p>- {#IFDESCR} MATCHES_REGEX `{$NET.IF.IFDESCR.MATCHES}`</p><p>- {#IFDESCR} NOT_MATCHES_REGEX `{$NET.IF.IFDESCR.NOT_MATCHES}`</p><p>- {#IFTYPE} MATCHES_REGEX `{$NET.IF.IFTYPE.MATCHES}`</p><p>- {#IFTYPE} NOT_MATCHES_REGEX `{$NET.IF.IFTYPE.NOT_MATCHES}`</p> |

## Items collected

|Group|Name|Description|Type|Key and additional info|
|-----|----|-----------|----|---------------------|
|CPU |#{#SNMPVALUE}: CPU utilization |<p>MIB: TPLINK-SYSMONITOR-MIB</p><p>Displays the CPU utilization in 1 minute.</p><p>Reference: http://www.tp-link.com/faq-1330.html</p> |SNMP |system.cpu.util[tpSysMonitorCpu1Minute.{#SNMPINDEX}] |
|General |SNMP traps (fallback) |<p>The item is used to collect all SNMP traps unmatched by other snmptrap items</p> |SNMP_TRAP |snmptrap.fallback |
|General |System location |<p>MIB: SNMPv2-MIB</p><p>The physical location of this node (e.g., `telephone closet, 3rd floor').  If the location is unknown, the value is the zero-length string.</p> |SNMP |system.location[sysLocation.0]<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `12h`</p> |
|General |System contact details |<p>MIB: SNMPv2-MIB</p><p>The textual identification of the contact person for this managed node, together with information on how to contact this person.  If no contact information is known, the value is the zero-length string.</p> |SNMP |system.contact[sysContact.0]<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `12h`</p> |
|General |System object ID |<p>MIB: SNMPv2-MIB</p><p>The vendor's authoritative identification of the network management subsystem contained in the entity.  This value is allocated within the SMI enterprises subtree (1.3.6.1.4.1) and provides an easy and unambiguous means for determining`what kind of box' is being managed.  For example, if vendor`Flintstones, Inc.' was assigned the subtree1.3.6.1.4.1.4242, it could assign the identifier 1.3.6.1.4.1.4242.1.1 to its `Fred Router'.</p> |SNMP |system.objectid[sysObjectID.0]<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `12h`</p> |
|General |System name |<p>MIB: SNMPv2-MIB</p><p>An administratively-assigned name for this managed node.By convention, this is the node's fully-qualified domain name.  If the name is unknown, the value is the zero-length string.</p> |SNMP |system.name<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `12h`</p> |
|General |System description |<p>MIB: SNMPv2-MIB</p><p>A textual description of the entity. This value should</p><p>include the full name and version identification of the system's hardware type, software operating-system, and</p><p>networking software.</p> |SNMP |system.descr[sysDescr.0]<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `12h`</p> |
|Inventory |Hardware model name |<p>MIB: TPLINK-SYSINFO-MIB</p><p>The hardware version of the product.</p> |SNMP |system.hw.model<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `1d`</p> |
|Inventory |Hardware serial number |<p>MIB: TPLINK-SYSINFO-MIB</p><p>The Serial number of the product.</p> |SNMP |system.hw.serialnumber<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `1d`</p> |
|Inventory |Firmware version |<p>MIB: TPLINK-SYSINFO-MIB</p><p>The software version of the product.</p> |SNMP |system.hw.firmware<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `1d`</p> |
|Inventory |Hardware version(revision) |<p>MIB: TPLINK-SYSINFO-MIB</p><p>The hardware version of the product.</p> |SNMP |system.hw.version<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `1d`</p> |
|Memory |#{#SNMPVALUE}: Memory utilization |<p>MIB: TPLINK-SYSMONITOR-MIB</p><p>Displays the memory utilization.</p><p>Reference: http://www.tp-link.com/faq-1330.html</p> |SNMP |vm.memory.util[tpSysMonitorMemoryUtilization.{#SNMPINDEX}] |
|Network interfaces |Interface {#IFDESCR}: Operational status |<p>MIB: IF-MIB</p><p>The current operational state of the interface.</p><p>- The testing(3) state indicates that no operational packet scan be passed</p><p>- If ifAdminStatus is down(2) then ifOperStatus should be down(2)</p><p>- If ifAdminStatus is changed to up(1) then ifOperStatus should change to up(1) if the interface is ready to transmit and receive network traffic</p><p>- It should change todormant(5) if the interface is waiting for external actions (such as a serial line waiting for an incoming connection)</p><p>- It should remain in the down(2) state if and only if there is a fault that prevents it from going to the up(1) state</p><p>- It should remain in the notPresent(6) state if the interface has missing(typically, hardware) components.</p> |SNMP |net.if.status[ifOperStatus.{#SNMPINDEX}] |
|Network interfaces |Interface {#IFDESCR}: Bits received |<p>MIB: IF-MIB</p><p>The total number of octets received on the interface,including framing characters. Discontinuities in the value of this counter can occur at re-initialization of the management system, and atother times as indicated by the value of ifCounterDiscontinuityTime.</p> |SNMP |net.if.in[ifInOctets.{#SNMPINDEX}]<p>**Preprocessing**:</p><p>- CHANGE_PER_SECOND</p><p>- MULTIPLIER: `8`</p> |
|Network interfaces |Interface {#IFDESCR}: Bits sent |<p>MIB: IF-MIB</p><p>The total number of octets transmitted out of the interface, including framing characters. Discontinuities in the value of this counter can occur at re-initialization of the management system, and at other times as indicated by the value of ifCounterDiscontinuityTime.</p> |SNMP |net.if.out[ifOutOctets.{#SNMPINDEX}]<p>**Preprocessing**:</p><p>- CHANGE_PER_SECOND</p><p>- MULTIPLIER: `8`</p> |
|Network interfaces |Interface {#IFDESCR}: Inbound packets with errors |<p>MIB: IF-MIB</p><p>For packet-oriented interfaces, the number of inbound packets that contained errors preventing them from being deliverable to a higher-layer protocol.  For character-oriented or fixed-length interfaces, the number of inbound transmission units that contained errors preventing them from being deliverable to a higher-layer protocol. Discontinuities in the value of this counter can occur at re-initialization of the management system, and at other times as indicated by the value of ifCounterDiscontinuityTime.</p> |SNMP |net.if.in.errors[ifInErrors.{#SNMPINDEX}]<p>**Preprocessing**:</p><p>- CHANGE_PER_SECOND</p> |
|Network interfaces |Interface {#IFDESCR}: Outbound packets with errors |<p>MIB: IF-MIB</p><p>For packet-oriented interfaces, the number of outbound packets that contained errors preventing them from being deliverable to a higher-layer protocol.  For character-oriented or fixed-length interfaces, the number of outbound transmission units that contained errors preventing them from being deliverable to a higher-layer protocol. Discontinuities in the value of this counter can occur at re-initialization of the management system, and at other times as indicated by the value of ifCounterDiscontinuityTime.</p> |SNMP |net.if.out.errors[ifOutErrors.{#SNMPINDEX}]<p>**Preprocessing**:</p><p>- CHANGE_PER_SECOND</p> |
|Network interfaces |Interface {#IFDESCR}: Outbound packets discarded |<p>MIB: IF-MIB</p><p>The number of outbound packets which were chosen to be discarded</p><p>even though no errors had been detected to prevent their being deliverable to a higher-layer protocol.</p><p>One possible reason for discarding such a packet could be to free up buffer space.</p><p>Discontinuities in the value of this counter can occur at re-initialization of the management system,</p><p>and at other times as indicated by the value of ifCounterDiscontinuityTime.</p> |SNMP |net.if.out.discards[ifOutDiscards.{#SNMPINDEX}]<p>**Preprocessing**:</p><p>- CHANGE_PER_SECOND</p> |
|Network interfaces |Interface {#IFDESCR}: Inbound packets discarded |<p>MIB: IF-MIB</p><p>The number of inbound packets which were chosen to be discarded</p><p>even though no errors had been detected to prevent their being deliverable to a higher-layer protocol.</p><p>One possible reason for discarding such a packet could be to free up buffer space.</p><p>Discontinuities in the value of this counter can occur at re-initialization of the management system,</p><p>and at other times as indicated by the value of ifCounterDiscontinuityTime.</p> |SNMP |net.if.in.discards[ifInDiscards.{#SNMPINDEX}]<p>**Preprocessing**:</p><p>- CHANGE_PER_SECOND</p> |
|Network interfaces |Interface {#IFDESCR}: Interface type |<p>MIB: IF-MIB</p><p>The type of interface.</p><p>Additional values for ifType are assigned by the Internet Assigned Numbers Authority (IANA),</p><p>through updating the syntax of the IANAifType textual convention.</p> |SNMP |net.if.type[ifType.{#SNMPINDEX}]<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `1d`</p> |
|Network interfaces |Interface {#IFDESCR}: Speed |<p>MIB: IF-MIB</p><p>An estimate of the interface's current bandwidth in bits per second.</p><p>For interfaces which do not vary in bandwidth or for those where no accurate estimation can be made,</p><p>this object should contain the nominal bandwidth.</p><p>If the bandwidth of the interface is greater than the maximum value reportable by this object then</p><p>this object should report its maximum value (4,294,967,295) and ifHighSpeed must be used to report the interface's speed.</p><p>For a sub-layer which has no concept of bandwidth, this object should be zero.</p> |SNMP |net.if.speed[ifSpeed.{#SNMPINDEX}] |
|Status |Uptime (network) |<p>MIB: SNMPv2-MIB</p><p>The time (in hundredths of a second) since the network management portion of the system was last re-initialized.</p> |SNMP |system.net.uptime[sysUpTime.0]<p>**Preprocessing**:</p><p>- MULTIPLIER: `0.01`</p> |
|Status |Uptime (hardware) |<p>MIB: HOST-RESOURCES-MIB</p><p>The amount of time since this host was last initialized. Note that this is different from sysUpTime in the SNMPv2-MIB [RFC1907] because sysUpTime is the uptime of the network management portion of the system.</p> |SNMP |system.hw.uptime[hrSystemUptime.0]<p>**Preprocessing**:</p><p>- CHECK_NOT_SUPPORTED</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> 0`</p><p>- MULTIPLIER: `0.01`</p> |
|Status |SNMP agent availability |<p>Availability of SNMP checks on the host. The value of this item corresponds to availability icons in the host list.</p><p>Possible value:</p><p>0 - not available</p><p>1 - available</p><p>2 - unknown</p> |INTERNAL |zabbix[host,snmp,available] |
|Status |ICMP ping |<p>-</p> |SIMPLE |icmpping |
|Status |ICMP loss |<p>-</p> |SIMPLE |icmppingloss |
|Status |ICMP response time |<p>-</p> |SIMPLE |icmppingsec |

## Triggers

|Name|Description|Expression|Severity|Dependencies and additional info|
|----|-----------|----|----|----|
|#{#SNMPVALUE}: High CPU utilization |<p>CPU utilization is too high. The system might be slow to respond.</p> |`min(/TP-LINK by SNMP/system.cpu.util[tpSysMonitorCpu1Minute.{#SNMPINDEX}],5m)>{$CPU.UTIL.CRIT}` |WARNING | |
|System name has changed |<p>System name has changed. Ack to close.</p> |`last(/TP-LINK by SNMP/system.name,#1)<>last(/TP-LINK by SNMP/system.name,#2) and length(last(/TP-LINK by SNMP/system.name))>0` |INFO |<p>Manual close: YES</p> |
|Device has been replaced |<p>Device serial number has changed. Ack to close</p> |`last(/TP-LINK by SNMP/system.hw.serialnumber,#1)<>last(/TP-LINK by SNMP/system.hw.serialnumber,#2) and length(last(/TP-LINK by SNMP/system.hw.serialnumber))>0` |INFO |<p>Manual close: YES</p> |
|Firmware has changed |<p>Firmware version has changed. Ack to close</p> |`last(/TP-LINK by SNMP/system.hw.firmware,#1)<>last(/TP-LINK by SNMP/system.hw.firmware,#2) and length(last(/TP-LINK by SNMP/system.hw.firmware))>0` |INFO |<p>Manual close: YES</p> |
|#{#SNMPVALUE}: High memory utilization |<p>The system is running out of free memory.</p> |`min(/TP-LINK by SNMP/vm.memory.util[tpSysMonitorMemoryUtilization.{#SNMPINDEX}],5m)>{$MEMORY.UTIL.MAX}` |AVERAGE | |
|Interface {#IFDESCR}: Link down |<p>This trigger expression works as follows:</p><p>1. Can be triggered if operations status is down.</p><p>2. {$IFCONTROL:"{#IFNAME}"}=1 - user can redefine Context macro to value - 0. That marks this interface as not important. No new trigger will be fired if this interface is down.</p><p>3. {TEMPLATE_NAME:METRIC.diff()}=1) - trigger fires only if operational status was up(1) sometime before. (So, do not fire 'ethernal off' interfaces.)</p><p>WARNING: if closed manually - won't fire again on next poll, because of .diff.</p> |`{$IFCONTROL:"{#IFNAME}"}=1 and last(/TP-LINK by SNMP/net.if.status[ifOperStatus.{#SNMPINDEX}])=2 and (last(/TP-LINK by SNMP/net.if.status[ifOperStatus.{#SNMPINDEX}],#1)<>last(/TP-LINK by SNMP/net.if.status[ifOperStatus.{#SNMPINDEX}],#2))`<p>Recovery expression:</p>`last(/TP-LINK by SNMP/net.if.status[ifOperStatus.{#SNMPINDEX}])<>2 or {$IFCONTROL:"{#IFNAME}"}=0` |AVERAGE |<p>Manual close: YES</p> |
|Interface {#IFDESCR}: High bandwidth usage |<p>The network interface utilization is close to its estimated maximum bandwidth.</p> |`(avg(/TP-LINK by SNMP/net.if.in[ifInOctets.{#SNMPINDEX}],15m)>({$IF.UTIL.MAX:"{#IFNAME}"}/100)*last(/TP-LINK by SNMP/net.if.speed[ifSpeed.{#SNMPINDEX}]) or avg(/TP-LINK by SNMP/net.if.out[ifOutOctets.{#SNMPINDEX}],15m)>({$IF.UTIL.MAX:"{#IFNAME}"}/100)*last(/TP-LINK by SNMP/net.if.speed[ifSpeed.{#SNMPINDEX}])) and last(/TP-LINK by SNMP/net.if.speed[ifSpeed.{#SNMPINDEX}])>0`<p>Recovery expression:</p>`avg(/TP-LINK by SNMP/net.if.in[ifInOctets.{#SNMPINDEX}],15m)<(({$IF.UTIL.MAX:"{#IFNAME}"}-3)/100)*last(/TP-LINK by SNMP/net.if.speed[ifSpeed.{#SNMPINDEX}]) and avg(/TP-LINK by SNMP/net.if.out[ifOutOctets.{#SNMPINDEX}],15m)<(({$IF.UTIL.MAX:"{#IFNAME}"}-3)/100)*last(/TP-LINK by SNMP/net.if.speed[ifSpeed.{#SNMPINDEX}])` |WARNING |<p>Manual close: YES</p><p>**Depends on**:</p><p>- Interface {#IFDESCR}: Link down</p> |
|Interface {#IFDESCR}: High error rate |<p>Recovers when below 80% of {$IF.ERRORS.WARN:"{#IFNAME}"} threshold</p> |`min(/TP-LINK by SNMP/net.if.in.errors[ifInErrors.{#SNMPINDEX}],5m)>{$IF.ERRORS.WARN:"{#IFNAME}"} or min(/TP-LINK by SNMP/net.if.out.errors[ifOutErrors.{#SNMPINDEX}],5m)>{$IF.ERRORS.WARN:"{#IFNAME}"}`<p>Recovery expression:</p>`max(/TP-LINK by SNMP/net.if.in.errors[ifInErrors.{#SNMPINDEX}],5m)<{$IF.ERRORS.WARN:"{#IFNAME}"}*0.8 and max(/TP-LINK by SNMP/net.if.out.errors[ifOutErrors.{#SNMPINDEX}],5m)<{$IF.ERRORS.WARN:"{#IFNAME}"}*0.8` |WARNING |<p>Manual close: YES</p><p>**Depends on**:</p><p>- Interface {#IFDESCR}: Link down</p> |
|Interface {#IFDESCR}: Ethernet has changed to lower speed than it was before |<p>This Ethernet connection has transitioned down from its known maximum speed. This might be a sign of autonegotiation issues. Ack to close.</p> |`change(/TP-LINK by SNMP/net.if.speed[ifSpeed.{#SNMPINDEX}])<0 and last(/TP-LINK by SNMP/net.if.speed[ifSpeed.{#SNMPINDEX}])>0 and ( last(/TP-LINK by SNMP/net.if.type[ifType.{#SNMPINDEX}])=6 or last(/TP-LINK by SNMP/net.if.type[ifType.{#SNMPINDEX}])=7 or last(/TP-LINK by SNMP/net.if.type[ifType.{#SNMPINDEX}])=11 or last(/TP-LINK by SNMP/net.if.type[ifType.{#SNMPINDEX}])=62 or last(/TP-LINK by SNMP/net.if.type[ifType.{#SNMPINDEX}])=69 or last(/TP-LINK by SNMP/net.if.type[ifType.{#SNMPINDEX}])=117 ) and (last(/TP-LINK by SNMP/net.if.status[ifOperStatus.{#SNMPINDEX}])<>2) `<p>Recovery expression:</p>`(change(/TP-LINK by SNMP/net.if.speed[ifSpeed.{#SNMPINDEX}])>0 and last(/TP-LINK by SNMP/net.if.speed[ifSpeed.{#SNMPINDEX}],#2)>0) or (last(/TP-LINK by SNMP/net.if.status[ifOperStatus.{#SNMPINDEX}])=2) ` |INFO |<p>Manual close: YES</p><p>**Depends on**:</p><p>- Interface {#IFDESCR}: Link down</p> |
|Host has been restarted |<p>Uptime is less than 10 minutes.</p> |`(last(/TP-LINK by SNMP/system.hw.uptime[hrSystemUptime.0])>0 and last(/TP-LINK by SNMP/system.hw.uptime[hrSystemUptime.0])<10m) or (last(/TP-LINK by SNMP/system.hw.uptime[hrSystemUptime.0])=0 and last(/TP-LINK by SNMP/system.net.uptime[sysUpTime.0])<10m)` |WARNING |<p>Manual close: YES</p><p>**Depends on**:</p><p>- No SNMP data collection</p> |
|No SNMP data collection |<p>SNMP is not available for polling. Please check device connectivity and SNMP settings.</p> |`max(/TP-LINK by SNMP/zabbix[host,snmp,available],{$SNMP.TIMEOUT})=0` |WARNING |<p>**Depends on**:</p><p>- Unavailable by ICMP ping</p> |
|Unavailable by ICMP ping |<p>Last three attempts returned timeout.  Please check device connectivity.</p> |`max(/TP-LINK by SNMP/icmpping,#3)=0` |HIGH | |
|High ICMP ping loss |<p>-</p> |`min(/TP-LINK by SNMP/icmppingloss,5m)>{$ICMP_LOSS_WARN} and min(/TP-LINK by SNMP/icmppingloss,5m)<100` |WARNING |<p>**Depends on**:</p><p>- Unavailable by ICMP ping</p> |
|High ICMP ping response time |<p>-</p> |`avg(/TP-LINK by SNMP/icmppingsec,5m)>{$ICMP_RESPONSE_TIME_WARN}` |WARNING |<p>**Depends on**:</p><p>- High ICMP ping loss</p><p>- Unavailable by ICMP ping</p> |

## Feedback

Please report any issues with the template at https://support.zabbix.com.

## Known Issues

- Description: Default sysLocation, sysName and sysContact is not filled with proper data. Real hostname and location can be found only in private branch (TPLINK-SYSINFO-MIB). Please check whether this problem exists in the latest firmware: https://www.tp-link.com/en/support/download/t2600g-28ts/#Firmware
  - Version: 2.0.0 Build 20170628 Rel.55184 (Beta)
  - Device: T2600G-28TS 2.0

- Description: The Serial number of the product (tpSysInfoSerialNum) is missing in HW versions prior to V2_170323
  - Version: Prior to version V2_170323
  - Device: T2600G-28TS 2.0

