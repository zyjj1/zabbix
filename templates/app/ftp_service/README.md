
# FTP Service

## Overview

For Zabbix version: 6.0 and higher  

## Setup

Refer to the vendor documentation.

## Zabbix configuration

No specific Zabbix configuration is required.


## Template links

There are no template links in this template.

## Discovery rules


## Items collected

|Group|Name|Description|Type|Key and additional info|
|-----|----|-----------|----|---------------------|
|Services |FTP service is running |<p>-</p> |SIMPLE |net.tcp.service[ftp] |

## Triggers

|Name|Description|Expression|Severity|Dependencies and additional info|
|----|-----------|----|----|----|
|FTP service is down on {HOST.NAME} |<p>-</p> |`max(/FTP Service/net.tcp.service[ftp],#3)=0` |AVERAGE | |

## Feedback

Please report any issues with the template at https://support.zabbix.com

