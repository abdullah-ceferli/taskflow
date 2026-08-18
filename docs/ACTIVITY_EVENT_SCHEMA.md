# Activity event schema

> Roadmap Phase 2 — versioned canonical audit payloads.

## Canonical event names

`Modules\Activity\Enums\ActivityEvent` is the only source of truth for business activity event names.

```text
auth.registered, auth.login, auth.logout
project.created, project.updated, project.archived, project.activated
project.member_added, project.member_removed
task.created, task.updated, task.assigned, task.status_changed, task.deleted
comment.created, comment.deleted
attachment.uploaded, attachment.deleted
```

All business activity is recorded through `ActivityRecorder`.

## Payload version 1

Every payload contains:

```json
{ "schema_version": 1 }
```

Context keys are added only when relevant: `project_id`, `task_id`, `attachment_id`, `comment_id`, `changed`, `old`, `new`, and safe display values such as task number or filename.

## Sensitive-data filtering

`ActivityRecorder` recursively removes these property keys before persistence:

```text
password, password_confirmation, token, plain_text_token,
secret, api_key, authorization, path, disk
```

New event payloads must never add credentials, plaintext personal access tokens, storage locations, or authorization headers. A payload schema change requires a version increase, an API resource review, and a focused test.

## Compatibility

Existing Activity API resources keep the canonical event string and expose safe properties only. Consumers should tolerate additional safe keys but rely on `schema_version` before interpreting new payload shapes.