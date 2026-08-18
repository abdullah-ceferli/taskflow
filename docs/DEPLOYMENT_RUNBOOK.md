# TaskFlow deployment and rollback runbook

## Release gate

Every release must have a version tag and a passing `Quality gates` workflow. The release owner confirms the dependency and container scans, additive migration check, tests, static analysis, mutation pilot, formatting, frontend build, and disposable restore drill. Never deploy an unversioned working tree.

## Zero-downtime sequence

1. Create a new immutable release directory and unpack the versioned artifact there.
2. Inject secrets from the deployment platform; never copy an environment file from another release.
3. Run `php artisan taskflow:migrations:check`, then take and verify an encrypted database backup.
4. Run normal forward migrations with `php artisan migrate --force`. Never use fresh, reset, refresh, or wipe in a deployed environment.
5. Warm config, events, routes, and views; atomically switch the `current` symlink.
6. Restart workers with `php artisan queue:restart`; keep the scheduler supervised.
7. Check `/health/live`, authenticated `/health/ready`, login, registration, dashboard, project, task, queue, notification, and webhook smoke flows.
8. Record release version, operator, migration batch, smoke result, and backup identifier.

## Rollback

For application-only failures, atomically point `current` to the previous version, restart workers, and repeat smoke checks. Do not automatically reverse migrations. Database rollback requires an approved compatibility decision; prefer a forward fix for additive migrations. If data corruption occurred, isolate writes, invoke the restore procedure from `OPERATIONS_RUNBOOK.md`, and record the incident timeline.

Feature flags in `config/taskflow.php` default off and may be environment-backed. Disable a risky feature before application rollback when that safely contains the incident.
