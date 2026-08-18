# API conventions

## Authentication and abilities

All API routes use `/api/v1` and Laravel Sanctum personal access tokens. Token issue and current-user endpoints are:

```text
POST   /api/v1/auth/token
GET    /api/v1/me
DELETE /api/v1/auth/token
```

Available token abilities:

```text
projects:read, projects:write, tasks:read, tasks:write,
comments:write, activity:read, dashboard:read
```

A request without authentication returns `401`; a missing ability or denied policy returns `403`; invalid input returns `422`.

## Endpoint groups

```text
/projects                         Project list and CRUD
/projects/{project}/members       Project membership
/tasks                            Task list and CRUD
/tasks/{task}/status              Task status transition
/tasks/{task}/assignee            Task assignment
/tasks/{task}/comments            Comment list/create/delete
/tasks/{task}/attachments         Attachment list/upload/delete
/tasks/{task}/attachments/{attachment}/download
/activity                         Scoped activity
/dashboard/summary|my-tasks|overdue
```

## Responses

Controllers return API Resources, never raw Eloquent models. Single resources use `data`; paginated resources add pagination metadata. Create responses use `201`; successful deletion uses `204`.

Task filters accept `search`, `status`, `priority`, `project_id`, `assignee_id`, `due_before`, `sort`, `page`, and `per_page`. `per_page` is capped at `100`; sorting is whitelisted.

## Security

Attachment uploads enforce MIME type and a 10 MB maximum. Attachment download requires policy authorization. Nested comment and attachment routes verify that the child belongs to the requested task. Responses never expose attachment storage paths, passwords, plaintext tokens, or secret activity properties.