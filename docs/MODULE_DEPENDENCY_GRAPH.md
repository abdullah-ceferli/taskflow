# Module dependency graph

> Roadmap Phase 2 — architecture stabilization baseline.
>
> This document records the current, intentionally accepted dependencies before any loose-coupling refactor. It is a snapshot, not a request to introduce abstractions prematurely.

## Runtime graph

```text
Host application
  ├── Projects
  ├── Tasks
  ├── Activity
  └── Dashboard

Projects ──models──> Tasks
Projects ──activity recording/query──> Activity
Tasks ──project membership/models──> Projects
Tasks ──activity recording/query──> Activity
Activity ──scoped query models──> Projects, Tasks
Dashboard ──metrics/resources──> Projects, Tasks, Activity
```

## Dependency inventory

| Source module | Depends on | Current reason | Boundary status |
| --- | --- | --- | --- |
| Projects | Tasks | `Project::tasks()` relation and project detail task display. | Accepted direct read dependency. |
| Projects | Activity | Project/member services record events; project pages display history. | Accepted initial side effect/read dependency. |
| Tasks | Projects | Task creation, assignment, visibility, policies, and Livewire project selection require project/member data. | Highest-priority Phase 3 refactor candidate. |
| Tasks | Activity | Task, comment, attachment, assignment, and status services record audit events. | Candidate for event/listener migration. |
| Activity | Projects, Tasks | Activity filters and scoped routes resolve project/task context. | Accepted read dependency. |
| Dashboard | Projects, Tasks, Activity | Role-aware metrics, distributions, queues, and recent activity. | Candidate for metrics contracts only after measured need. |

## Public services currently used across module boundaries

| Service | Consumers | Responsibility |
| --- | --- | --- |
| `ProjectMemberService` | Tasks | Membership checks and manager visibility. |
| `ActivityRecorder` | Projects, Tasks | Canonical activity event recording. |
| `ActivityQueryService` | Projects, Tasks, Dashboard | Scoped activity retrieval. |
| `DashboardService` | Dashboard controllers only | Role-aware dashboard metrics. |

## Phase 3 refactor order

1. Inventory every `Tasks -> Projects` access point and introduce only the minimum project-access contract required by Tasks.
2. Replace direct cross-module activity recording with after-commit domain events and idempotent listeners.
3. Measure Dashboard query pressure before extracting metrics contracts.
4. Add module enable/disable health checks only after contracts have real implementations.

## Guardrails

- Do not remove a direct dependency merely to satisfy a diagram; preserve behavior with tests first.
- Keep Eloquent relations inside their owning module unless a concrete read contract replaces them.
- Controller, API resource, or Livewire code must not create new cross-module business rules.
- Every future dependency change requires a before/after graph and focused parity tests.