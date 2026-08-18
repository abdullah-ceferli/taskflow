# Module service boundaries

> Roadmap Phase 2 — public service and internal implementation inventory.

A class is **public** only when another module currently uses it or when it is the approved entry point for a documented module use case. Everything else is **internal** by default. This inventory documents the present state; it does not add new interfaces yet.

## Projects

| Class | Status | Consumers | Rule |
| --- | --- | --- | --- |
| `ProjectMemberService` | Temporary public | Tasks services, Tasks policy/controllers | The only approved cross-module membership and manager-access entry point. |
| `ProjectService` | Internal | Projects Web/API controllers | Project lifecycle changes remain owned by Projects. |
| `ProjectRepositoryInterface` | Internal | Projects module only | Other modules must not query Projects persistence directly through its repository. |

## Tasks

| Class | Status | Consumers | Rule |
| --- | --- | --- | --- |
| `TaskService`, `TaskAssignmentService`, `TaskStatusService` | Internal | Tasks Web/API/Livewire | Task mutations remain within Tasks. |
| `TaskQueryService`, `TaskCommentQueryService` | Internal | Tasks Livewire | Read adapters for approved Tasks UI components. |
| Task repositories | Internal | Tasks module only | No other module may inject a Tasks repository. |

## Activity

| Class | Status | Consumers | Rule |
| --- | --- | --- | --- |
| `ActivityRecorder` | Temporary public | Projects and Tasks services | Canonical event recording entry point until after-commit domain listeners replace direct calls. |
| `ActivityQueryService` | Temporary public | Projects, Tasks, Dashboard | Scoped activity read entry point. Consumers must never query the Spatie model directly. |
| `ActivityPolicy` and API resources | Internal | Activity module only | Activity authorization and presentation stay in Activity. |

## Dashboard

| Class | Status | Consumers | Rule |
| --- | --- | --- | --- |
| `DashboardService` | Internal | Dashboard Web/API controllers | Metrics are presented only through Dashboard. |
| `DashboardSummaryResource` | Internal | Dashboard API controller | Other modules do not construct dashboard payloads. |

## Boundary rules for future changes

1. Controllers and Livewire components may call only their own module services, except the three temporary public services above.
2. No module may inject another module's repository contract or Eloquent repository implementation.
3. New cross-module writes should use the owning module service; do not create models directly from another module.
4. Phase 3 must replace `Tasks -> Projects` direct model access before treating this inventory as a stable public API.
5. Any public service signature change requires focused Web/API/Livewire parity tests.