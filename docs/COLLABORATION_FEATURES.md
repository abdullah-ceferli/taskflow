# Phase 5 collaboration features

## Labels and saved views

- Labels belong to one workspace and may optionally be limited to one project.
- Task-label synchronization rejects labels from another workspace or project.
- Saved views belong to one user in one workspace and persist only whitelisted filter keys.
- API and Livewire filters use the same `TaskFiltersData` and repository query.

## Mentions and notifications

- Mentions use the explicit `@email@example.com` form and only resolve current-workspace members.
- Blade output remains escaped, so mention text cannot inject HTML.
- Assignment, status, due-soon, and mention notifications implement `ShouldQueue`.
- Per-workspace preferences independently enable database and email channels.
- Due-soon delivery is scheduled daily and uses a channel-independent delivery ledger, so database-only, mail-only, and mixed preferences cannot enqueue the same user/task/day twice.

## Activity and privacy

- Admin audit export is authorization-protected and workspace-scoped.
- Canonical payload sanitization removes token, secret, disk, and storage-path keys.
- Raw IP addresses and complete device strings are not collected; adding them requires a privacy review and retention decision.

## Attachments

- Up to ten files may be submitted in one request.
- Storage is private; previews and downloads pass through task authorization.
- Metadata includes schema version, SHA-256 checksum, scanner status, download count, and last-download time.
- `MalwareScannerInterface` is an adapter boundary. The default local adapter reports `not_scanned`; production must bind a real scanner before claiming scanned uploads.
- Workspace quota defaults to 100 MiB and is enforced before storage.
