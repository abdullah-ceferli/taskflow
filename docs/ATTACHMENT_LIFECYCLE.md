# Attachment lifecycle and retention

> Roadmap Phase 2 — private attachment storage policy.

## Storage lifecycle

1. Uploads are stored on the private `local` disk under `task-attachments/{task-id}`.
2. The attachment metadata record is created in a database transaction.
3. If metadata creation or activity recording fails, the newly written file is deleted.
4. On deletion, the file is removed together with metadata. If the database transaction fails after file deletion, the service restores the original file content.
5. API resources never expose `disk` or `path`; every download passes through task visibility authorization.
6. Each upload records a checksum, metadata version, scan state, and workspace quota usage. Authorized previews never create a public URL.

## Orphan cleanup

`taskflow:attachments:prune-orphans` compares private storage files with `task_attachments` database records.

```powershell
# Safe report only (default seven-day retention)
php artisan taskflow:attachments:prune-orphans

# Report files older than 30 days
php artisan taskflow:attachments:prune-orphans --retention-days=30

# Permanently delete eligible orphan files
php artisan taskflow:attachments:prune-orphans --retention-days=30 --force
```

The command is dry-run by default. `--force` is required for deletion. Retention starts at seven days, so a transient DB/storage failure can be investigated before cleanup.

## Operational decision

The scheduler runs the cleanup daily at 03:00 with the command's seven-day retention. Production deployment must monitor scheduler failures and retain backups before enabling the worker.
