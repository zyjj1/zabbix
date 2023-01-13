
# Kubernetes cluster state by HTTP

## Overview

For Zabbix version: 6.4 and higher.
The template to monitor Kubernetes state that work without any external scripts. 
It works without external scripts and uses the script item to make HTTP requests to the Kubernetes API.

Template `Kubernetes cluster state by HTTP` — collects metrics by HTTP agent from kube-state-metrics endpoint and Kubernetes API.

Don't forget change macros {$KUBE.API.URL} and {$KUBE.API.TOKEN}.
Also, see the Macros section for a list of macros used to set trigger values.
*NOTE.* Some metrics may not be collected depending on your Kubernetes version and configuration.


This template was tested on:

- Kubernetes, version 1.19

## Setup

> See [Zabbix template operation](https://www.zabbix.com/documentation/6.4/manual/config/templates_out_of_the_box/http) for basic instructions.

Install the [Zabbix Helm Chart](https://git.zabbix.com/projects/ZT/repos/kubernetes-helm/browse) in your Kubernetes cluster.
Internal service metrics are collected from kube-state-metrics endpoint.

Template needs to use Authorization via API token.

Set the `{$KUBE.API.URL}` such as `<scheme>://<host>:<port>`.

Get the generated service account token using the command

`kubectl get secret zabbix-service-account -n monitoring -o jsonpath={.data.token} | base64 -d`

Then set it to the macro `{$KUBE.API.TOKEN}`.  
Set `{$KUBE.STATE.ENDPOINT.NAME}` with Kube state metrics endpoint name. See `kubectl -n monitoring get ep`. Default: `zabbix-kube-state-metrics`.

Also, see the Macros section for a list of macros used to set trigger values.
*NOTE.* Some metrics may not be collected depending on your Kubernetes version and configuration.

Set up the macros to filter the metrics of discovered worker nodes:

- {$KUBE.LLD.FILTER.WORKER_NODE.MATCHES}
- {$KUBE.LLD.FILTER.WORKER_NODE.NOT_MATCHES}

Set up macros to filter metrics by namespace:

- {$KUBE.LLD.FILTER.NAMESPACE.MATCHES}
- {$KUBE.LLD.FILTER.NAMESPACE.NOT_MATCHES}

Set up macros to filter node metrics by nodename:

- {$KUBE.LLD.FILTER.NODE.MATCHES}
- {$KUBE.LLD.FILTER.NODE.NOT_MATCHES}

**Note**, If you have a large cluster, it is highly recommended to set a filter for discoverable namespaces.



## Zabbix configuration

No specific Zabbix configuration is required.

### Macros used

|Name|Description|Default|
|----|-----------|-------|
|{$KUBE.API.COMPONENTSTATUSES.ENDPOINT} |<p>Kubernetes API componentstatuses endpoint /api/v1/componentstatuses</p> |`/api/v1/componentstatuses` |
|{$KUBE.API.LIVEZ.ENDPOINT} |<p>Kubernetes API livez endpoint /livez</p> |`/livez` |
|{$KUBE.API.READYZ.ENDPOINT} |<p>Kubernetes API readyz endpoint /readyz</p> |`/readyz` |
|{$KUBE.API.TOKEN} |<p>Service account bearer token</p> |`` |
|{$KUBE.API.URL} |<p>Kubernetes API endpoint URL in the format <scheme>://<host>:<port></p> |`https://localhost:6443` |
|{$KUBE.API_SERVER.PORT} |<p>Kubernetes API servers metrics endpoint port. Used in ControlPlane LLD.</p> |`6443` |
|{$KUBE.API_SERVER.SCHEME} |<p>Kubernetes API servers metrics endpoint scheme. Used in ControlPlane LLD.</p> |`https` |
|{$KUBE.CONTROLLER_MANAGER.PORT} |<p>Kubernetes Controller manager metrics endpoint port. Used in ControlPlane LLD.</p> |`10252` |
|{$KUBE.CONTROLLER_MANAGER.SCHEME} |<p>Kubernetes Controller manager metrics endpoint scheme. Used in ControlPlane LLD.</p> |`http` |
|{$KUBE.KUBELET.PORT} |<p>Kubernetes Kubelet manager metrics endpoint port. Used in Kubelet LLD.</p> |`10250` |
|{$KUBE.KUBELET.SCHEME} |<p>Kubernetes Kubelet manager metrics endpoint scheme. Used in Kubelet LLD.</p> |`https` |
|{$KUBE.LLD.FILTER.NAMESPACE.MATCHES} |<p>Filter of discoverable pods by namespace</p> |`.*` |
|{$KUBE.LLD.FILTER.NAMESPACE.NOT_MATCHES} |<p>Filter to exclude discovered pods by namespace</p> |`CHANGE_IF_NEEDED` |
|{$KUBE.LLD.FILTER.NODE.MATCHES} |<p>Filter of discoverable nodes by nodename</p> |`.*` |
|{$KUBE.LLD.FILTER.NODE.NOT_MATCHES} |<p>Filter to exclude discovered nodes by nodename</p> |`CHANGE_IF_NEEDED` |
|{$KUBE.LLD.FILTER.WORKER_NODE.MATCHES} |<p>Filter of discoverable worker nodes by nodename</p> |`.*` |
|{$KUBE.LLD.FILTER.WORKER_NODE.NOT_MATCHES} |<p>Filter to exclude discovered worker nodes by nodename</p> |`CHANGE_IF_NEEDED` |
|{$KUBE.SCHEDULER.PORT} |<p>Kubernetes Scheduler manager metrics endpoint port. Used in ControlPlane LLD.</p> |`10251` |
|{$KUBE.SCHEDULER.SCHEME} |<p>Kubernetes Scheduler manager metrics endpoint scheme. Used in ControlPlane LLD.</p> |`http` |
|{$KUBE.STATE.ENDPOINT.NAME} |<p>Kubernetes state endpoint name</p> |`zabbix-kube-state-metrics` |

## Template links

There are no template links in this template.

## Discovery rules

|Name|Description|Type|Key and additional info|
|----|-----------|----|----|
|API servers discovery |<p>-</p> |DEPENDENT |kube.api_servers.discovery |
|Component statuses discovery |<p>-</p> |DEPENDENT |kube.componentstatuses.discovery<p>**Preprocessing**:</p><p>- JAVASCRIPT<p>- DISCARD_UNCHANGED_HEARTBEAT |
|Controller manager nodes discovery |<p>-</p> |DEPENDENT |kube.controller_manager.discovery |
|CronJob discovery |<p>-</p> |DEPENDENT |kube.cronjob.discovery<p>**Preprocessing**:</p><p>- PROMETHEUS_TO_JSON<p>- JAVASCRIPT<p>- DISCARD_UNCHANGED_HEARTBEAT<p>**Filter**:</p>AND <p>- {#NAMESPACE} MATCHES_REGEX `{$KUBE.LLD.FILTER.NAMESPACE.MATCHES}`</p><p>- {#NAMESPACE} NOT_MATCHES_REGEX `{$KUBE.LLD.FILTER.NAMESPACE.NOT_MATCHES}`</p> |
|Daemonset discovery |<p>-</p> |DEPENDENT |kube.daemonset.discovery<p>**Preprocessing**:</p><p>- PROMETHEUS_TO_JSON<p>- JAVASCRIPT<p>- DISCARD_UNCHANGED_HEARTBEAT<p>**Filter**:</p>AND <p>- {#NAMESPACE} MATCHES_REGEX `{$KUBE.LLD.FILTER.NAMESPACE.MATCHES}`</p><p>- {#NAMESPACE} NOT_MATCHES_REGEX `{$KUBE.LLD.FILTER.NAMESPACE.NOT_MATCHES}`</p> |
|Deployment discovery |<p>-</p> |DEPENDENT |kube.deployment.discovery<p>**Preprocessing**:</p><p>- PROMETHEUS_TO_JSON<p>- JAVASCRIPT<p>- DISCARD_UNCHANGED_HEARTBEAT<p>**Filter**:</p>AND <p>- {#NAMESPACE} MATCHES_REGEX `{$KUBE.LLD.FILTER.NAMESPACE.MATCHES}`</p><p>- {#NAMESPACE} NOT_MATCHES_REGEX `{$KUBE.LLD.FILTER.NAMESPACE.NOT_MATCHES}`</p> |
|Endpoint discovery |<p>-</p> |DEPENDENT |kube.endpoint.discovery<p>**Preprocessing**:</p><p>- PROMETHEUS_TO_JSON<p>- JAVASCRIPT<p>- DISCARD_UNCHANGED_HEARTBEAT<p>**Filter**:</p>AND <p>- {#NAMESPACE} MATCHES_REGEX `{$KUBE.LLD.FILTER.NAMESPACE.MATCHES}`</p><p>- {#NAMESPACE} NOT_MATCHES_REGEX `{$KUBE.LLD.FILTER.NAMESPACE.NOT_MATCHES}`</p> |
|Job discovery |<p>-</p> |DEPENDENT |kube.job.discovery<p>**Preprocessing**:</p><p>- PROMETHEUS_TO_JSON<p>- JAVASCRIPT<p>- DISCARD_UNCHANGED_HEARTBEAT<p>**Filter**:</p>AND <p>- {#NAMESPACE} MATCHES_REGEX `{$KUBE.LLD.FILTER.NAMESPACE.MATCHES}`</p><p>- {#NAMESPACE} NOT_MATCHES_REGEX `{$KUBE.LLD.FILTER.NAMESPACE.NOT_MATCHES}`</p> |
|Kubelet discovery |<p>-</p> |DEPENDENT |kube.kubelet.discovery<p>**Filter**:</p>AND <p>- {#NAME} MATCHES_REGEX `{$KUBE.LLD.FILTER.WORKER_NODE.MATCHES}`</p><p>- {#NAME} NOT_MATCHES_REGEX `{$KUBE.LLD.FILTER.WORKER_NODE.NOT_MATCHES}`</p> |
|Livez discovery |<p>-</p> |DEPENDENT |kube.livez.discovery<p>**Preprocessing**:</p><p>- JAVASCRIPT<p>- DISCARD_UNCHANGED_HEARTBEAT |
|Node discovery |<p>-</p> |DEPENDENT |kube.node.discovery<p>**Preprocessing**:</p><p>- PROMETHEUS_TO_JSON<p>- JAVASCRIPT<p>- DISCARD_UNCHANGED_HEARTBEAT<p>**Filter**:</p>AND <p>- {#NAME} MATCHES_REGEX `{$KUBE.LLD.FILTER.NODE.MATCHES}`</p><p>- {#NAME} NOT_MATCHES_REGEX `{$KUBE.LLD.FILTER.NODE.NOT_MATCHES}`</p> |
|Pod discovery |<p>-</p> |DEPENDENT |kube.pod.discovery<p>**Preprocessing**:</p><p>- PROMETHEUS_TO_JSON<p>- JAVASCRIPT<p>- DISCARD_UNCHANGED_HEARTBEAT<p>**Filter**:</p>AND <p>- {#NAMESPACE} MATCHES_REGEX `{$KUBE.LLD.FILTER.NAMESPACE.MATCHES}`</p><p>- {#NAMESPACE} NOT_MATCHES_REGEX `{$KUBE.LLD.FILTER.NAMESPACE.NOT_MATCHES}`</p> |
|PodDisruptionBudget discovery |<p>-</p> |DEPENDENT |kube.pdb.discovery<p>**Preprocessing**:</p><p>- PROMETHEUS_TO_JSON<p>- JAVASCRIPT<p>- DISCARD_UNCHANGED_HEARTBEAT<p>**Filter**:</p>AND <p>- {#NAMESPACE} MATCHES_REGEX `{$KUBE.LLD.FILTER.NAMESPACE.MATCHES}`</p><p>- {#NAMESPACE} NOT_MATCHES_REGEX `{$KUBE.LLD.FILTER.NAMESPACE.NOT_MATCHES}`</p> |
|PVC discovery |<p>-</p> |DEPENDENT |kube.pvc.discovery<p>**Preprocessing**:</p><p>- PROMETHEUS_TO_JSON<p>- JAVASCRIPT<p>- DISCARD_UNCHANGED_HEARTBEAT<p>**Filter**:</p>AND <p>- {#NAMESPACE} MATCHES_REGEX `{$KUBE.LLD.FILTER.NAMESPACE.MATCHES}`</p><p>- {#NAMESPACE} NOT_MATCHES_REGEX `{$KUBE.LLD.FILTER.NAMESPACE.NOT_MATCHES}`</p> |
|Readyz discovery |<p>-</p> |DEPENDENT |kube.readyz.discovery<p>**Preprocessing**:</p><p>- JAVASCRIPT<p>- DISCARD_UNCHANGED_HEARTBEAT |
|Replicaset discovery |<p>-</p> |DEPENDENT |kube.replicaset.discovery<p>**Preprocessing**:</p><p>- PROMETHEUS_TO_JSON<p>- JAVASCRIPT<p>- DISCARD_UNCHANGED_HEARTBEAT<p>**Filter**:</p>AND <p>- {#NAMESPACE} MATCHES_REGEX `{$KUBE.LLD.FILTER.NAMESPACE.MATCHES}`</p><p>- {#NAMESPACE} NOT_MATCHES_REGEX `{$KUBE.LLD.FILTER.NAMESPACE.NOT_MATCHES}`</p> |
|Scheduler servers nodes discovery |<p>-</p> |DEPENDENT |kube.scheduler.discovery |
|Statefulset discovery |<p>-</p> |DEPENDENT |kube.statefulset.discovery<p>**Preprocessing**:</p><p>- PROMETHEUS_TO_JSON<p>- JAVASCRIPT<p>- DISCARD_UNCHANGED_HEARTBEAT<p>**Filter**:</p>AND <p>- {#NAMESPACE} MATCHES_REGEX `{$KUBE.LLD.FILTER.NAMESPACE.MATCHES}`</p><p>- {#NAMESPACE} NOT_MATCHES_REGEX `{$KUBE.LLD.FILTER.NAMESPACE.NOT_MATCHES}`</p> |

## Items collected

|Group|Name|Description|Type|Key and additional info|
|-----|----|-----------|----|---------------------|
|Kubernetes |Kubernetes: Get state metrics |<p>Collecting Kubernetes metrics from kube-state-metrics.</p> |SCRIPT |kube.state.metrics<p>**Expression**:</p>`The text is too long. Please see the template.` |
|Kubernetes |Kubernetes: Control plane LLD |<p>Generation of data for Control plane discovery rules.</p> |SCRIPT |kube.control_plane.lld<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `3h`</p><p>**Expression**:</p>`The text is too long. Please see the template.` |
|Kubernetes |Kubernetes: Node LLD |<p>Generation of data for Kubelet discovery rules.</p> |SCRIPT |kube.node.lld<p>**Preprocessing**:</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `3h`</p><p>**Expression**:</p>`The text is too long. Please see the template.` |
|Kubernetes |Kubernetes: Get component statuses |<p>-</p> |HTTP_AGENT |kube.componentstatuses<p>**Preprocessing**:</p><p>- CHECK_NOT_SUPPORTED</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Get readyz |<p>-</p> |HTTP_AGENT |kube.readyz<p>**Preprocessing**:</p><p>- JAVASCRIPT: `var output = [],     component; value.split(/\n/).forEach(function (entry) {     if (component = entry.match(/^\[.+\](.+)\s(\w+)$/)) {         output.push({             name: component[1],             value: component[2]         });     } }); return JSON.stringify(output); `</p> |
|Kubernetes |Kubernetes: Get livez |<p>-</p> |HTTP_AGENT |kube.livez<p>**Preprocessing**:</p><p>- JAVASCRIPT: `var output = [],     component; value.split(/\n/).forEach(function (entry) {     if (component = entry.match(/^\[.+\](.+)\s(\w+)$/)) {         output.push({             name: component[1],             value: component[2]         });     } }); return JSON.stringify(output); `</p> |
|Kubernetes |Kubernetes: Namespace count |<p>The number of namespaces.</p> |DEPENDENT |kube.namespace.count<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_namespace_created`: `function`: `count`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: CronJob count |<p>Number of cronjobs.</p> |DEPENDENT |kube.cronjob.count<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_cronjob_created`: `function`: `count`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Job count |<p>Number of jobs(generated by cronjob + job).</p> |DEPENDENT |kube.job.count<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_job_created`: `function`: `count`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Endpoint count |<p>Number of endpoints.</p> |DEPENDENT |kube.endpoint.count<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_endpoint_created`: `function`: `count`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Deployment count |<p>The number of deployments.</p> |DEPENDENT |kube.deployment.count<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_deployment_created`: `function`: `count`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Service count |<p>The number of services.</p> |DEPENDENT |kube.service.count<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_service_created`: `function`: `count`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Statefulset count |<p>The number of statefulsets.</p> |DEPENDENT |kube.statefulset.count<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_statefulset_created`: `function`: `count`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Node count |<p>The number of nodes.</p> |DEPENDENT |kube.node.count<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_node_created`: `function`: `count`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Daemonset [{#NAME}]: Ready |<p>The number of nodes that should be running the daemon pod and have one or more running and ready.</p> |DEPENDENT |kube.daemonset.ready[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_daemonset_status_number_ready{namespace="{#NAMESPACE}", daemonset="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Daemonset [{#NAME}]: Scheduled |<p>The number of nodes running at least one daemon pod and are supposed to.</p> |DEPENDENT |kube.daemonset.scheduled[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_daemonset_status_current_number_scheduled{namespace="{#NAMESPACE}", daemonset="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Daemonset [{#NAME}]: Desired |<p>The number of nodes that should be running the daemon pod.</p> |DEPENDENT |kube.daemonset.desired[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_daemonset_status_desired_number_scheduled{namespace="{#NAMESPACE}", daemonset="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Daemonset [{#NAME}]: Misscheduled |<p>The number of nodes running a daemon pod but are not supposed to.</p> |DEPENDENT |kube.daemonset.misscheduled[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_daemonset_status_number_misscheduled{namespace="{#NAMESPACE}", daemonset="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Daemonset [{#NAME}]: Updated number scheduled |<p>The total number of nodes that are running updated daemon pod.</p> |DEPENDENT |kube.daemonset.updated[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_daemonset_status_updated_number_scheduled{namespace="{#NAMESPACE}", daemonset="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] PVC [{#NAME}] Status phase: Available |<p>Persistent volume claim is currently in Active phase.</p> |DEPENDENT |kube.pvc.status_phase.active[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_persistentvolumeclaim_status_phase{namespace="{#NAMESPACE}", name="{#NAME}", phase="Available"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] PVC [{#NAME}] Status phase: Lost |<p>Persistent volume claim is currently in Lost phase.</p> |DEPENDENT |kube.pvc.status_phase.lost[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_persistentvolumeclaim_status_phase{namespace="{#NAMESPACE}", name="{#NAME}", phase="Lost"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] PVC [{#NAME}] Status phase: Bound |<p>Persistent volume claim is currently in Bound phase.</p> |DEPENDENT |kube.pvc.status_phase.bound[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_persistentvolumeclaim_status_phase{namespace="{#NAMESPACE}", name="{#NAME}", phase="Bound"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] PVC [{#NAME}] Status phase: Pending |<p>Persistent volume claim is currently in Pending phase.</p> |DEPENDENT |kube.pvc.status_phase.pending[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_persistentvolumeclaim_status_phase{namespace="{#NAMESPACE}", name="{#NAME}", phase="Pending"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] PVC [{#NAME}] Requested storage |<p>The capacity of storage requested by the persistent volume claim.</p> |DEPENDENT |kube.pvc.requested.storage[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_persistentvolumeclaim_resource_requests_storage_bytes{namespace="{#NAMESPACE}", name="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Status phase: Pending, sum |<p>Persistent volume claim is currently in Pending phase.</p> |DEPENDENT |kube.pvc.status_phase.pending.sum[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_persistentvolumeclaim_status_phase{namespace="{#NAMESPACE}", persistentvolumeclaim="{#NAME}", phase="Pending"}`: `function`: `sum`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Status phase: Active, sum |<p>Persistent volume claim is currently in Active phase.</p> |DEPENDENT |kube.pvc.status_phase.active.sum[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_persistentvolumeclaim_status_phase{namespace="{#NAMESPACE}", persistentvolumeclaim="{#NAME}", phase="Active"}`: `function`: `sum`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Status phase: Bound, sum |<p>Persistent volume claim is currently in Bound phase.</p> |DEPENDENT |kube.pvc.status_phase.bound.sum[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_persistentvolumeclaim_status_phase{namespace="{#NAMESPACE}", persistentvolumeclaim="{#NAME}", phase="Bound"}`: `function`: `sum`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Status phase: Lost, sum |<p>Persistent volume claim is currently in Lost phase.</p> |DEPENDENT |kube.pvc.status_phase.lost.sum[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_persistentvolumeclaim_status_phase{namespace="{#NAMESPACE}",persistentvolumeclaim="{#NAME}", phase="Lost"}`: `function`: `sum`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Deployment [{#NAME}]: Paused |<p>Whether the deployment is paused and will not be processed by the deployment controller.</p> |DEPENDENT |kube.deployment.spec_paused[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_deployment_spec_paused{namespace="{#NAMESPACE}", deployment="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Deployment [{#NAME}]: Replicas desired |<p>Number of desired pods for a deployment.</p> |DEPENDENT |kube.deployment.replicas_desired[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_deployment_spec_replicas{namespace="{#NAMESPACE}", deployment="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Deployment [{#NAME}]: Rollingupdate max unavailable |<p>Maximum number of unavailable replicas during a rolling update of a deployment.</p> |DEPENDENT |kube.deployment.rollingupdate.max_unavailable[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_deployment_spec_strategy_rollingupdate_max_unavailable{namespace="{#NAMESPACE}", deployment="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Deployment [{#NAME}]: Replicas |<p>The number of replicas per deployment.</p> |DEPENDENT |kube.deployment.replicas[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_deployment_status_replicas{namespace="{#NAMESPACE}", deployment="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Deployment [{#NAME}]: Replicas available |<p>The number of available replicas per deployment.</p> |DEPENDENT |kube.deployment.replicas_available[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_deployment_status_replicas_available{namespace="{#NAMESPACE}", deployment="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Deployment [{#NAME}]: Replicas unavailable |<p>The number of unavailable replicas per deployment.</p> |DEPENDENT |kube.deployment.replicas_unavailable[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_deployment_status_replicas_unavailable{namespace="{#NAMESPACE}", deployment="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Deployment [{#NAME}]: Replicas updated |<p>The number of updated replicas per deployment.</p> |DEPENDENT |kube.deployment.replicas_updated[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_deployment_status_replicas_updated{namespace="{#NAMESPACE}", deployment="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Endpoint [{#NAME}]: Address available |<p>Number of addresses available in endpoint.</p> |DEPENDENT |kube.endpoint.address_available[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_endpoint_address_available{namespace="{#NAMESPACE}", endpoint="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Endpoint [{#NAME}]: Address not ready |<p>Number of addresses not ready in endpoint.</p> |DEPENDENT |kube.endpoint.address_not_ready[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_endpoint_address_not_ready{namespace="{#NAMESPACE}", endpoint="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Endpoint [{#NAME}]: Age |<p>Endpoint age (number of seconds since creation).</p> |DEPENDENT |kube.endpoint.age[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_endpoint_created{namespace="{#NAMESPACE}", endpoint="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- JAVASCRIPT: `return (Math.floor(Date.now()/1000)-Number(value))`</p> |
|Kubernetes |Kubernetes: Node [{#NAME}]: CPU allocatable |<p>The CPU resources of a node that are available for scheduling.</p> |DEPENDENT |kube.node.cpu_allocatable[{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_node_status_allocatable{node="{#NAME}", resource="cpu"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Node [{#NAME}]: Memory allocatable |<p>The Memory resources of a node that are available for scheduling.</p> |DEPENDENT |kube.node.memory_allocatable[{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_node_status_allocatable{node="{#NAME}", resource="memory"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Node [{#NAME}]: Pods allocatable |<p>The Pods resources of a node that are available for scheduling.</p> |DEPENDENT |kube.node.pods_allocatable[{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_node_status_allocatable{node="{#NAME}", resource="pods"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Node [{#NAME}]: Ephemeral storage allocatable |<p>The allocatable ephemeral-storage of a node that is available for scheduling.</p> |DEPENDENT |kube.node.ephemeral_storage_allocatable[{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_node_status_allocatable{node="{#NAME}", resource="ephemeral_storage"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Node [{#NAME}]: CPU capacity |<p>The capacity for CPU resources of a node.</p> |DEPENDENT |kube.node.cpu_capacity[{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_node_status_capacity{node="{#NAME}", resource="cpu"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Node [{#NAME}]: Memory capacity |<p>The capacity for Memory resources of a node.</p> |DEPENDENT |kube.node.memory_capacity[{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_node_status_capacity{node="{#NAME}", resource="memory"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Node [{#NAME}]: Ephemeral storage capacity |<p>The ephemeral-storage capacity of a node.</p> |DEPENDENT |kube.node.ephemeral_storage_capacity[{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_node_status_capacity{node="{#NAME}", resource="ephemeral_storage"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Node [{#NAME}]: Pods capacity |<p>The capacity for Pods resources of a node.</p> |DEPENDENT |kube.node.pods_capacity[{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_node_status_capacity{node="{#NAME}", resource="pods"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Pod [{#NAME}] Phase: Pending |<p>Pod is in pending state.</p> |DEPENDENT |kube.pod.phase.pending[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_pod_status_phase{pod="{#NAME}", phase="Pending"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Pod [{#NAME}] Phase: Succeeded |<p>Pod is in succeeded state.</p> |DEPENDENT |kube.pod.phase.succeeded[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_pod_status_phase{pod="{#NAME}", phase="Succeeded"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Pod [{#NAME}] Phase: Failed |<p>Pod is in failed state.</p> |DEPENDENT |kube.pod.phase.failed[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_pod_status_phase{pod="{#NAME}", phase="Failed"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Pod [{#NAME}] Phase: Unknown |<p>Pod is in unknown state.</p> |DEPENDENT |kube.pod.phase.unknown[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_pod_status_phase{pod="{#NAME}", phase="Unknown"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Pod [{#NAME}] Phase: Running |<p>Pod is in unknown state.</p> |DEPENDENT |kube.pod.phase.running[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_pod_status_phase{pod="{#NAME}", phase="Running"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Pod [{#NAME}]: Containers terminated |<p>Describes whether the container is currently in terminated state.</p> |DEPENDENT |kube.pod.containers_terminated[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_pod_container_status_terminated{pod="{#NAME}"}`: `function`: `sum`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Pod [{#NAME}]: Containers waiting |<p>Describes whether the container is currently in waiting state.</p> |DEPENDENT |kube.pod.containers_waiting[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_pod_container_status_waiting{pod="{#NAME}"}`: `function`: `sum`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Pod [{#NAME}]: Containers ready |<p>Describes whether the containers readiness check succeeded.</p> |DEPENDENT |kube.pod.containers_ready[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_pod_container_status_ready{pod="{#NAME}"}`: `function`: `sum`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Pod [{#NAME}]: Containers restarts |<p>The number of container restarts.</p> |DEPENDENT |kube.pod.containers_restarts[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_pod_container_status_restarts_total{pod="{#NAME}"}`: `function`: `sum`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Pod [{#NAME}]: Containers running |<p>Describes whether the container is currently in running state.</p> |DEPENDENT |kube.pod.containers_running[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_pod_container_status_running{pod="{#NAME}"}`: `function`: `sum`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Pod [{#NAME}]: Ready |<p>Describes whether the pod is ready to serve requests.</p> |DEPENDENT |kube.pod.ready[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_pod_status_ready{pod="{#NAME}", condition="true"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Pod [{#NAME}]: Scheduled |<p>Describes the status of the scheduling process for the pod.</p> |DEPENDENT |kube.pod.scheduled[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_pod_status_scheduled{pod="{#NAME}", condition="true"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Pod [{#NAME}]: Unschedulable |<p>Describes the unschedulable status for the pod.</p> |DEPENDENT |kube.pod.unschedulable[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_pod_status_unschedulable{pod="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Pod [{#NAME}]: Containers CPU limits |<p>The limit on CPU cores to be used by a container.</p> |DEPENDENT |kube.pod.containers.limits.cpu[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_pod_container_resource_limits{pod="{#NAME}", resource="cpu"}`: `function`: `sum`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Pod [{#NAME}]: Containers memory limits |<p>The limit on memory to be used by a container.</p> |DEPENDENT |kube.pod.containers.limits.memory[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_pod_container_resource_limits{pod="{#NAME}", resource="memory"}`: `function`: `sum`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Pod [{#NAME}]: Containers CPU requests |<p>The number of requested cpu cores by a container.</p> |DEPENDENT |kube.pod.containers.requests.cpu[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_pod_container_resource_requests{pod="{#NAME}", resource="cpu"}`: `function`: `sum`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Pod [{#NAME}]: Containers memory requests |<p>The number of requested memory bytes by a container.</p> |DEPENDENT |kube.pod.containers.requests.memory[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_pod_container_resource_requests{pod="{#NAME}", resource="memory"}`: `function`: `sum`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Replicaset [{#NAME}]: Replicas |<p>The number of replicas per ReplicaSet.</p> |DEPENDENT |kube.replicaset.replicas[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_replicaset_status_replicas{namespace="{#NAMESPACE}", replicaset="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Replicaset [{#NAME}]: Desired replicas |<p>Number of desired pods for a ReplicaSet.</p> |DEPENDENT |kube.replicaset.replicas_desired[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_replicaset_spec_replicas{namespace="{#NAMESPACE}", replicaset="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Replicaset [{#NAME}]: Fully labeled replicas |<p>The number of fully labeled replicas per ReplicaSet.</p> |DEPENDENT |kube.replicaset.fully_labeled_replicas[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_replicaset_status_fully_labeled_replicas{namespace="{#NAMESPACE}", replicaset="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Replicaset [{#NAME}]: Ready |<p>The number of ready replicas per ReplicaSet.</p> |DEPENDENT |kube.replicaset.ready[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_replicaset_status_ready_replicas{namespace="{#NAMESPACE}", replicaset="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Statefulset [{#NAME}]: Replicas |<p>The number of replicas per StatefulSet.</p> |DEPENDENT |kube.statefulset.replicas[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_statefulset_status_replicas{namespace="{#NAMESPACE}", statefulset="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Statefulset [{#NAME}]: Desired replicas |<p>Number of desired pods for a StatefulSet.</p> |DEPENDENT |kube.statefulset.replicas_desired[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_statefulset_replicas{namespace="{#NAMESPACE}", statefulset="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Statefulset [{#NAME}]: Current replicas |<p>The number of current replicas per StatefulSet.</p> |DEPENDENT |kube.statefulset.replicas_current[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_statefulset_status_replicas_current{namespace="{#NAMESPACE}", statefulset="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Statefulset [{#NAME}]: Ready replicas |<p>The number of ready replicas per StatefulSet.</p> |DEPENDENT |kube.statefulset.replicas_ready[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_statefulset_status_replicas_ready{namespace="{#NAMESPACE}", statefulset="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Statefulset [{#NAME}]: Updated replicas |<p>The number of updated replicas per StatefulSet.</p> |DEPENDENT |kube.statefulset.replicas_updated[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_statefulset_status_replicas_updated{namespace="{#NAMESPACE}", statefulset="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] PodDisruptionBudget [{#NAME}]: Pods healthy |<p>Current number of healthy pods.</p> |DEPENDENT |kube.pdb.pods_healthy[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_poddisruptionbudget_status_current_healthy{namespace="{#NAMESPACE}", poddisruptionbudget="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] PodDisruptionBudget [{#NAME}]: Pods desired |<p>Minimum desired number of healthy pods.</p> |DEPENDENT |kube.pdb.pods_desired[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_poddisruptionbudget_status_desired_healthy{namespace="{#NAMESPACE}", poddisruptionbudget="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] PodDisruptionBudget [{#NAME}]: Disruptions allowed |<p>Number of pod disruptions that are allowed.</p> |DEPENDENT |kube.pdb.disruptions_allowed[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_poddisruptionbudget_status_pod_disruptions_allowed{namespace="{#NAMESPACE}", poddisruptionbudget="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] PodDisruptionBudget [{#NAME}]: Pods total |<p>Total number of pods counted by this disruption budget.</p> |DEPENDENT |kube.pdb.pods_total[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_poddisruptionbudget_status_expected_pods{namespace="{#NAMESPACE}", poddisruptionbudget="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] CronJob [{#NAME}]: Suspend |<p>Suspend flag tells the controller to suspend subsequent executions.</p> |DEPENDENT |kube.cronjob.spec_suspend[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_cronjob_spec_suspend{namespace="{#NAMESPACE}", cronjob="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- DISCARD_UNCHANGED_HEARTBEAT: `1h`</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] CronJob [{#NAME}]: Active |<p>Active holds pointers to currently running jobs.</p> |DEPENDENT |kube.cronjob.status_active[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_cronjob_status_active{namespace="{#NAMESPACE}", cronjob="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] CronJob [{#NAME}]: Last schedule |<p>LastScheduleTime keeps information of when was the last time the job was successfully scheduled.</p> |DEPENDENT |kube.cronjob.last_schedule_time[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_cronjob_status_last_schedule_time{namespace="{#NAMESPACE}", cronjob="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- JAVASCRIPT: `return new Date(value * 1000).toString().slice(0,19);`</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] CronJob [{#NAME}]: Next schedule |<p>Next time the cronjob should be scheduled. The time after lastScheduleTime, or after the cron job's creation time if it's never been scheduled. Use this to determine if the job is delayed.</p> |DEPENDENT |kube.cronjob.next_schedule_time[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_cronjob_next_schedule_time{namespace="{#NAMESPACE}", cronjob="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p><p>- JAVASCRIPT: `return new Date(value * 1000).toString().slice(0,19);`</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] CronJob [{#NAME}]: Failed |<p>The number of pods which reached Phase Failed and the reason for failure.</p> |DEPENDENT |kube.cronjob.status_failed[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_job_status_failed{namespace="{#NAMESPACE}", job_name=~"{#NAME}-*"}`: `function`: `sum`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] CronJob [{#NAME}]: Succeeded |<p>The number of pods which reached Phase Succeeded.</p> |DEPENDENT |kube.cronjob.status_succeeded[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_job_status_succeeded{namespace="{#NAMESPACE}", job_name=~"{#NAME}-*"}`: `function`: `sum`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] CronJob [{#NAME}]: Completion succeeded |<p>Number of job has completed its execution.</p> |DEPENDENT |kube.cronjob.completion.succeeded[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_job_complete{namespace="{#NAMESPACE}", job_name=~"{#NAME}-*", condition="true"}`: `function`: `sum`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] CronJob [{#NAME}]: Completion failed |<p>Number of job has failed its execution.</p> |DEPENDENT |kube.cronjob.completion.failed[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_job_failed{namespace="{#NAMESPACE}", job_name=~"{#NAME}-*", condition="true"}`: `function`: `sum`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Job [{#NAME}]: Failed |<p>The number of pods which reached Phase Failed and the reason for failure.</p> |DEPENDENT |kube.job.status_failed[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_job_status_failed{namespace="{#NAMESPACE}", job_name="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Job [{#NAME}]: Succeeded |<p>The number of pods which reached Phase Succeeded.</p> |DEPENDENT |kube.job.status_succeeded[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_job_status_succeeded{namespace="{#NAMESPACE}", job_name="{#NAME}"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Job [{#NAME}]: Completion succeeded |<p>Number of job has completed its execution.</p> |DEPENDENT |kube.job.completion.succeeded[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_job_complete{namespace="{#NAMESPACE}", job_name="{#NAME}", condition="true"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Namespace [{#NAMESPACE}] Job [{#NAME}]: Completion failed |<p>Number of job has failed its execution.</p> |DEPENDENT |kube.job.completion.failed[{#NAMESPACE}/{#NAME}]<p>**Preprocessing**:</p><p>- PROMETHEUS_PATTERN: `kube_job_failed{namespace="{#NAMESPACE}", job_name="{#NAME}", condition="true"}`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Component [{#NAME}]: Healthy |<p>Cluster component healthy.</p> |DEPENDENT |kube.componentstatuses.healthy[{#NAME}]<p>**Preprocessing**:</p><p>- JSONPATH: `$.items.[?(@.metadata.name == "{#NAME}")].conditions[?(@.type == "Healthy")].status.first()`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Readyz [{#NAME}]: Healthcheck |<p>Result of readyz healthcheck for component.</p> |DEPENDENT |kube.readyz.healthcheck[{#NAME}]<p>**Preprocessing**:</p><p>- JSONPATH: `$.[?(@.name == "{#NAME}")].value.first()`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |
|Kubernetes |Kubernetes: Livez [{#NAME}]: Healthcheck |<p>Result of livez healthcheck for component.</p> |DEPENDENT |kube.livez.healthcheck[{#NAME}]<p>**Preprocessing**:</p><p>- JSONPATH: `$.[?(@.name == "{#NAME}")].value.first()`</p><p>⛔️ON_FAIL: `DISCARD_VALUE -> `</p> |

## Triggers

|Name|Description|Expression|Severity|Dependencies and additional info|
|----|-----------|----|----|----|
|Kubernetes: NS [{#NAMESPACE}] PVC [{#NAME}]: PVC is pending |<p>-</p> |`min(/Kubernetes cluster state by HTTP/kube.pvc.status_phase.pending[{#NAMESPACE}/{#NAME}],2m)>0` |WARNING | |
|Kubernetes: Namespace [{#NAMESPACE}] Deployment [{#NAME}]: Deployment replicas mismatch |<p>-</p> |`(last(/Kubernetes cluster state by HTTP/kube.deployment.replicas[{#NAMESPACE}/{#NAME}])-last(/Kubernetes cluster state by HTTP/kube.deployment.replicas_available[{#NAMESPACE}/{#NAME}]))<>0` |WARNING | |
|Kubernetes: Namespace [{#NAMESPACE}] Pod [{#NAME}]: Pod is not healthy |<p>-</p> |`min(/Kubernetes cluster state by HTTP/kube.pod.phase.failed[{#NAMESPACE}/{#NAME}],10m)>0 or min(/Kubernetes cluster state by HTTP/kube.pod.phase.pending[{#NAMESPACE}/{#NAME}],10m)>0 or min(/Kubernetes cluster state by HTTP/kube.pod.phase.unknown[{#NAMESPACE}/{#NAME}],10m)>0` |HIGH | |
|Kubernetes: Namespace [{#NAMESPACE}] Pod [{#NAME}]: Pod is crash looping |<p>-</p> |`(last(/Kubernetes cluster state by HTTP/kube.pod.containers_restarts[{#NAMESPACE}/{#NAME}])-min(/Kubernetes cluster state by HTTP/kube.pod.containers_restarts[{#NAMESPACE}/{#NAME}],#3))>2` |WARNING | |
|Kubernetes: Namespace [{#NAMESPACE}] RS [{#NAME}]: ReplicasSet mismatch |<p>-</p> |`(last(/Kubernetes cluster state by HTTP/kube.replicaset.replicas[{#NAMESPACE}/{#NAME}])-last(/Kubernetes cluster state by HTTP/kube.replicaset.ready[{#NAMESPACE}/{#NAME}]))<>0` |WARNING | |
|Kubernetes: Namespace [{#NAMESPACE}] StatefulSet [{#NAME}]: StatfulSet is down |<p>-</p> |`(last(/Kubernetes cluster state by HTTP/kube.statefulset.replicas_ready[{#NAMESPACE}/{#NAME}]) / last(/Kubernetes cluster state by HTTP/kube.statefulset.replicas_current[{#NAMESPACE}/{#NAME}]))<>1` |HIGH | |
|Kubernetes: Namespace [{#NAMESPACE}] RS [{#NAME}]: Statefulset replicas mismatch |<p>-</p> |`(last(/Kubernetes cluster state by HTTP/kube.statefulset.replicas[{#NAMESPACE}/{#NAME}])-last(/Kubernetes cluster state by HTTP/kube.statefulset.replicas_ready[{#NAMESPACE}/{#NAME}]))<>0` |WARNING | |
|Kubernetes: Component [{#NAME}] is unhealthy |<p>-</p> |`count(/Kubernetes cluster state by HTTP/kube.componentstatuses.healthy[{#NAME}],#3,,"True")<2 and length(last(/Kubernetes cluster state by HTTP/kube.componentstatuses.healthy[{#NAME}]))>0` |WARNING | |
|Kubernetes: Readyz [{#NAME}] is unhealthy |<p>-</p> |`count(/Kubernetes cluster state by HTTP/kube.readyz.healthcheck[{#NAME}],#3,,"ok")<2 and length(last(/Kubernetes cluster state by HTTP/kube.readyz.healthcheck[{#NAME}]))>0` |WARNING | |
|Kubernetes: Livez [{#NAME}] is unhealthy |<p>-</p> |`count(/Kubernetes cluster state by HTTP/kube.livez.healthcheck[{#NAME}],#3,,"ok")<2 and length(last(/Kubernetes cluster state by HTTP/kube.livez.healthcheck[{#NAME}]))>0` |WARNING | |

## Feedback

Please report any issues with the template at https://support.zabbix.com.

You can also provide feedback, discuss the template, or ask for help at [ZABBIX forums](https://www.zabbix.com/forum/zabbix-suggestions-and-feedback).

