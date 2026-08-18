# TaskFlow compliance checklist

## Status legend

- `missing`: target implementation does not exist.
- `in_progress`: implementation exists, but its full acceptance matrix is not yet verified.
- `verified`: implementation and the listed automated validation have passed.

Initial direct dependencies were accepted for the first implementation. Roadmap Phases 3–10 now record the verified coupling, workspace-isolation, collaboration, planning, integration, quality, measured-performance, and deployment-readiness work.

## Verified baseline

| Requirement | Plan task | Status | Evidence |
| --- | --- | --- | --- |
| Nwidart modules and providers | 0.1, 1.1 | verified | Activity, Dashboard, Projects and Tasks are enabled; 92 named application routes resolve. |
| Portable schema, seed data and factories | 1.2, 1.3 | verified | Existing PHP 8.5.9 fresh migration/seed validation passed; module factories resolve under SQLite tests. |
| Projects actor visibility and archived update rule | 2.1, 2.4 | verified | `ProjectAuthorizationTest`: 2 passing tests. |
| Tasks actor visibility, active-project and assignee-membership invariants | 2.2, 2.4, 5.1 | verified | `TaskAuthorizationTest`, `OwnershipAndAttachmentCompensationTest` and scope tests cover visibility, membership and owner-delete rules. |
| Sanctum authentication baseline | 3.2 | verified | `ApiSecurityTest` verifies token issue, plaintext non-persistence, authenticated `/me`, 401 and ability 403. |
| Task API validation, create status and pagination cap | 6.1 | verified | `ApiSecurityTest` verifies 422, 201 and `per_page` cap. |
| Public API resource contract | Roadmap Phase 2 | verified | `ApiContractTest` locks the projects collection envelope and project/task resource field sets. |
| Projects access contract | Roadmap Phase 3.1 | verified | Task services and policies use `ProjectAccessInterface`; fake-contract tests protect active/manageable decisions. |
| Dashboard metrics contracts | Roadmap Phase 3.2 | verified | Dashboard depends on Projects/Tasks metrics contracts; scope and page/API tests pass. |
| Domain activity events | Roadmap Phase 3.3 | verified | Four immutable cross-module events use an idempotent Activity listener; duplicate event test passes. |
| Module resilience | Roadmap Phase 3.4 | verified | `taskflow:modules:check` confirms four modules and seven public contract bindings. |
| Workspace model, membership, invitation, and current context | Roadmap Phase 4 | verified | `WorkspaceIsolationTest` covers Web/API isolation, header tampering, hashed/expiring/single-use invitations, and audited admin role changes. |
| Workspace query and export isolation | Roadmap Phase 4 | verified | Projects, Tasks, Activity, and Dashboard use current-workspace scoping; `PhaseFiveCollaborationTest` proves the administrator CSV export excludes another workspace. |
| Labels and saved task views | Roadmap Phase 5.1 | verified | Indexed workspace/project labels, actor-owned views, repository/Livewire filter parity, and foreign-workspace rejection are covered. |
| Mentions and queued notifications | Roadmap Phase 5.2 | verified | Assignment/mention preferences and channel-independent due-soon idempotency are covered; assignment, status, due, and mention notifications implement `ShouldQueue`. |
| Rich activity and audit export | Roadmap Phase 5.3 | verified | Canonical workspace-aware payloads remain sanitized; admin CSV export is permission- and workspace-scoped. |
| Attachment lifecycle | Roadmap Phase 5.4 | verified | Multiple private uploads, preview authorization, checksum/version/scan metadata, quota, download audit, and scheduled orphan cleanup are implemented; lifecycle tests pass. |
| Accessible Kanban board and optimistic status writes | Roadmap Phase 6.1 | verified | `PhaseSixPlanningTest` verifies scoped board data, stale-write HTTP conflict semantics, and conflict activity audit; keyboard forms and pointer drag/drop call the same status service. |
| Task dependency graph | Roadmap Phase 6.2 | verified | Indexed same-project edges, project-level concurrent locks, cycle rejection, and blocked-status rules are covered. |
| Recurring tasks | Roadmap Phase 6.3 | verified | Timezone-aware schedule calculation, queued scheduler dispatch, unique occurrence ledger, retry idempotency, and non-retroactive template snapshots are covered. |
| Milestones, risk, workload, and capacity | Roadmap Phase 6.4 | verified | Progress/risk aggregation, open-estimate workload, editable weekly capacity, dashboard/API output, and a four-query acceptance budget are covered. |
| Permission-aware global search | Roadmap Phase 7 | verified | Workspace-scoped project/task/comment search uses MySQL FULLTEXT with SQLite fallback; cross-workspace Web/API exclusion tests pass. |
| Personal token lifecycle | Roadmap Phase 7 | verified | Enumerated selectable abilities, least-privilege defaults, expiry, device/last-used UI, rotation, single/all revocation, and plaintext non-persistence are covered. |
| API task idempotency | Roadmap Phase 7 | verified | Actor/workspace key scoping, request fingerprint conflict, response replay, expiry, and one-task generation are covered. |
| Signed webhooks | Roadmap Phase 7 | verified | Encrypted one-time secret, HTTPS subscriptions, HMAC signature verification, bounded retry/backoff, delivery log, and replay tests pass. |
| Private CSV reporting and API lifecycle | Roadmap Phase 7 | verified | Queued actor/workspace-scoped exports, private signed expiring downloads, lifecycle headers, 90-day deprecation policy, and exact OpenAPI route parity are verified. |
| Risk-based quality matrix and architecture guards | Roadmap Phase 8 | verified | Dataset-driven role/ability/policy tests, API contract tests, controller/Livewire/module boundary guards, fake-based feature tests, and accessibility smoke tests pass. |
| Static and mutation analysis | Roadmap Phase 8 | verified | Larastan level 5 passes on critical policies/transitions without a baseline; the deterministic source mutation pilot kills 4/4 transition mutants (100%). Infection is configured for coverage-enabled CI. |
| Scoped performance telemetry and dashboard read model | Roadmap Phase 9 | verified | Rolling workspace/actor route telemetry calculates p50/p95/error/query metrics; conditional aggregates and versioned scope-safe dashboard caching pass cold/cached query budgets and write invalidation tests. |
| Queue, activity retention, and index scale plan | Roadmap Phase 9 | verified | Heavy cleanup is queued; private archive-before-delete retention passes; MySQL generated activity scope indexes and load/EXPLAIN/pagination decisions are documented. |
| Release gates and migration compatibility | Roadmap Phase 10 | verified | PR quality, scheduled security, versioned release/staging-smoke workflows and additive migration inspection are present; the local migration compatibility command passes. |
| Correlated observability and operational health | Roadmap Phase 10 | verified | Structured exception/queue/security events use correlation IDs; token-protected readiness and aggregate metrics plus an administrator operations dashboard pass six focused tests. |
| Rollback, restore and service supervision | Roadmap Phase 10 | verified | Versioned rollback, incident, secret rotation, RPO/RTO, worker and scheduler procedures are documented; the disposable restore drill restored 30 migrations and verified its sentinel record. |
| Complete authorization and endpoint guard matrix | 3.3, 4.1–6.4, 9.2–9.4 | verified | `EndpointAcceptanceMatrixTest` checks every protected API route for 401, every ability-protected route for 403, Projects Web/API policy and validation outcomes, Activity actor/project scope, and Dashboard output for admin/manager/member. Comment and attachment ownership tests also prove task visibility cannot be bypassed. |
| Approved Livewire and progressive UI acceptance | 7.2–7.6 | verified | All four approved components have positive, validation, filtering, and unauthorized interaction tests. The user reported successful desktop/mobile/JS-disabled manual verification on 2026-08-18. |
| Real MySQL migration acceptance | 1.2, 9.4 | verified | The user reported successful normal MySQL migration execution against the TaskFlow database on 2026-08-18; no destructive migration command was used by the agent. |
| Activity old/new payload safety | 8.1 | verified | `ActivityAuditTest` verifies status old/new values and excludes password/token keys. |
| Guest/app layout baseline and registration | 3.1, 7.1 | verified | Shared guest layout/components compile; `RegistrationTest`: 2 passing tests. |
| Main workspace render baseline | 7.1, 8.2 | verified | `WorkspacePagesTest` verifies dashboard, projects, tasks and activity return 200 for an administrator. |
| Test infrastructure | 9.1 | verified | SQLite in-memory configuration; 74 Pest tests discover and run with 312 assertions. |
| Source formatting and frontend build | 9.4 | verified | PHP 8.5.9 Pint source check passes; `npm run build` passes. |

