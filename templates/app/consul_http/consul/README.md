
# HashiCorp Consul Node by HTTP

## Overview

For Zabbix version: 6.4 and higher  
The template to monitor HashiCorp Consul by Zabbix that works without any external scripts.  
Most of the metrics are collected in one go, thanks to Zabbix bulk data collection.  
Do not forget to enable Prometheus format for export metrics.
See [documentation](https://www.consul.io/docs/agent/options#telemetry-prometheus_retention_time).  
More information about metrics you can find in [official documentation](https://www.consul.io/docs/agent/telemetry).  

Template `HashiCorp Consul Node by HTTP` — collects metrics by HTTP agent from /v1/agent/metrics endpoint.



This template was tested on:

- HashiCorp Consul, version 1.10.0

## Setup

> See [Zabbix template operation](https://www.zabbix.com/documentation/6.4/manual/config/templates_out_of_the_box/http) for basic instructions.

Internal service metrics are collected from /v1/agent/metrics endpoint.
Do not forget to enable Prometheus format for export metrics. See [documentation](https://www.consul.io/docs/agent/options#telemetry-prometheus_retention_time).
Template need to use Authorization via API token.

Don't forget to change macros {$CONSUL.NODE.API.URL}, {$CONSUL.TOKEN}.  
Also, see the Macros section for a list of macros used to set trigger values.  

This template support [Consul namespaces](https://www.consul.io/docs/enterprise/namespaces). You can set macros {$CONSUL.LLD.FILTER.SERVICE_NAMESPACE.MATCHES}, {$CONSUL.LLD.FILTER.SERVICE_NAMESPACE.NOT_MATCHES} if you want to filter discovered services by namespace.  
In case of Open Source version service namespace will be set to 'None'.

*NOTE.* Some metrics may not be collected depending on your HashiCorp Consul instance version and configuration.  
*NOTE.* You maybe are interested in Envoy Proxy by HTTP [template](../../envoy_proxy_http).


## Zabbix configuration

No specific Zabbix configuration is required.

### Macros used

|Name|Description|Default|
|----|-----------|-------|
|{$CONSUL.LLD.FILTER.LOCAL_SERVICE_NAME.MATCHES} |<p>Filter of discoverable discovered services on local node.</p> |`.*` |
|{$CONSUL.LLD.FILTER.LOCAL_SERVICE_NAME.NOT_MATCHES} |<p>Filter to exclude discovered services on local node.</p> |`CHANGE IF NEEDED` |
|{$CONSUL.LLD.FILTER.SERVICE_NAMESPACE.MATCHES} |<p>Filter of discoverable discovered service by namespace on local node. Enterprise only, in case of Open Source version Namespace will be set to 'None'.</p> |`.*` |
|{$CONSUL.LLD.FILTER.SERVICE_NAMESPACE.NOT_MATCHES} |<p>Filter to exclude discovered service by namespace on local node. Enterprise only, in case of Open Source version Namespace will be set to 'None'.</p> |`CHANGE IF NEEDED` |
|{$CONSUL.NODE.API.URL} |<p>Consul instance URL.</p> |`http://localhost:8500` |
|{$CONSUL.NODE.HEALTH_SCORE.MAX.HIGH} |<p>Maximum acceptable value of node's health score for AVERAGE trigger expression.</p> |`4` |
|{$CONSUL.NODE.HEALTH_SCORE.MAX.WARN} |<p>Maximum acceptable value of node's health score for WARNING trigger expression.</p> |`2` |
|{$CONSUL.OPEN.FDS.MAX.WARN} |<p>Maximum percentage of used file descriptors.</p> |`90` |
|{$CONSUL.TOKEN} |<p>Consul auth token.</p> |`<PUT YOUR AUTH TOKEN>` |

## Template links

There are no template links in this template.

## Discovery rules

|Name|Description|Type|Key and additional info|
|----|-----------|----|----|
|HTTP API methods discovery |<p>Discovery HTTP API methods specific metrics.</p> |DEPENDENT |consul.http_api_discovery<p>**Preprocessing**:</p><p>- PROMETHEUS_TO_JSON: `consul_api_http{method =~ ".*"}`</p><p>- JAVASCRIPT: `The text is too long. Please see the template.`</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `3h`</p> |
|Local node services discovery |<p>Discover metrics for services that are registered with the local agent.</p> |DEPENDENT |consul.node_services_lld<p>**Preprocessing**:</p><p>- JAVASCRIPT: `The text is too long. Please see the template.`</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `3h`</p><p>**Filter**:</p> <p>- {#SERVICE_NAME} MATCHES_REGEX `{$CONSUL.LLD.FILTER.LOCAL_SERVICE_NAME.MATCHES}`</p><p>- {#SERVICE_NAME} NOT_MATCHES_REGEX `{$CONSUL.LLD.FILTER.LOCAL_SERVICE_NAME.NOT_MATCHES}`</p><p>- {#SERVICE_NAMESPACE} MATCHES_REGEX `{$CONSUL.LLD.FILTER.SERVICE_NAMESPACE.MATCHES}`</p><p>- {#SERVICE_NAMESPACE} NOT_MATCHES_REGEX `{$CONSUL.LLD.FILTER.SERVICE_NAMESPACE.NOT_MATCHES}`</p><p>**Overrides:**</p><p>aggregated status<br> - {#TYPE} MATCHES_REGEX `aggregated_status`<br>  - ITEM_PROTOTYPE LIKE `Aggregated status` - DISCOVER</p><br>  - ITEM_PROTOTYPE LIKE `State` - DISCOVER</p><p>checks<br> - {#TYPE} MATCHES_REGEX `service_check`<br>  - ITEM_PROTOTYPE LIKE `Check` - DISCOVER</p> |
|Raft leader metrics discovery |<p>Discover raft metrics for leader nodes.</p> |DEPENDENT |consul.raft.leader.discovery<p>**Preprocessing**:</p><p>- JAVASCRIPT: `The text is too long. Please see the template.`</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `3h`</p> |
|Raft server metrics discovery |<p>Discover raft metrics for server nodes.</p> |DEPENDENT |consul.raft.server.discovery<p>**Preprocessing**:</p><p>- JAVASCRIPT: `The text is too long. Please see the template.`</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `3h`</p> |

## Items collected

|Group|Name|Description|Type|Key and additional info|
|-----|----|-----------|----|---------------------|
|Consul |Consul: Role |<p>Role of current Consul agent.</p> |DEPENDENT |consul.role<p>**Preprocessing**:</p><p>- JSONPATH: `$.Config.Server`</p><p>- BOOL_TO_DECIMAL</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `3h`</p> |
|Consul |Consul: Version |<p>Version of Consul agent.</p> |DEPENDENT |consul.version<p>**Preprocessing**:</p><p>- JSONPATH: `$.Config.Version`</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `3h`</p> |
|Consul |Consul: Number of services |<p>Number of services on current node.</p> |DEPENDENT |consul.services_number<p>**Preprocessing**:</p><p>- JSONPATH: `$.Stats.agent.services`</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `3h`</p> |
|Consul |Consul: Number of checks |<p>Number of checks on current node.</p> |DEPENDENT |consul.checks_number<p>**Preprocessing**:</p><p>- JSONPATH: `$.Stats.agent.checks`</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `3h`</p> |
|Consul |Consul: Number of check monitors |<p>Number of check monitors on current node.</p> |DEPENDENT |consul.check_monitors_number<p>**Preprocessing**:</p><p>- JSONPATH: `$.Stats.agent.check_monitors`</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `3h`</p> |
|Consul |Consul: Process CPU seconds, total |<p>Total user and system CPU time spent in seconds.</p> |DEPENDENT |consul.cpu_seconds_total.rate<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `process_cpu_seconds_total`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- CHANGE_PER_SECOND</p> |
|Consul |Consul: Virtual memory size |<p>Virtual memory size in bytes.</p> |DEPENDENT |consul.virtual_memory_bytes<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `process_virtual_memory_bytes`</p> |
|Consul |Consul: RSS memory usage |<p>Resident memory size in bytes.</p> |DEPENDENT |consul.resident_memory_bytes<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `process_resident_memory_bytes`</p> |
|Consul |Consul: Goroutine count |<p>The number of Goroutines on Consul instance.</p> |DEPENDENT |consul.goroutines<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `go_goroutines`</p> |
|Consul |Consul: Open file descriptors |<p>Number of open file descriptors.</p> |DEPENDENT |consul.process_open_fds<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `process_open_fds`</p> |
|Consul |Consul: Open file descriptors, max |<p>Maximum number of open file descriptors.</p> |DEPENDENT |consul.process_max_fds<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `process_max_fds`</p> |
|Consul |Consul: Client RPC, per second |<p>Number of times per second whenever a Consul agent in client mode makes an RPC request to a Consul server.</p><p>This gives a measure of how much a given agent is loading the Consul servers.</p><p>This is only generated by agents in client mode, not Consul servers.</p> |DEPENDENT |consul.client_rpc<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_client_rpc`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- CHANGE_PER_SECOND</p> |
|Consul |Consul: Client RPC failed ,per second |<p>Number of times per second whenever a Consul agent in client mode makes an RPC request to a Consul server and fails.</p> |DEPENDENT |consul.client_rpc_failed<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_client_rpc_failed`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- CHANGE_PER_SECOND</p> |
|Consul |Consul: TCP connections, accepted per second |<p>This metric counts the number of times a Consul agent has accepted an incoming TCP stream connection per second.</p> |DEPENDENT |consul.memberlist.tcp_accept<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_memberlist_tcp_accept`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- CHANGE_PER_SECOND</p> |
|Consul |Consul: TCP connections, per second |<p>This metric counts the number of times a Consul agent has initiated a push/pull sync with an other agent per second.</p> |DEPENDENT |consul.memberlist.tcp_connect<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_memberlist_tcp_connect`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- CHANGE_PER_SECOND</p> |
|Consul |Consul: TCP send bytes, per second |<p>This metric measures the total number of bytes sent by a Consul agent through the TCP protocol per second.</p> |DEPENDENT |consul.memberlist.tcp_sent<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_memberlist_tcp_sent`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- CHANGE_PER_SECOND</p> |
|Consul |Consul: UDP received bytes, per second |<p>This metric measures the total number of bytes received by a Consul agent through the UDP protocol per second.</p> |DEPENDENT |consul.memberlist.udp_received<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_memberlist_udp_received`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- CHANGE_PER_SECOND</p> |
|Consul |Consul: UDP sent bytes, per second |<p>This metric measures the total number of bytes sent by a Consul agent through the UDP protocol per second.</p> |DEPENDENT |consul.memberlist.udp_sent<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_memberlist_udp_sent`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- CHANGE_PER_SECOND</p> |
|Consul |Consul: GC pause, p90 |<p>The 90 percentile for the number of nanoseconds consumed by stop-the-world garbage collection (GC) pauses since Consul started, in milliseconds.</p> |DEPENDENT |consul.gc_pause.p90<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_runtime_gc_pause_ns{quantile="0.9"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- JAVASCRIPT: `return (isNaN(value)) ? 0 : value; `</p><p>- MULTIPLIER: `1.0E-9`</p> |
|Consul |Consul: GC pause, p50 |<p>The 50 percentile (median) for the number of nanoseconds consumed by stop-the-world garbage collection (GC) pauses since Consul started, in milliseconds.</p> |DEPENDENT |consul.gc_pause.p50<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_runtime_gc_pause_ns{quantile="0.5"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- JAVASCRIPT: `return (isNaN(value)) ? 0 : value; `</p><p>- MULTIPLIER: `1.0E-9`</p> |
|Consul |Consul: Memberlist: degraded |<p>This metric counts the number of times the Consul agent has performed failure detection on another agent at a slower probe rate.</p><p>The agent uses its own health metric as an indicator to perform this action.</p><p>If its health score is low, it means that the node is healthy, and vice versa.</p> |DEPENDENT |consul.memberlist.degraded<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_memberlist_degraded`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Consul |Consul: Memberlist: health score |<p>This metric describes a node's perception of its own health based on how well it is meeting the soft real-time requirements of the protocol.</p><p>This metric ranges from 0 to 8, where 0 indicates "totally healthy".</p> |DEPENDENT |consul.memberlist.health_score<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_memberlist_health_score`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Consul |Consul: Memberlist: gossip, p90 |<p>The 90 percentile for the number of gossips (messages) broadcasted to a set of randomly selected nodes.</p> |DEPENDENT |consul.memberlist.dispatch_log.p90<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_memberlist_gossip{quantile="0.9"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- JAVASCRIPT: `return (isNaN(value)) ? 0 : value; `</p> |
|Consul |Consul: Memberlist: gossip, p50 |<p>The 50 for the number of gossips (messages) broadcasted to a set of randomly selected nodes.</p> |DEPENDENT |consul.memberlist.gossip.p50<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_memberlist_gossip{quantile="0.5"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- JAVASCRIPT: `return (isNaN(value)) ? 0 : value; `</p> |
|Consul |Consul: Memberlist: msg alive |<p>This metric counts the number of alive Consul agents, that the agent has mapped out so far, based on the message information given by the network layer.</p> |DEPENDENT |consul.memberlist.msg.alive<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_memberlist_msg_alive`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Consul |Consul: Memberlist: msg dead |<p>This metric counts the number of times a Consul agent has marked another agent to be a dead node.</p> |DEPENDENT |consul.memberlist.msg.dead<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_memberlist_msg_dead`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Consul |Consul: Memberlist: msg suspect |<p>The number of times a Consul agent suspects another as failed while probing during gossip protocol.</p> |DEPENDENT |consul.memberlist.msg.suspect<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_memberlist_msg_suspect`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Consul |Consul: Memberlist: probe node, p90 |<p>The 90 percentile for the time taken to perform a single round of failure detection on a select Consul agent.</p> |DEPENDENT |consul.memberlist.probe_node.p90<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_memberlist_probeNode{quantile="0.9"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- JAVASCRIPT: `return (isNaN(value)) ? 0 : value; `</p> |
|Consul |Consul: Memberlist: probe node, p50 |<p>The 50 percentile (median) for the time taken to perform a single round of failure detection on a select Consul agent.</p> |DEPENDENT |consul.memberlist.probe_node.p50<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_memberlist_probeNode{quantile="0.5"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- JAVASCRIPT: `return (isNaN(value)) ? 0 : value; `</p> |
|Consul |Consul: Memberlist: push pull node, p90 |<p>The 90 percentile for the number of Consul agents that have exchanged state with this agent.</p> |DEPENDENT |consul.memberlist.push_pull_node.p90<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_memberlist_pushPullNode{quantile="0.9"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- JAVASCRIPT: `return (isNaN(value)) ? 0 : value; `</p> |
|Consul |Consul: Memberlist: push pull node, p50 |<p>The 50 percentile (median) for the number of Consul agents that have exchanged state with this agent.</p> |DEPENDENT |consul.memberlist.push_pull_node.p50<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_memberlist_pushPullNode{quantile="0.5"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- JAVASCRIPT: `return (isNaN(value)) ? 0 : value; `</p> |
|Consul |Consul: KV store: apply, p90 |<p>The 90 percentile for the time it takes to complete an update to the KV store.</p> |DEPENDENT |consul.kvs.apply.p90<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_kvs_apply{quantile="0.9"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- JAVASCRIPT: `return (isNaN(value)) ? 0 : value; `</p> |
|Consul |Consul: KV store: apply, p50 |<p>The 50 percentile (median) for the time it takes to complete an update to the KV store.</p> |DEPENDENT |consul.kvs.apply.p50<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_kvs_apply{quantile="0.5"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- JAVASCRIPT: `return (isNaN(value)) ? 0 : value; `</p> |
|Consul |Consul: KV store: apply, rate |<p>The number of updates to the KV store per second.</p> |DEPENDENT |consul.kvs.apply.rate<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_kvs_apply_count`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- CHANGE_PER_SECOND</p> |
|Consul |Consul: Serf member: flap, rate |<p>Increments when an agent is marked dead and then recovers within a short time period.</p><p>This can be an indicator of overloaded agents, network problems, or configuration errors where agents cannot connect to each other on the required ports.</p><p>Shown as events per second.</p> |DEPENDENT |consul.serf.member.flap.rate<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_serf_member_flap`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- CHANGE_PER_SECOND</p> |
|Consul |Consul: Serf member: failed, rate |<p>Increments when an agent is marked dead.</p><p>This can be an indicator of overloaded agents, network problems, or configuration errors where agents cannot connect to each other on the required ports.</p><p>Shown as events per second.</p> |DEPENDENT |consul.serf.member.failed.rate<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_serf_member_failed`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- CHANGE_PER_SECOND</p> |
|Consul |Consul: Serf member: join, rate |<p>Increments when an agent joins the cluster. If an agent flapped or failed this counter also increments when it re-joins.</p><p>Shown as events per second.</p> |DEPENDENT |consul.serf.member.join.rate<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_serf_member_join`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- CHANGE_PER_SECOND</p> |
|Consul |Consul: Serf member: left, rate |<p>Increments when an agent leaves the cluster. Shown as events per second.</p> |DEPENDENT |consul.serf.member.left.rate<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_serf_member_left`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- CHANGE_PER_SECOND</p> |
|Consul |Consul: Serf member: update, rate |<p>Increments when a Consul agent updates. Shown as events per second.</p> |DEPENDENT |consul.serf.member.update.rate<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_serf_member_update`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- CHANGE_PER_SECOND</p> |
|Consul |Consul: ACL: resolves, rate |<p>The number of ACL resolves per second.</p> |DEPENDENT |consul.acl.resolves.rate<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_acl_ResolveToken_count`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- CHANGE_PER_SECOND</p> |
|Consul |Consul: Catalog: register, rate |<p>The number of catalog register operation per second.</p> |DEPENDENT |consul.catalog.register.rate<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_catalog_register_count`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- CHANGE_PER_SECOND</p> |
|Consul |Consul: Catalog: deregister, rate |<p>The number of catalog deregister operation per second.</p> |DEPENDENT |consul.catalog.deregister.rate<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_catalog_deregister_count`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- CHANGE_PER_SECOND</p> |
|Consul |Consul: Snapshot: append line, p90 |<p>The 90 percentile for the time taken by the Consul agent to append an entry into the existing log.</p> |DEPENDENT |consul.snapshot.append_line.p90<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_serf_snapshot_appendLine{quantile="0.9"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- JAVASCRIPT: `return (isNaN(value)) ? 0 : value; `</p> |
|Consul |Consul: Snapshot: append line, p50 |<p>The 50 percentile (median) for the time taken by the Consul agent to append an entry into the existing log.</p> |DEPENDENT |consul.snapshot.append_line.p50<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_serf_snapshot_appendLine{quantile="0.5"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- JAVASCRIPT: `return (isNaN(value)) ? 0 : value; `</p> |
|Consul |Consul: Snapshot: append line, rate |<p>The number of snapshot appendLine operations per second.</p> |DEPENDENT |consul.snapshot.append_line.rate<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_serf_snapshot_appendLine_count`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- CHANGE_PER_SECOND</p> |
|Consul |Consul: Snapshot: compact, p90 |<p>The 90 percentile for the time taken by the Consul agent to compact a log.</p><p>This operation occurs only when the snapshot becomes large enough to justify the compaction.</p> |DEPENDENT |consul.snapshot.compact.p90<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_serf_snapshot_compact{quantile="0.9"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- JAVASCRIPT: `return (isNaN(value)) ? 0 : value; `</p> |
|Consul |Consul: Snapshot: compact, p50 |<p>The 50 percentile (median) for the time taken by the Consul agent to compact a log.</p><p>This operation occurs only when the snapshot becomes large enough to justify the compaction.</p> |DEPENDENT |consul.snapshot.compact.p50<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_serf_snapshot_compact{quantile="0.5"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- JAVASCRIPT: `return (isNaN(value)) ? 0 : value; `</p> |
|Consul |Consul: Snapshot: compact, rate |<p>The number of snapshot compact operations per second.</p> |DEPENDENT |consul.snapshot.compact.rate<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_serf_snapshot_compact_count`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- CHANGE_PER_SECOND</p> |
|Consul |Consul: Get local services check |<p>Data collection check.</p> |DEPENDENT |consul.get_local_services.check<p>**Preprocessing**:</p><p>- JSONPATH: `$.error`</p><p>⛔️ON_FAIL: `CUSTOM_VALUE -> `</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `3h`</p> |
|Consul |Consul: ["{#SERVICE_NAME}"]: Aggregated status |<p>Aggregated values of all health checks for the service instance.</p> |DEPENDENT |consul.service.aggregated_state["{#SERVICE_ID}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.id == "{#SERVICE_ID}")].status.first()`</p><p>- JAVASCRIPT: `The text is too long. Please see the template.`</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `3h`</p> |
|Consul |Consul: ["{#SERVICE_NAME}"]: Check ["{#SERVICE_CHECK_NAME}"]: Status |<p>Current state of health check for the service.</p> |DEPENDENT |consul.service.check.state["{#SERVICE_ID}/{#SERVICE_CHECK_ID}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.id == "{#SERVICE_ID}")].checks[?(@.CheckID == "{#SERVICE_CHECK_ID}")].Status.first()`</p><p>- JAVASCRIPT: `The text is too long. Please see the template.`</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `3h`</p> |
|Consul |Consul: ["{#SERVICE_NAME}"]: Check ["{#SERVICE_CHECK_NAME}"]: Output |<p>Current output of health check for the service.</p> |DEPENDENT |consul.service.check.output["{#SERVICE_ID}/{#SERVICE_CHECK_ID}"]<p>**Preprocessing**:</p><p>- JSONPATH: `$[?(@.id == "{#SERVICE_ID}")].checks[?(@.CheckID == "{#SERVICE_CHECK_ID}")].Output.first()`</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `3h`</p> |
|Consul |Consul: HTTP request: ["{#HTTP_METHOD}"], p90 |<p>The 90 percentile of how long it takes to service the given HTTP request for the given verb.</p> |DEPENDENT |consul.http.api.p90["{#HTTP_METHOD}"]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_api_http{method = "{#HTTP_METHOD}", quantile = "0.9"}`: `function`: `sum`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Consul |Consul: HTTP request: ["{#HTTP_METHOD}"], p50 |<p>The 50 percentile (median) of how long it takes to service the given HTTP request for the given verb.</p> |DEPENDENT |consul.http.api.p50["{#HTTP_METHOD}"]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_api_http{method = "{#HTTP_METHOD}", quantile = "0.5"}`: `function`: `sum`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Consul |Consul: HTTP request: ["{#HTTP_METHOD}"], rate |<p>Thr number of HTTP request for the given verb per second.</p> |DEPENDENT |consul.http.api.rate["{#HTTP_METHOD}"]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_api_http_count{method = "{#HTTP_METHOD}"}`: `function`: `sum`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- CHANGE_PER_SECOND</p> |
|Consul |Consul: Raft state |<p>Current state of Consul agent.</p> |DEPENDENT |consul.raft.state[{#SINGLETON}]<p>**Preprocessing**:</p><p>- JSONPATH: `$.Stats.raft.state`</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `3h`</p> |
|Consul |Consul: Raft state: leader |<p>Increments when a server becomes a leader.</p> |DEPENDENT |consul.raft.state_leader[{#SINGLETON}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_raft_state_leader`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Consul |Consul: Raft state: candidate |<p>The number of initiated leader elections.</p> |DEPENDENT |consul.raft.state_candidate[{#SINGLETON}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_raft_state_candidate`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Consul |Consul: Raft: apply, rate |<p>Incremented whenever a leader first passes a message into the Raft commit process (called an Apply operation).</p><p>This metric describes the arrival rate of new logs into Raft per second.</p> |DEPENDENT |consul.raft.apply.rate[{#SINGLETON}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_raft_apply`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- CHANGE_PER_SECOND</p> |
|Consul |Consul: Raft state: leader last contact, p90 |<p>The 90 percentile of how long it takes a leader node to communicate with followers during a leader lease check, in milliseconds.</p> |DEPENDENT |consul.raft.leader_last_contact.p90[{#SINGLETON}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_raft_leader_lastContact{quantile="0.9"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- JAVASCRIPT: `return (isNaN(value)) ? 0 : value; `</p> |
|Consul |Consul: Raft state: leader last contact, p50 |<p>The 50 percentile (median) of how long it takes a leader node to communicate with followers during a leader lease check, in milliseconds.</p> |DEPENDENT |consul.raft.leader_last_contact.p50[{#SINGLETON}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_raft_leader_lastContact{quantile="0.5"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- JAVASCRIPT: `return (isNaN(value)) ? 0 : value; `</p> |
|Consul |Consul: Raft state: commit time, p90 |<p>The 90 percentile time it takes to commit a new entry to the raft log on the leader, in milliseconds.</p> |DEPENDENT |consul.raft.commit_time.p90[{#SINGLETON}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_raft_commitTime{quantile="0.9"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- JAVASCRIPT: `return (isNaN(value)) ? 0 : value; `</p> |
|Consul |Consul: Raft state: commit time, p50 |<p>The 50 percentile (median) time it takes to commit a new entry to the raft log on the leader, in milliseconds.</p> |DEPENDENT |consul.raft.commit_time.p50[{#SINGLETON}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_raft_commitTime{quantile="0.5"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- JAVASCRIPT: `return (isNaN(value)) ? 0 : value; `</p> |
|Consul |Consul: Raft state: dispatch log, p90 |<p>The 90 percentile time it takes for the leader to write log entries to disk, in milliseconds.</p> |DEPENDENT |consul.raft.dispatch_log.p90[{#SINGLETON}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_raft_leader_dispatchLog{quantile="0.9"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- JAVASCRIPT: `return (isNaN(value)) ? 0 : value; `</p> |
|Consul |Consul: Raft state: dispatch log, p50 |<p>The 50 percentile (median) time it takes for the leader to write log entries to disk, in milliseconds.</p> |DEPENDENT |consul.raft.dispatch_log.p50[{#SINGLETON}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_raft_leader_dispatchLog{quantile="0.5"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- JAVASCRIPT: `return (isNaN(value)) ? 0 : value; `</p> |
|Consul |Consul: Raft state: dispatch log, rate |<p>The number of times a Raft leader writes a log to disk per second.</p> |DEPENDENT |consul.raft.dispatch_log.rate[{#SINGLETON}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_raft_leader_dispatchLog_count`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- CHANGE_PER_SECOND</p> |
|Consul |Consul: Raft state: commit, rate |<p>The number of commits a new entry to the Raft log on the leader per second.</p> |DEPENDENT |consul.raft.commit_time.rate[{#SINGLETON}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_raft_commitTime_count`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- CHANGE_PER_SECOND</p> |
|Consul |Consul: Autopilot healthy |<p>Tracks the overall health of the local server cluster. 1 if all servers are healthy, 0 if one or more are unhealthy.</p> |DEPENDENT |consul.autopilot.healthy[{#SINGLETON}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `consul_autopilot_healthy`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Zabbix raw items |Consul: Get instance metrics |<p>Get raw metrics from Consul instance /metrics endpoint.</p> |HTTP_AGENT |consul.get_metrics<p>**Preprocessing**:</p><p>- CHECK_NOT_SUPPORTED</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Zabbix raw items |Consul: Get node info |<p>Get configuration and member information of the local agent.</p> |HTTP_AGENT |consul.get_node_info<p>**Preprocessing**:</p><p>- CHECK_NOT_SUPPORTED</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Zabbix raw items |Consul: Get local services |<p>Get all the services that are registered with the local agent and their status.</p> |SCRIPT |consul.get_local_services<p>**Expression**:</p>`The text is too long. Please see the template.` |

## Triggers

|Name|Description|Expression|Severity|Dependencies and additional info|
|----|-----------|----|----|----|
|Consul: Version has been changed |<p>Consul version has changed. Ack to close.</p> |`last(/HashiCorp Consul Node by HTTP/consul.version,#1)<>last(/HashiCorp Consul Node by HTTP/consul.version,#2) and length(last(/HashiCorp Consul Node by HTTP/consul.version))>0` |INFO |<p>Manual close: YES</p> |
|Consul: Current number of open files is too high |<p>"Heavy file descriptor usage (i.e., near the process’s file descriptor limit) indicates a potential file descriptor exhaustion issue."</p> |`min(/HashiCorp Consul Node by HTTP/consul.process_open_fds,5m)/last(/HashiCorp Consul Node by HTTP/consul.process_max_fds)*100>{$CONSUL.OPEN.FDS.MAX.WARN}` |WARNING | |
|Consul: Node's health score is warning |<p>This metric ranges from 0 to 8, where 0 indicates "totally healthy".</p><p>This health score is used to scale the time between outgoing probes, and higher scores translate into longer probing intervals.</p><p>For more details see section IV of the Lifeguard paper: https://arxiv.org/pdf/1707.00788.pdf</p> |`max(/HashiCorp Consul Node by HTTP/consul.memberlist.health_score,#3)>{$CONSUL.NODE.HEALTH_SCORE.MAX.WARN}` |WARNING |<p>**Depends on**:</p><p>- Consul: Node's health score is critical</p> |
|Consul: Node's health score is critical |<p>This metric ranges from 0 to 8, where 0 indicates "totally healthy".</p><p>This health score is used to scale the time between outgoing probes, and higher scores translate into longer probing intervals.</p><p>For more details see section IV of the Lifeguard paper: https://arxiv.org/pdf/1707.00788.pdf</p> |`max(/HashiCorp Consul Node by HTTP/consul.memberlist.health_score,#3)>{$CONSUL.NODE.HEALTH_SCORE.MAX.HIGH}` |AVERAGE | |
|Consul: Failed to get local services |<p>Failed to get local services. Check debug log for more information.</p> |`length(last(/HashiCorp Consul Node by HTTP/consul.get_local_services.check))>0` |WARNING | |
|Consul: Aggregated status is 'warning' |<p>Aggregated state of service on the local agent is 'warning'.</p> |`last(/HashiCorp Consul Node by HTTP/consul.service.aggregated_state["{#SERVICE_ID}"]) = 1` |WARNING | |
|Consul: Aggregated status is 'critical' |<p>Aggregated state of service on the local agent is 'critical'.</p> |`last(/HashiCorp Consul Node by HTTP/consul.service.aggregated_state["{#SERVICE_ID}"]) = 2` |AVERAGE | |

## Feedback

Please report any issues with the template at https://support.zabbix.com

You can also provide feedback, discuss the template or ask for help with it at [ZABBIX forums](https://www.zabbix.com/forum/zabbix-suggestions-and-feedback).

