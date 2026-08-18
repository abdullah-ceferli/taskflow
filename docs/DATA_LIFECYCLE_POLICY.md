# Data lifecycle policy

> Roadmap Phase 2 — date/timezone, soft-delete, purge, and audit retention decisions.

## Time and dates

- Timestamps (`created_at`, `updated_at`, activity time, token time) are stored and exchanged as UTC ISO-8601 values.
- `projects.starts_at`, `projects.due_at`, and `tasks.due_at` are date-only business-calendar values; they do not carry a time-of-day.
- A task is overdue when its date-only `due_at` is earlier than the current application date and it is not `done` or `cancelled`.
- Until the workspace feature exists, the application timezone remains the Laravel default. Per-user or per-workspace timezone conversion is explicitly deferred; no client may reinterpret stored UTC timestamps as a different persisted value.

## Soft deletes and restore

| Record | Current behavior | Restore policy |
| --- | --- | --- |
| Project | Soft deleted; normal queries exclude it. | No self-service restore UI/API yet. Restore requires a supervised maintenance procedure and policy review. |
| Task | Soft deleted; normal queries exclude it. | No self-service restore UI/API yet. Restore requires a supervised maintenance procedure and policy review. |
| Task comment | Soft deleted. | Restore is not exposed; preserve audit evidence. |
| Task attachment metadata | Hard deleted only after file lifecycle handling. | Restore is unavailable; backups are the recovery mechanism. |

No automatic purge job is enabled. A purge policy must define legal, backup, and workspace-retention requirements before destructive scheduling is introduced.

## Audit retention

- Activity records are retained indefinitely in the current local implementation.
- Sensitive values are filtered before activity persistence; storage paths, credentials, and plaintext tokens are never retained.
- Before production, choose a retention window, archive destination, restore process, and deletion authorization owner.
- Export, legal hold, or privacy deletion features require a dedicated later roadmap task and tests.

## Operational checklist

Before adding restore, purge, or scheduled cleanup:

- [ ] Define the retention owner and retention period.
- [ ] Confirm backup/restore coverage.
- [ ] Add authorization and audit events.
- [ ] Add dry-run mode and failure alerting.
- [ ] Add Web/API tests for cross-actor data isolation.