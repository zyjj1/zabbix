
# Mikrotik SNMP

## Overview

For Zabbix version: 6.0 and higher  

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
|{$IF.UTIL.MAX} |<p>-</p> |`90` |
|{$IFCONTROL} |<p>-</p> |`1` |
|{$IFNAME.LTEMODEM.MATCHES} |<p>This macro is used in LTE modem discovery. It can be overridden on the host.</p> |`^lte` |
|{$IFNAME.WIFI.MATCHES} |<p>This macro is used in CAPsMAN AP channel discovery. It can be overridden on the host level.</p> |`WIFI` |
|{$LTEMODEM.RSRP.MIN.WARN} |<p>The LTE modem RSRP minimum value for warning trigger expression.</p> |`-100` |
|{$LTEMODEM.RSRQ.MIN.WARN} |<p>The LTE modem RSRQ minimum value for warning trigger expression.</p> |`-20` |
|{$LTEMODEM.RSSI.MIN.WARN} |<p>The LTE modem RSSI minimum value for warning trigger expression.</p> |`-100` |
|{$LTEMODEM.SINR.MIN.WARN} |<p>The LTE modem SINR minimum value for warning trigger expression.</p> |`0` |
|{$MEMORY.UTIL.MAX} |<p>-</p> |`90` |
|{$NET.IF.IFADMINSTATUS.MATCHES} |<p>Ignore notPresent(6)</p> |`^.*` |
|{$NET.IF.IFADMINSTATUS.NOT_MATCHES} |<p>Ignore down(2) administrative status</p> |`^2$` |
|{$NET.IF.IFALIAS.MATCHES} |<p>-</p> |`.*` |
|{$NET.IF.IFALIAS.NOT_MATCHES} |<p>-</p> |`CHANGE_IF_NEEDED` |
|{$NET.IF.IFDESCR.MATCHES} |<p>-</p> |`.*` |
|{$NET.IF.IFDESCR.NOT_MATCHES} |<p>-</p> |`CHANGE_IF_NEEDED` |
|{$NET.IF.IFNAME.MATCHES} |<p>-</p> |`^.*$` |
|{$NET.IF.IFNAME.NOT_MATCHES} |<p>Filter out loopbacks, nulls, docker veth links and docker0 bridge by default</p> |`(^Software Loopback Interface|^NULL[0-9.]*$|^[Ll]o[0-9.]*$|^[Ss]ystem$|^Nu[0-9.]*$|^veth[0-9a-z]+$|docker[0-9]+|br-[a-z0-9]{12})` |
|{$NET.IF.IFOPERSTATUS.MATCHES} |<p>-</p> |`^.*$` |
|{$NET.IF.IFOPERSTATUS.NOT_MATCHES} |<p>Ignore notPresent(6)</p> |`^6$` |
|{$NET.IF.IFTYPE.MATCHES} |<p>-</p> |`.*` |
|{$NET.IF.IFTYPE.NOT_MATCHES} |<p>-</p> |`CHANGE_IF_NEEDED` |
|{$SNMP.TIMEOUT} |<p>-</p> |`5m` |
|{$TEMP_CRIT:"CPU"} |<p>-</p> |`75` |
|{$TEMP_CRIT_LOW} |<p>-</p> |`5` |
|{$TEMP_CRIT} |<p>-</p> |`60` |
|{$TEMP_WARN:"CPU"} |<p>-</p> |`70` |
|{$TEMP_WARN} |<p>-</p> |`50` |
|{$VFS.FS.PUSED.MAX.CRIT} |<p>-</p> |`90` |
|{$VFS.FS.PUSED.MAX.WARN} |<p>-</p> |`80` |

## Template links

There are no template links in this template.

## Discovery rules

