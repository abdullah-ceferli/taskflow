# Workspace security and lifecycle

## Current workspace

- Web requests keep `current_workspace_id` in the authenticated session.
- API requests select a workspace with `X-Workspace-Id`.
- `ResolveCurrentWorkspace` rejects a requested workspace when the actor is not a member; global administrators may select any workspace.
- Project, Task, Activity, and Dashboard query paths are restricted to the resolved workspace.
- A user may belong to several workspaces with different `owner`, `manager`, or `member` roles.

## Invitations

- Invitations store only a SHA-256 token hash; the plaintext token is shown once to the inviter.
- Tokens expire after seven days, are bound to the invited email, and become invalid after acceptance.
- Only workspace owners and managers may issue invitations.

## Administration

- Global administrators can manage global roles through `/admin/users` and the matching `/api/v1/admin/users` endpoints.
- Self-demotion is blocked and every role change writes an audit event.
- Workspace role checks remain record-level authorization even when a user has a broader global role.

## Deletion and retention

- Workspace hard-delete is deliberately unavailable in the current UI/API.
- Workspaces use soft deletes; project/task/audit retention follows `DATA_LIFECYCLE_POLICY.md`.
- A future purge must verify legal retention, attachment cleanup, backup state, and cross-workspace isolation before physical deletion.