## Remaining environment acceptance

| Requirement | Plan task | Status | Remaining verification |
| --- | --- | --- | --- |
| Staging/production operational acceptance | Roadmap Phase 10 | in_progress | Run the prepared staging smoke workflow, production-format encrypted backup restore drill, alert delivery test, and application rollback rehearsal in the real deployment environment. |

## Current validation evidence

- PHP runtime: `D:\programs\laragon\bin\php\php-8.5.9-nts-Win32-vs17-x64\php.exe`
- `artisan test`: **91 passed, 505 assertions**.
- `vendor/bin/phpstan analyse`: passed at the Phase 8 critical level-5 scope.
- `scripts/run-mutation-pilot.php`: **100%**, 4/4 critical mutants killed.
- `vendor/bin/pint --test`: passed.
- `npm run build`: passed.
- `taskflow:modules:check`: four enabled modules and seven contract bindings passed.
- Route inventory: **143** total, **136** named, and **57** under `/api/v1`.
- OpenAPI 1.2 documents **41** unique paths and **57** operations with exact route method/path parity and no duplicate path keys.
- Composer and npm dependency audits report no known vulnerabilities; container scanning is configured in CI because Docker is not installed in the local environment.
- `taskflow:migrations:check` passed and `taskflow:backup:restore-drill` restored a disposable SQLite backup with 30 migrations and a verified sentinel row.
- Roadmap execution was explicitly authorized by the user on 2026-08-18. Phases 2–10 are implemented; `sample/roadmap.md` remains unchanged.
- Phase 4–10 migrations are exercised by the isolated SQLite test database; the user additionally confirmed a successful normal migration against the real MySQL TaskFlow database. The agent did not execute a destructive migration command.

## Completion gate

The user explicitly authorized roadmap work on 2026-08-18 after receiving the implementation report and later confirmed the real MySQL migration plus manual browser/mobile/JS-disabled checks. Only real staging/production deployment, rollback, alert-delivery, and production-format restore evidence remain environment-owned until those environments and credentials are available.