|Name|Description|Type|Key and additional info|
|----|-----------|----|----|
|AP channel discovery |<p>MIKROTIK-MIB::mtxrWlAp</p> |SNMP |mtxrWlAp.discovery<p>**Filter**:</p>AND <p>- {#IFTYPE} MATCHES_REGEX `^71$`</p><p>- {#IFADMINSTATUS} MATCHES_REGEX `^1$`</p> |
|CAPsMAN AP channel discovery |<p>MIKROTIK-MIB::mtxrWlCMChannel</p> |SNMP |mtxrWlCMChannel.discovery<p>**Filter**:</p>AND <p>- {#IFTYPE} MATCHES_REGEX `^1$`</p><p>- {#IFNAME} MATCHES_REGEX `{$IFNAME.WIFI.MATCHES}`</p> |
|CPU discovery |<p>HOST-RESOURCES-MIB::hrProcessorTable discovery</p> |SNMP |hrProcessorLoad.discovery |
|LTE modem discovery |<p>MIKROTIK-MIB::mtxrLTEModemInterfaceIndex</p> |SNMP |mtxrLTEModem.discovery<p>**Filter**:</p>AND <p>- {#IFTYPE} MATCHES_REGEX `^1$`</p><p>- {#IFNAME} MATCHES_REGEX `{$IFNAME.LTEMODEM.MATCHES}`</p> |
|Network interfaces discovery |<p>Discovering interfaces from IF-MIB.</p> |SNMP |net.if.discovery<p>**Filter**:</p>AND <p>- {#IFADMINSTATUS} MATCHES_REGEX `{$NET.IF.IFADMINSTATUS.MATCHES}`</p><p>- {#IFADMINSTATUS} NOT_MATCHES_REGEX `{$NET.IF.IFADMINSTATUS.NOT_MATCHES}`</p><p>- {#IFOPERSTATUS} MATCHES_REGEX `{$NET.IF.IFOPERSTATUS.MATCHES}`</p><p>- {#IFOPERSTATUS} NOT_MATCHES_REGEX `{$NET.IF.IFOPERSTATUS.NOT_MATCHES}`</p><p>- {#IFNAME} MATCHES_REGEX `{$NET.IF.IFNAME.MATCHES}`</p><p>- {#IFNAME} NOT_MATCHES_REGEX `{$NET.IF.IFNAME.NOT_MATCHES}`</p><p>- {#IFDESCR} MATCHES_REGEX `{$NET.IF.IFDESCR.MATCHES}`</p><p>- {#IFDESCR} NOT_MATCHES_REGEX `{$NET.IF.IFDESCR.NOT_MATCHES}`</p><p>- {#IFALIAS} MATCHES_REGEX `{$NET.IF.IFALIAS.MATCHES}`</p><p>- {#IFALIAS} NOT_MATCHES_REGEX `{$NET.IF.IFALIAS.NOT_MATCHES}`</p><p>- {#IFTYPE} MATCHES_REGEX `{$NET.IF.IFTYPE.MATCHES}`</p><p>- {#IFTYPE} NOT_MATCHES_REGEX `{$NET.IF.IFTYPE.NOT_MATCHES}`</p> |
|Storage discovery |<p>HOST-RESOURCES-MIB::hrStorage discovery with storage filter</p> |SNMP |storage.discovery<p>**Filter**:</p>OR <p>- {#STORAGE_TYPE} MATCHES_REGEX `.+4$`</p><p>- {#STORAGE_TYPE} MATCHES_REGEX `.+hrStorageFixedDisk`</p> |
|Temperature CPU discovery |<p>MIKROTIK-MIB::mtxrHlProcessorTemperature</p><p>Since temperature of CPU is not available on all Mikrotik hardware, this is done to avoid unsupported items.</p> |SNMP |mtxrHlProcessorTemperature.discovery |
|Temperature sensor discovery |<p>MIKROTIK-MIB::mtxrHlTemperature</p><p>Since temperature sensor is not available on all Mikrotik hardware, this is done to avoid unsupported items.</p> |SNMP |mtxrHlTemperature.discovery |

## Items collected

|Group|Name|Description|Type|Key and additional info|
|-----|----|-----------|----|---------------------|
|CPU |#{#SNMPINDEX}: CPU utilization |<p>MIB: HOST-RESOURCES-MIB</p><p>The average, over the last minute, of the percentage of time that this processor was not idle. Implementations may approximate this one minute smoothing period if necessary.</p> |SNMP |system.cpu.util[hrProcessorLoad.{#SNMPINDEX}] |
|General |SNMP traps (fallback) |<p>The item is used to collect all SNMP traps unmatched by other snmptrap items</p> |SNMP_TRAP |snmptrap.fallback |
|General |System location |<p>MIB: SNMPv2-MIB</p><p>The physical location of this node (e.g., `telephone closet, 3rd floor').  If the location is unknown, the value is the zero-length string.</p> |SNMP |system.location[sysLocation.0]<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `12h`</p> |
|General |System contact details |<p>MIB: SNMPv2-MIB</p><p>The textual identification of the contact person for this managed node, together with information on how to contact this person.  If no contact information is known, the value is the zero-length string.</p> |SNMP |system.contact[sysContact.0]<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `12h`</p> |
|General |System object ID |<p>MIB: SNMPv2-MIB</p><p>The vendor's authoritative identification of the network management subsystem contained in the entity.  This value is allocated within the SMI enterprises subtree (1.3.6.1.4.1) and provides an easy and unambiguous means for determining`what kind of box' is being managed.  For example, if vendor`Flintstones, Inc.' was assigned the subtree1.3.6.1.4.1.4242, it could assign the identifier 1.3.6.1.4.1.4242.1.1 to its `Fred Router'.</p> |SNMP |system.objectid[sysObjectID.0]<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `12h`</p> |
|General |System name |<p>MIB: SNMPv2-MIB</p><p>An administratively-assigned name for this managed node.By convention, this is the node's fully-qualified domain name.  If the name is unknown, the value is the zero-length string.</p> |SNMP |system.name<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `12h`</p> |
|General |System description |<p>MIB: SNMPv2-MIB</p><p>A textual description of the entity. This value should</p><p>include the full name and version identification of the system's hardware type, software operating-system, and</p><p>networking software.</p> |SNMP |system.descr[sysDescr.0]<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `12h`</p> |
|Inventory |Operating system |<p>MIB: MIKROTIK-MIB</p><p>Software version.</p> |SNMP |system.sw.os[mtxrLicVersion.0]<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `1d`</p> |
|Inventory |Hardware model name |<p>-</p> |SNMP |system.hw.model<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `1d`</p> |
|Inventory |Hardware serial number |<p>MIB: MIKROTIK-MIB</p><p>RouterBOARD serial number.</p> |SNMP |system.hw.serialnumber<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `1d`</p> |
|Inventory |Firmware version |<p>MIB: MIKROTIK-MIB</p><p>Current firmware version.</p> |SNMP |system.hw.firmware<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `1d`</p> |
|Memory |Used memory |<p>MIB: HOST-RESOURCES-MIB</p><p>The amount of the storage represented by this entry that is allocated, in units of hrStorageAllocationUnits.</p> |SNMP |vm.memory.used[hrStorageUsed.Memory]<p>**Preprocessing**:</p><p>- MULTIPLIER: `1024`</p> |
|Memory |Total memory |<p>MIB: HOST-RESOURCES-MIB</p><p>The size of the storage represented by this entry, in</p><p>units of hrStorageAllocationUnits. This object is</p><p>writable to allow remote configuration of the size of</p><p>the storage area in those cases where such an</p><p>operation makes sense and is possible on the</p><p>underlying system. For example, the amount of main</p><p>memory allocated to a buffer pool might be modified or</p><p>the amount of disk space allocated to virtual memory</p><p>might be modified.</p> |SNMP |vm.memory.total[hrStorageSize.Memory]<p>**Preprocessing**:</p><p>- MULTIPLIER: `1024`</p> |
|Memory |Memory utilization |<p>Memory utilization in %</p> |CALCULATED |vm.memory.util[memoryUsedPercentage.Memory]<p>**Expression**:</p>`last(//vm.memory.used[hrStorageUsed.Memory])/last(//vm.memory.total[hrStorageSize.Memory])*100` |
|Network interfaces |Interface {#IFNAME}({#IFALIAS}): Operational status |<p>MIB: IF-MIB</p><p>The current operational state of the interface.</p><p>- The testing(3) state indicates that no operational packet scan be passed</p><p>- If ifAdminStatus is down(2) then ifOperStatus should be down(2)</p><p>- If ifAdminStatus is changed to up(1) then ifOperStatus should change to up(1) if the interface is ready to transmit and receive network traffic</p><p>- It should change todormant(5) if the interface is waiting for external actions (such as a serial line waiting for an incoming connection)</p><p>- It should remain in the down(2) state if and only if there is a fault that prevents it from going to the up(1) state</p><p>- It should remain in the notPresent(6) state if the interface has missing(typically, hardware) components.</p> |SNMP |net.if.status[ifOperStatus.{#SNMPINDEX}] |
|Network interfaces |Interface {#IFNAME}({#IFALIAS}): Bits received |<p>MIB: IF-MIB</p><p>The total number of octets received on the interface, including framing characters. This object is a 64-bit version of ifInOctets. Discontinuities in the value of this counter can occur at re-initialization of the management system, and at other times as indicated by the value of ifCounterDiscontinuityTime.</p> |SNMP |net.if.in[ifHCInOctets.{#SNMPINDEX}]<p>**Preprocessing**:</p><p>- CHANGE_PER_SECOND</p><p>- MULTIPLIER: `8`</p> |
|Network interfaces |Interface {#IFNAME}({#IFALIAS}): Bits sent |<p>MIB: IF-MIB</p><p>The total number of octets transmitted out of the interface, including framing characters. This object is a 64-bit version of ifOutOctets.Discontinuities in the value of this counter can occur at re-initialization of the management system, and at other times as indicated by the value of ifCounterDiscontinuityTime.</p> |SNMP |net.if.out[ifHCOutOctets.{#SNMPINDEX}]<p>**Preprocessing**:</p><p>- CHANGE_PER_SECOND</p><p>- MULTIPLIER: `8`</p> |
|Network interfaces |Interface {#IFNAME}({#IFALIAS}): Inbound packets with errors |<p>MIB: IF-MIB</p><p>For packet-oriented interfaces, the number of inbound packets that contained errors preventing them from being deliverable to a higher-layer protocol.  For character-oriented or fixed-length interfaces, the number of inbound transmission units that contained errors preventing them from being deliverable to a higher-layer protocol. Discontinuities in the value of this counter can occur at re-initialization of the management system, and at other times as indicated by the value of ifCounterDiscontinuityTime.</p> |SNMP |net.if.in.errors[ifInErrors.{#SNMPINDEX}]<p>**Preprocessing**:</p><p>- CHANGE_PER_SECOND</p> |
|Network interfaces |Interface {#IFNAME}({#IFALIAS}): Outbound packets with errors |<p>MIB: IF-MIB</p><p>For packet-oriented interfaces, the number of outbound packets that contained errors preventing them from being deliverable to a higher-layer protocol.  For character-oriented or fixed-length interfaces, the number of outbound transmission units that contained errors preventing them from being deliverable to a higher-layer protocol. Discontinuities in the value of this counter can occur at re-initialization of the management system, and at other times as indicated by the value of ifCounterDiscontinuityTime.</p> |SNMP |net.if.out.errors[ifOutErrors.{#SNMPINDEX}]<p>**Preprocessing**:</p><p>- CHANGE_PER_SECOND</p> |
|Network interfaces |Interface {#IFNAME}({#IFALIAS}): Outbound packets discarded |<p>MIB: IF-MIB</p><p>The number of outbound packets which were chosen to be discarded</p><p>even though no errors had been detected to prevent their being deliverable to a higher-layer protocol.</p><p>One possible reason for discarding such a packet could be to free up buffer space.</p><p>Discontinuities in the value of this counter can occur at re-initialization of the management system,</p><p>and at other times as indicated by the value of ifCounterDiscontinuityTime.</p> |SNMP |net.if.out.discards[ifOutDiscards.{#SNMPINDEX}]<p>**Preprocessing**:</p><p>- CHANGE_PER_SECOND</p> |
|Network interfaces |Interface {#IFNAME}({#IFALIAS}): Inbound packets discarded |<p>MIB: IF-MIB</p><p>The number of inbound packets which were chosen to be discarded</p><p>even though no errors had been detected to prevent their being deliverable to a higher-layer protocol.</p><p>One possible reason for discarding such a packet could be to free up buffer space.</p><p>Discontinuities in the value of this counter can occur at re-initialization of the management system,</p><p>and at other times as indicated by the value of ifCounterDiscontinuityTime.</p> |SNMP |net.if.in.discards[ifInDiscards.{#SNMPINDEX}]<p>**Preprocessing**:</p><p>- CHANGE_PER_SECOND</p> |
|Network interfaces |Interface {#IFNAME}({#IFALIAS}): Interface type |<p>MIB: IF-MIB</p><p>The type of interface.</p><p>Additional values for ifType are assigned by the Internet Assigned NumbersAuthority (IANA),</p><p>through updating the syntax of the IANAifType textual convention.</p> |SNMP |net.if.type[ifType.{#SNMPINDEX}]<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `1d`</p> |
|Network interfaces |Interface {#IFNAME}({#IFALIAS}): Speed |<p>MIB: IF-MIB</p><p>An estimate of the interface's current bandwidth in units of 1,000,000 bits per second. If this object reports a value of `n' then the speed of the interface is somewhere in the range of `n-500,000' to`n+499,999'.  For interfaces which do not vary in bandwidth or for those where no accurate estimation can be made, this object should contain the nominal bandwidth. For a sub-layer which has no concept of bandwidth, this object should be zero.</p> |SNMP |net.if.speed[ifHighSpeed.{#SNMPINDEX}]<p>**Preprocessing**:</p><p>- MULTIPLIER: `1000000`</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `1h`</p> |
|Status |Uptime |<p>MIB: SNMPv2-MIB</p><p>The time (in hundredths of a second) since the network management portion of the system was last re-initialized.</p> |SNMP |system.uptime[sysUpTime.0]<p>**Preprocessing**:</p><p>- MULTIPLIER: `0.01`</p> |
|Status |SNMP agent availability |<p>Availability of SNMP checks on the host. The value of this item corresponds to availability icons in the host list.</p><p>Possible value:</p><p>0 - not available</p><p>1 - available</p><p>2 - unknown</p> |INTERNAL |zabbix[host,snmp,available] |
|Status |ICMP ping |<p>-</p> |SIMPLE |icmpping |
|Status |ICMP loss |<p>-</p> |SIMPLE |icmppingloss |
|Status |ICMP response time |<p>-</p> |SIMPLE |icmppingsec |
|Storage |Disk-{#SNMPINDEX}: Used space |<p>MIB: HOST-RESOURCES-MIB</p><p>The amount of the storage represented by this entry that is allocated, in units of hrStorageAllocationUnits.</p> |SNMP |vfs.fs.used[hrStorageSize.{#SNMPINDEX}]<p>**Preprocessing**:</p><p>- MULTIPLIER: `1024`</p> |
|Storage |Disk-{#SNMPINDEX}: Total space |<p>MIB: HOST-RESOURCES-MIB</p><p>The size of the storage represented by this entry, in</p><p>units of hrStorageAllocationUnits. This object is</p><p>writable to allow remote configuration of the size of</p><p>the storage area in those cases where such an</p><p>operation makes sense and is possible on the</p><p>underlying system. For example, the amount of main</p><p>memory allocated to a buffer pool might be modified or</p><p>the amount of disk space allocated to virtual memory</p><p>might be modified.</p> |SNMP |vfs.fs.total[hrStorageSize.{#SNMPINDEX}]<p>**Preprocessing**:</p><p>- MULTIPLIER: `1024`</p> |
|Storage |Disk-{#SNMPINDEX}: Space utilization |<p>Space utilization in % for Disk-{#SNMPINDEX}</p> |CALCULATED |vfs.fs.pused[hrStorageSize.{#SNMPINDEX}]<p>**Expression**:</p>`(last(//vfs.fs.used[hrStorageSize.{#SNMPINDEX}])/last(//vfs.fs.total[hrStorageSize.{#SNMPINDEX}]))*100` |
|Temperature |CPU: Temperature |<p>MIB: MIKROTIK-MIB</p><p>mtxrHlProcessorTemperature Processor temperature in Celsius (degrees C).</p><p>Might be missing in entry models (RB750, RB450G..).</p> |SNMP |sensor.temp.value[mtxrHlProcessorTemperature.{#SNMPINDEX}]<p>**Preprocessing**:</p><p>- MULTIPLIER: `0.1`</p> |
|Temperature |Device: Temperature |<p>MIB: MIKROTIK-MIB</p><p>mtxrHlTemperature Device temperature in Celsius (degrees C).</p><p>Might be missing in entry models (RB750, RB450G..).</p><p>Reference: http://wiki.mikrotik.com/wiki/Manual:SNMP</p> |SNMP |sensor.temp.value[mtxrHlTemperature.{#SNMPINDEX}]<p>**Preprocessing**:</p><p>- MULTIPLIER: `0.1`</p> |
|Wireless |Interface {#IFNAME}({#IFALIAS}): LTE modem RSSI |<p>MIB: MIKROTIK-MIB</p><p>mtxrLTEModemSignalRSSI Received Signal Strength Indicator.</p> |SNMP |lte.modem.rssi[mtxrLTEModemSignalRSSI.{#SNMPINDEX}] |
|Wireless |Interface {#IFNAME}({#IFALIAS}): LTE modem RSRP |<p>MIB: MIKROTIK-MIB</p><p>mtxrLTEModemSignalRSRP Reference Signal Received Power.</p> |SNMP |lte.modem.rsrp[mtxrLTEModemSignalRSRP.{#SNMPINDEX}] |
|Wireless |Interface {#IFNAME}({#IFALIAS}): LTE modem RSRQ |<p>MIB: MIKROTIK-MIB</p><p>mtxrLTEModemSignalRSRQ Reference Signal Received Quality.</p> |SNMP |lte.modem.rsrq[mtxrLTEModemSignalRSRQ.{#SNMPINDEX}] |
|Wireless |Interface {#IFNAME}({#IFALIAS}): LTE modem SINR |<p>MIB: MIKROTIK-MIB</p><p>mtxrLTEModemSignalSINR Signal to Interference & Noise Ratio.</p> |SNMP |lte.modem.sinr[mtxrLTEModemSignalSINR.{#SNMPINDEX}] |
|Wireless |Interface {#IFNAME}({#IFALIAS}): SSID |<p>MIB: MIKROTIK-MIB</p><p>mtxrWlApSsid Service Set Identifier.</p> |SNMP |ssid.name[mtxrWlApSsid.{#SNMPINDEX}]<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `1h`</p> |
|Wireless |Interface {#IFNAME}({#IFALIAS}): AP band |<p>MIB: MIKROTIK-MIB</p><p>mtxrWlApBand</p> |SNMP |ssid.band[mtxrWlApBand.{#SNMPINDEX}]<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `1h`</p> |
|Wireless |Interface {#IFNAME}({#IFALIAS}): AP noise floor |<p>MIB: MIKROTIK-MIB</p><p>mtxrWlApNoiseFloor</p> |SNMP |ssid.noise[mtxrWlApNoiseFloor.{#SNMPINDEX}]<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `15m`</p> |
|Wireless |Interface {#IFNAME}({#IFALIAS}): AP registered clients |<p>MIB: MIKROTIK-MIB</p><p>mtxrWlApClientCount Client established connection to AP, but didn't finish all authentication procedures for full connection.</p> |SNMP |ssid.regclient[mtxrWlApClientCount.{#SNMPINDEX}] |
|Wireless |Interface {#IFNAME}({#IFALIAS}): AP authenticated clients |<p>MIB: MIKROTIK-MIB</p><p>mtxrWlApAuthClientCount Number of authentication clients.</p> |SNMP |ssid.authclient[mtxrWlApAuthClientCount.{#SNMPINDEX}] |
|Wireless |Interface {#IFNAME}({#IFALIAS}): AP channel |<p>MIB: MIKROTIK-MIB</p><p>mtxrWlCMChannel</p> |SNMP |ssid.channel[mtxrWlCMChannel.{#SNMPINDEX}]<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `1h`</p> |
|Wireless |Interface {#IFNAME}({#IFALIAS}): AP state |<p>MIB: MIKROTIK-MIB</p><p>mtxrWlCMState Wireless interface state.</p> |SNMP |ssid.state[mtxrWlCMState.{#SNMPINDEX}]<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `1h`</p> |
|Wireless |Interface {#IFNAME}({#IFALIAS}): AP registered clients |<p>MIB: MIKROTIK-MIB</p><p>mtxrWlCMRegClientCount Client established connection to AP, but didn't finish all authentication procedures for full connection.</p> |SNMP |ssid.regclient[mtxrWlCMRegClientCount.{#SNMPINDEX}] |
|Wireless |Interface {#IFNAME}({#IFALIAS}): AP authenticated clients |<p>MIB: MIKROTIK-MIB</p><p>mtxrWlCMAuthClientCount Number of authentication clients.</p> |SNMP |ssid.authclient[mtxrWlCMAuthClientCount.{#SNMPINDEX}] |

## Triggers

|Name|Description|Expression|Severity|Dependencies and additional info|
|----|-----------|----|----|----|
|#{#SNMPINDEX}: High CPU utilization (over {$CPU.UTIL.CRIT}% for 5m) |<p>CPU utilization is too high. The system might be slow to respond.</p> |`min(/Mikrotik SNMP/system.cpu.util[hrProcessorLoad.{#SNMPINDEX}],5m)>{$CPU.UTIL.CRIT}` |WARNING | |
|System name has changed (new name: {ITEM.VALUE}) |<p>System name has changed. Ack to close.</p> |`last(/Mikrotik SNMP/system.name,#1)<>last(/Mikrotik SNMP/system.name,#2) and length(last(/Mikrotik SNMP/system.name))>0` |INFO |<p>Manual close: YES</p> |
|Operating system description has changed |<p>Operating system description has changed. Possible reasons that system has been updated or replaced. Ack to close.</p> |`last(/Mikrotik SNMP/system.sw.os[mtxrLicVersion.0],#1)<>last(/Mikrotik SNMP/system.sw.os[mtxrLicVersion.0],#2) and length(last(/Mikrotik SNMP/system.sw.os[mtxrLicVersion.0]))>0` |INFO |<p>Manual close: YES</p><p>**Depends on**:</p><p>- System name has changed (new name: {ITEM.VALUE})</p> |
|Device has been replaced (new serial number received) |<p>Device serial number has changed. Ack to close</p> |`last(/Mikrotik SNMP/system.hw.serialnumber,#1)<>last(/Mikrotik SNMP/system.hw.serialnumber,#2) and length(last(/Mikrotik SNMP/system.hw.serialnumber))>0` |INFO |<p>Manual close: YES</p> |
|Firmware has changed |<p>Firmware version has changed. Ack to close</p> |`last(/Mikrotik SNMP/system.hw.firmware,#1)<>last(/Mikrotik SNMP/system.hw.firmware,#2) and length(last(/Mikrotik SNMP/system.hw.firmware))>0` |INFO |<p>Manual close: YES</p> |
|High memory utilization (>{$MEMORY.UTIL.MAX}% for 5m) |<p>The system is running out of free memory.</p> |`min(/Mikrotik SNMP/vm.memory.util[memoryUsedPercentage.Memory],5m)>{$MEMORY.UTIL.MAX}` |AVERAGE | |
|Interface {#IFNAME}({#IFALIAS}): Link down |<p>This trigger expression works as follows:</p><p>1. Can be triggered if operations status is down.</p><p>2. {$IFCONTROL:"{#IFNAME}"}=1 - user can redefine Context macro to value - 0. That marks this interface as not important. No new trigger will be fired if this interface is down.</p><p>3. {TEMPLATE_NAME:METRIC.diff()}=1) - trigger fires only if operational status was up(1) sometime before. (So, do not fire 'ethernal off' interfaces.)</p><p>WARNING: if closed manually - won't fire again on next poll, because of .diff.</p> |`{$IFCONTROL:"{#IFNAME}"}=1 and last(/Mikrotik SNMP/net.if.status[ifOperStatus.{#SNMPINDEX}])=2 and (last(/Mikrotik SNMP/net.if.status[ifOperStatus.{#SNMPINDEX}],#1)<>last(/Mikrotik SNMP/net.if.status[ifOperStatus.{#SNMPINDEX}],#2))`<p>Recovery expression:</p>`last(/Mikrotik SNMP/net.if.status[ifOperStatus.{#SNMPINDEX}])<>2 or {$IFCONTROL:"{#IFNAME}"}=0` |AVERAGE |<p>Manual close: YES</p> |
|Interface {#IFNAME}({#IFALIAS}): High bandwidth usage (>{$IF.UTIL.MAX:"{#IFNAME}"}%) |<p>The network interface utilization is close to its estimated maximum bandwidth.</p> |`(avg(/Mikrotik SNMP/net.if.in[ifHCInOctets.{#SNMPINDEX}],15m)>({$IF.UTIL.MAX:"{#IFNAME}"}/100)*last(/Mikrotik SNMP/net.if.speed[ifHighSpeed.{#SNMPINDEX}]) or avg(/Mikrotik SNMP/net.if.out[ifHCOutOctets.{#SNMPINDEX}],15m)>({$IF.UTIL.MAX:"{#IFNAME}"}/100)*last(/Mikrotik SNMP/net.if.speed[ifHighSpeed.{#SNMPINDEX}])) and last(/Mikrotik SNMP/net.if.speed[ifHighSpeed.{#SNMPINDEX}])>0`<p>Recovery expression:</p>`avg(/Mikrotik SNMP/net.if.in[ifHCInOctets.{#SNMPINDEX}],15m)<(({$IF.UTIL.MAX:"{#IFNAME}"}-3)/100)*last(/Mikrotik SNMP/net.if.speed[ifHighSpeed.{#SNMPINDEX}]) and avg(/Mikrotik SNMP/net.if.out[ifHCOutOctets.{#SNMPINDEX}],15m)<(({$IF.UTIL.MAX:"{#IFNAME}"}-3)/100)*last(/Mikrotik SNMP/net.if.speed[ifHighSpeed.{#SNMPINDEX}])` |WARNING |<p>Manual close: YES</p><p>**Depends on**:</p><p>- Interface {#IFNAME}({#IFALIAS}): Link down</p> |
|Interface {#IFNAME}({#IFALIAS}): High error rate (>{$IF.ERRORS.WARN:"{#IFNAME}"} for 5m) |<p>Recovers when below 80% of {$IF.ERRORS.WARN:"{#IFNAME}"} threshold</p> |`min(/Mikrotik SNMP/net.if.in.errors[ifInErrors.{#SNMPINDEX}],5m)>{$IF.ERRORS.WARN:"{#IFNAME}"} or min(/Mikrotik SNMP/net.if.out.errors[ifOutErrors.{#SNMPINDEX}],5m)>{$IF.ERRORS.WARN:"{#IFNAME}"}`<p>Recovery expression:</p>`max(/Mikrotik SNMP/net.if.in.errors[ifInErrors.{#SNMPINDEX}],5m)<{$IF.ERRORS.WARN:"{#IFNAME}"}*0.8 and max(/Mikrotik SNMP/net.if.out.errors[ifOutErrors.{#SNMPINDEX}],5m)<{$IF.ERRORS.WARN:"{#IFNAME}"}*0.8` |WARNING |<p>Manual close: YES</p><p>**Depends on**:</p><p>- Interface {#IFNAME}({#IFALIAS}): Link down</p> |
|Interface {#IFNAME}({#IFALIAS}): Ethernet has changed to lower speed than it was before |<p>This Ethernet connection has transitioned down from its known maximum speed. This might be a sign of autonegotiation issues. Ack to close.</p> |`change(/Mikrotik SNMP/net.if.speed[ifHighSpeed.{#SNMPINDEX}])<0 and last(/Mikrotik SNMP/net.if.speed[ifHighSpeed.{#SNMPINDEX}])>0 and ( last(/Mikrotik SNMP/net.if.type[ifType.{#SNMPINDEX}])=6 or last(/Mikrotik SNMP/net.if.type[ifType.{#SNMPINDEX}])=7 or last(/Mikrotik SNMP/net.if.type[ifType.{#SNMPINDEX}])=11 or last(/Mikrotik SNMP/net.if.type[ifType.{#SNMPINDEX}])=62 or last(/Mikrotik SNMP/net.if.type[ifType.{#SNMPINDEX}])=69 or last(/Mikrotik SNMP/net.if.type[ifType.{#SNMPINDEX}])=117 ) and (last(/Mikrotik SNMP/net.if.status[ifOperStatus.{#SNMPINDEX}])<>2)`<p>Recovery expression:</p>`(change(/Mikrotik SNMP/net.if.speed[ifHighSpeed.{#SNMPINDEX}])>0 and last(/Mikrotik SNMP/net.if.speed[ifHighSpeed.{#SNMPINDEX}],#2)>0) or (last(/Mikrotik SNMP/net.if.status[ifOperStatus.{#SNMPINDEX}])=2)` |INFO |<p>Manual close: YES</p><p>**Depends on**:</p><p>- Interface {#IFNAME}({#IFALIAS}): Link down</p> |
|{HOST.NAME} has been restarted (uptime < 10m) |<p>Uptime is less than 10 minutes</p> |`last(/Mikrotik SNMP/system.uptime[sysUpTime.0])<10m` |WARNING |<p>Manual close: YES</p><p>**Depends on**:</p><p>- No SNMP data collection</p> |
|No SNMP data collection |<p>SNMP is not available for polling. Please check device connectivity and SNMP settings.</p> |`max(/Mikrotik SNMP/zabbix[host,snmp,available],{$SNMP.TIMEOUT})=0` |WARNING |<p>**Depends on**:</p><p>- Unavailable by ICMP ping</p> |
|Unavailable by ICMP ping |<p>Last three attempts returned timeout.  Please check device connectivity.</p> |`max(/Mikrotik SNMP/icmpping,#3)=0` |HIGH | |
|High ICMP ping loss |<p>-</p> |`min(/Mikrotik SNMP/icmppingloss,5m)>{$ICMP_LOSS_WARN} and min(/Mikrotik SNMP/icmppingloss,5m)<100` |WARNING |<p>**Depends on**:</p><p>- Unavailable by ICMP ping</p> |
|High ICMP ping response time |<p>-</p> |`avg(/Mikrotik SNMP/icmppingsec,5m)>{$ICMP_RESPONSE_TIME_WARN}` |WARNING |<p>**Depends on**:</p><p>- High ICMP ping loss</p><p>- Unavailable by ICMP ping</p> |
|Disk-{#SNMPINDEX}: Disk space is critically low (used > {$VFS.FS.PUSED.MAX.CRIT:"Disk-{#SNMPINDEX}"}%) |<p>Two conditions should match: First, space utilization should be above {$VFS.FS.PUSED.MAX.CRIT:"Disk-{#SNMPINDEX}"}.</p><p> Second condition should be one of the following:</p><p> - The disk free space is less than 5G.</p><p> - The disk will be full in less than 24 hours.</p> |`last(/Mikrotik SNMP/vfs.fs.pused[hrStorageSize.{#SNMPINDEX}])>{$VFS.FS.PUSED.MAX.CRIT:"Disk-{#SNMPINDEX}"} and ((last(/Mikrotik SNMP/vfs.fs.total[hrStorageSize.{#SNMPINDEX}])-last(/Mikrotik SNMP/vfs.fs.used[hrStorageSize.{#SNMPINDEX}]))<5G or timeleft(/Mikrotik SNMP/vfs.fs.pused[hrStorageSize.{#SNMPINDEX}],1h,100)<1d)` |AVERAGE |<p>Manual close: YES</p> |
|Disk-{#SNMPINDEX}: Disk space is low (used > {$VFS.FS.PUSED.MAX.WARN:"Disk-{#SNMPINDEX}"}%) |<p>Two conditions should match: First, space utilization should be above {$VFS.FS.PUSED.MAX.WARN:"Disk-{#SNMPINDEX}"}.</p><p> Second condition should be one of the following:</p><p> - The disk free space is less than 10G.</p><p> - The disk will be full in less than 24 hours.</p> |`last(/Mikrotik SNMP/vfs.fs.pused[hrStorageSize.{#SNMPINDEX}])>{$VFS.FS.PUSED.MAX.WARN:"Disk-{#SNMPINDEX}"} and ((last(/Mikrotik SNMP/vfs.fs.total[hrStorageSize.{#SNMPINDEX}])-last(/Mikrotik SNMP/vfs.fs.used[hrStorageSize.{#SNMPINDEX}]))<10G or timeleft(/Mikrotik SNMP/vfs.fs.pused[hrStorageSize.{#SNMPINDEX}],1h,100)<1d)` |WARNING |<p>Manual close: YES</p><p>**Depends on**:</p><p>- Disk-{#SNMPINDEX}: Disk space is critically low (used > {$VFS.FS.PUSED.MAX.CRIT:"Disk-{#SNMPINDEX}"}%)</p> |
|CPU: Temperature is above warning threshold: >{$TEMP_WARN:"CPU"} |<p>This trigger uses temperature sensor values as well as temperature sensor status if available</p> |`avg(/Mikrotik SNMP/sensor.temp.value[mtxrHlProcessorTemperature.{#SNMPINDEX}],5m)>{$TEMP_WARN:"CPU"}`<p>Recovery expression:</p>`max(/Mikrotik SNMP/sensor.temp.value[mtxrHlProcessorTemperature.{#SNMPINDEX}],5m)<{$TEMP_WARN:"CPU"}-3` |WARNING |<p>**Depends on**:</p><p>- CPU: Temperature is above critical threshold: >{$TEMP_CRIT:"CPU"}</p> |
|CPU: Temperature is above critical threshold: >{$TEMP_CRIT:"CPU"} |<p>This trigger uses temperature sensor values as well as temperature sensor status if available</p> |`avg(/Mikrotik SNMP/sensor.temp.value[mtxrHlProcessorTemperature.{#SNMPINDEX}],5m)>{$TEMP_CRIT:"CPU"}`<p>Recovery expression:</p>`max(/Mikrotik SNMP/sensor.temp.value[mtxrHlProcessorTemperature.{#SNMPINDEX}],5m)<{$TEMP_CRIT:"CPU"}-3` |HIGH | |
|CPU: Temperature is too low: <{$TEMP_CRIT_LOW:"CPU"} |<p>-</p> |`avg(/Mikrotik SNMP/sensor.temp.value[mtxrHlProcessorTemperature.{#SNMPINDEX}],5m)<{$TEMP_CRIT_LOW:"CPU"}`<p>Recovery expression:</p>`min(/Mikrotik SNMP/sensor.temp.value[mtxrHlProcessorTemperature.{#SNMPINDEX}],5m)>{$TEMP_CRIT_LOW:"CPU"}+3` |AVERAGE | |
|Device: Temperature is above warning threshold: >{$TEMP_WARN:"Device"} |<p>This trigger uses temperature sensor values as well as temperature sensor status if available</p> |`avg(/Mikrotik SNMP/sensor.temp.value[mtxrHlTemperature.{#SNMPINDEX}],5m)>{$TEMP_WARN:"Device"}`<p>Recovery expression:</p>`max(/Mikrotik SNMP/sensor.temp.value[mtxrHlTemperature.{#SNMPINDEX}],5m)<{$TEMP_WARN:"Device"}-3` |WARNING |<p>**Depends on**:</p><p>- Device: Temperature is above critical threshold: >{$TEMP_CRIT:"Device"}</p> |
|Device: Temperature is above critical threshold: >{$TEMP_CRIT:"Device"} |<p>This trigger uses temperature sensor values as well as temperature sensor status if available</p> |`avg(/Mikrotik SNMP/sensor.temp.value[mtxrHlTemperature.{#SNMPINDEX}],5m)>{$TEMP_CRIT:"Device"}`<p>Recovery expression:</p>`max(/Mikrotik SNMP/sensor.temp.value[mtxrHlTemperature.{#SNMPINDEX}],5m)<{$TEMP_CRIT:"Device"}-3` |HIGH | |
|Device: Temperature is too low: <{$TEMP_CRIT_LOW:"Device"} |<p>-</p> |`avg(/Mikrotik SNMP/sensor.temp.value[mtxrHlTemperature.{#SNMPINDEX}],5m)<{$TEMP_CRIT_LOW:"Device"}`<p>Recovery expression:</p>`min(/Mikrotik SNMP/sensor.temp.value[mtxrHlTemperature.{#SNMPINDEX}],5m)>{$TEMP_CRIT_LOW:"Device"}+3` |AVERAGE | |
|Interface {#IFNAME}({#IFALIAS}): LTE modem RSSI is low (below {$LTEMODEM.RSSI.MIN.WARN}dbm for 5m) |<p>-</p> |`max(/Mikrotik SNMP/lte.modem.rssi[mtxrLTEModemSignalRSSI.{#SNMPINDEX}],5m) < {$LTEMODEM.RSSI.MIN.WARN}` |WARNING | |
|Interface {#IFNAME}({#IFALIAS}): LTE modem RSRP is low (below {$LTEMODEM.RSRP.MIN.WARN}dbm for 5m) |<p>-</p> |`max(/Mikrotik SNMP/lte.modem.rsrp[mtxrLTEModemSignalRSRP.{#SNMPINDEX}],5m) < {$LTEMODEM.RSRP.MIN.WARN}` |WARNING | |
|Interface {#IFNAME}({#IFALIAS}): LTE modem RSRQ is low (below {$LTEMODEM.RSRQ.MIN.WARN}db for 5m) |<p>-</p> |`max(/Mikrotik SNMP/lte.modem.rsrq[mtxrLTEModemSignalRSRQ.{#SNMPINDEX}],5m) < {$LTEMODEM.RSRQ.MIN.WARN}` |WARNING | |
|Interface {#IFNAME}({#IFALIAS}): LTE modem SINR is low (below {$LTEMODEM.SINR.MIN.WARN}db for 5m) |<p>-</p> |`max(/Mikrotik SNMP/lte.modem.sinr[mtxrLTEModemSignalSINR.{#SNMPINDEX}],5m) < {$LTEMODEM.SINR.MIN.WARN}` |WARNING | |
|Interface {#IFNAME}({#IFALIAS}): AP interface {#IFNAME}({#IFALIAS}) is not running |<p>Access point interface can be not running by different reasons - disabled interface, power off, network link down.</p> |`last(/Mikrotik SNMP/ssid.state[mtxrWlCMState.{#SNMPINDEX}])<>"running-ap"` |WARNING | |

## Feedback

Please report any issues with the template at https://support.zabbix.com

## Known Issues

- Description: Doesn't have ifHighSpeed filled. fixed in more recent versions
  - Version: RouterOS 6.28 or lower

- Description: Doesn't have any temperature sensors
  - Version: RouterOS 6.38.5
  - Device: Mikrotik 941-2nD, Mikrotik 951G-2HnD

