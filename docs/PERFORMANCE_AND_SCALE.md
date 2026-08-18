# Performance and scale baseline

## Measurement contract

`RecordRequestPerformance` records a bounded rolling sample for every named route. Every cache key includes route, workspace ID and actor ID, so cached metrics never become a cross-workspace read path. `QueryTelemetry` records query count, total query time and slow-query count without logging bindings. Queries at or above 250 ms are logged as `taskflow.slow_query`.

Run `php artisan taskflow:performance:report` after representative traffic. The report provides sample count, p50, p95, HTTP 5xx error rate and average query count. The first acceptance baseline is:

| Flow | p50 objective | p95 objective | Error objective | Query budget |
| --- | ---: | ---: | ---: | ---: |
| Task list filters | 250 ms | 750 ms | < 1% | 12 + eager loads |
| Dashboard summary, cold | 300 ms | 900 ms | < 1% | 12 |
| Dashboard summary, cached | 100 ms | 300 ms | < 1% | 4 |
| Activity list | 250 ms | 750 ms | < 1% | 8 + eager loads |
| Authorized attachment download | 150 ms | 500 ms | < 1% | 6 |

Production decisions require at least 30 representative samples per route/scope. A breached objective is evidence for investigation, not automatic permission to weaken authorization or consistency.

## Query and index audit

| Read path | Predicate/order | Existing or added index | Decision |
| --- | --- | --- | --- |
| Projects | workspace + status, newest | `(workspace_id, status)` plus primary/created ordering | Retain offset pagination while p95 stays inside objective. |
| Tasks | project/assignee + status + due date | `(project_id, assignee_id, status)`, `(assignee_id, status, due_at)`, `(project_id, milestone_id, status)` | Composite indexes match current filters and workload aggregates. |
| Activity | workspace/project/task + created date | MySQL generated scope columns with `(scope_id, created_at)` and `(event, created_at)` | Removes JSON scan on scoped activity feeds. Migration must be measured with `EXPLAIN` on staging before production rollout. |

Use `EXPLAIN ANALYZE` on staging with production-like cardinality. Record examined rows, chosen key and actual time before and after an index change. Indexes are not added only because a column appears in a filter.

## Pagination decision

Project, task and activity collections keep length-aware offset pagination because UI totals and page links are part of the current contract and the measured baseline is bounded. Introduce a separate cursor endpoint only when deep-page p95 breaches the objective or examined rows grow linearly beyond the accepted limit. Do not silently replace the existing API envelope.

## Dashboard read model and invalidation

Dashboard counts use conditional aggregate queries instead of one query per status. The complete summary is cached for 30 seconds with workspace ID, actor ID, role/permission hash and workspace version in the key. Project, task, project-membership, workspace-membership and activity writes increment the workspace version. A permission change changes the access hash even before the version expires.

## Queue and retention

- Task report generation, webhook delivery and user notifications are queued.
- Orphan attachment and expired export cleanup are unique queued jobs.
- Activity retention is disabled by default. When enabled, the job writes private JSONL archive chunks successfully before deleting the corresponding rows.
- Default retention is 365 days. Partitioning is evaluated only after table size and archive duration justify operational complexity.

## Stable load scenarios

1. Seed at least 50 projects, 5,000 tasks, 20,000 activity rows and representative memberships in staging.
2. Warm permissions, then run task filters, dashboard cold/warm, activity filters and authorized attachment downloads.
3. Use separate workspace/actor credentials; include 10% forbidden-resource attempts to measure authorization cost and error handling.
4. Capture p50/p95, 5xx rate, query count, slow queries and queue age for 10 minutes.
5. Repeat the same fixture and concurrency before/after a proposed index or cache change.

