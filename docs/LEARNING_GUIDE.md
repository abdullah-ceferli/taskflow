# Learning guide

## One business rule, one service layer

A project task can be created from a Blade controller, an API controller, or the QuickTaskCreate Livewire component. Each entry point validates and authorizes the request, then calls the same service. This prevents UI-specific copies of business rules.

## Validation versus domain rules

A Form Request validates input shape: title length, integer IDs, dates, MIME types, and enum values. A service or policy enforces domain rules: archived projects reject tasks, an assignee must belong to the project, and only allowed status transitions are accepted.

## Authorization layers

- Spatie role/permission: broad capability
- Laravel policy: specific project, task, comment, or attachment
- Sanctum ability: token scope for API calls

All three layers complement one another; none should be treated as a substitute for the others.

## Safe attachments and audit logs

Attachment metadata is stored in the database while file content stays on private storage. Downloads pass through authorization. Activity records contain safe context such as old/new task status values, but never passwords, token values, or file paths.

## Testing

The Pest suite uses SQLite in memory. It covers actor visibility, project/task invariants, API statuses and abilities, activity safety, registration, Livewire workflows, attachment authorization and compensation, and dashboard scope.