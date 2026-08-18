# TaskFlow project brief

## Purpose

TaskFlow is an internal project and task-management application. It is intentionally more than a CRUD todo list: project membership, assignment, controlled task transitions, comments, private attachments, audit activity, dashboards, session authentication, and a Sanctum API are first-class features.

## Roles

| Role | Access |
| --- | --- |
| `admin` | All projects, tasks, activity, dashboard, and API capabilities. |
| `project_manager` | Managed projects, membership administration, task creation/assignment, permitted status changes, and scoped activity. |
| `member` | Joined projects, assigned tasks, permitted task transitions, comments, and owned attachment/comment deletion. |

Policies decide record-level access. Spatie permissions decide broad capabilities. Sanctum abilities limit an API token; they never replace a policy.

## Domain rules

- Projects use `draft`, `active`, `completed`, and `archived` statuses.
- Tasks use `todo`, `in_progress`, `review`, `done`, and `cancelled` statuses.
- Tasks can be created only in active projects.
- Assignees must be project members.
- Archived projects cannot be updated.
- Attachments remain on private storage and require task visibility to download.
- Task numbers follow `TSK-000001` format.

## Scope

The initial version intentionally permits direct module model dependencies where they make the first working implementation clearer. Larger cross-module decoupling is deferred until the documented implementation work is complete.