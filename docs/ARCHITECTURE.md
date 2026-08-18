# Architecture

TaskFlow is a Laravel modular monolith.

```text
Route
  -> Controller or approved Livewire component
  -> Form Request + Policy
  -> DTO
  -> Service
  -> Repository contract
  -> Eloquent model
  -> Blade view or API Resource
```

## Module boundaries

```text
Modules/
├── Projects/   # projects and project members
├── Tasks/      # tasks, comments, attachments, task UI components
├── Activity/   # scoped activity query/display
└── Dashboard/  # role-aware workspace metrics
```

The host application owns authentication, the `User` model, global enums, personal access tokens, and session registration/login.

## Responsibilities

- **Controllers and Livewire:** HTTP/UI entry points; validate, authorize, construct DTOs, and call services.
- **Services:** transactions, domain invariants, activity recording, and use-case orchestration.
- **Repositories:** persistence, eager loading, filters, sorting, pagination, and actor visibility queries.
- **Policies:** record-level authorization.
- **API Resources:** safe JSON presentation; storage paths and secrets are not exposed.

Web, API, and approved Livewire components share service-layer business logic. Nwidart providers load module routes, views, and migrations. API endpoints are registered below `/api/v1` and protected by Sanctum.