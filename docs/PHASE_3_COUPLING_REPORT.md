# Phase 3 coupling report

## Before

- Task business services imported the Projects Eloquent model and `ProjectMemberService`.
- Task policies accepted a Projects model as their create-policy input.
- Dashboard built Project and Task Eloquent queries directly.
- Selected activity side effects were written synchronously inside business transactions.

## After

- Task business services and policies depend on `ProjectAccessInterface` and typed `ProjectAccessData`.
- `EloquentProjectAccess` is owned and bound by the Projects module.
- Dashboard depends on `ProjectMetricsInterface` and `TaskMetricsInterface`; query ownership stays in the source modules.
- `TaskCreated`, `TaskAssigned`, `TaskStatusChanged`, and `ProjectMemberAdded` are immutable events handled by Activity.
- Activity listener writes occur after the owning service transaction and use an event ID for idempotency.
- `taskflow:modules:check` provides a fail-fast check for required modules and public bindings.

## Trade-offs

- Eloquent relations still reference cross-module models because all modules share one database; only business orchestration was inverted.
- HTTP controllers may use route-bound models for presentation and Laravel policy dispatch, but business rules are resolved through contracts.
- Direct activity calls remain for local operations not selected as cross-module events; new event classes are added only when a real side effect benefits from decoupling.
