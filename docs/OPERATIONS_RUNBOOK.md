# TaskFlow operations runbook

## Endpoints and signals

- `/health/live` is public and proves the PHP process can respond.
- `/health/ready` verifies database, cache, queue configuration/table, and private local storage. It requires `X-Operations-Token`.
- `/health/metrics` exposes aggregate request p95/error rate, queue age/failures, webhook failures, and storage usage. It requires the same token and contains no actor/workspace identifiers.
- `/admin/operations` is limited to administrators and is the queue/health dashboard.
- `taskflow:operations:check` runs every five minutes and emits a structured `taskflow.slo_breach` event with the configured owner and runbook.

The default owner is `platform-owner`. Set an accountable owner and alert destination in the deployment platform before production. Alert on readiness failure, request p95 above 1000 ms, 5xx rate above 2%, queue age above 300 seconds, failed jobs, failed webhooks, and storage growth.

## Queue failure

Inspect the failed job class, correlation ID, release, and adjacent application logs. Fix the cause before retrying. Use `queue:retry <uuid>` only for an idempotent job; otherwise follow its domain-specific compensation path. Restart workers after releases and monitor oldest queue age until it recovers.

## Backup, restore, RPO and RTO

Production target: RPO 24 hours and RTO 4 hours until business requirements define stricter values. Store encrypted daily full backups outside the application host, retain at least 30 days, restrict restore permissions, and rotate encryption credentials. `taskflow:backup:restore-drill` performs a safe disposable SQLite integrity drill in CI; it does not validate the production backup.

Quarterly, restore the latest production-format backup into an isolated environment, verify schema/migration history, user/project/task counts, attachment references, permissions, and login with a drill account. Measure restore duration, record achieved RPO/RTO, then destroy the isolated copy. A release is not operationally complete until the first production-format drill has evidence.

## Secret rotation

Keep application key, database, mail, operations token, webhook and external integration credentials in the deployment secret manager. Rotate one credential at a time with an overlap window where supported, verify readiness and delivery, revoke the old credential, and attach evidence to the change record. Never log secret values or place them in release artifacts.
