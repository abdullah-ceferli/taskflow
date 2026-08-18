# TaskFlow compliance checklist

## Status legend

- `missing`: target implementation does not exist.
- `in_progress`: implementation exists, but its full acceptance matrix is not yet verified.
- `verified`: implementation and the listed automated validation have passed.

Initial direct dependencies between business modules are accepted for the first implementation. Decoupling remains outside this document and is not roadmap work executed here.

## Verified baseline

| Requirement | Plan task | Status | Evidence |
| --- | --- | --- | --- |
| Nwidart modules and providers | 0.1, 1.1 | verified | Activity, Dashboard, Projects and Tasks are enabled and 68 named application routes resolve. |
| Portable schema, seed data and factories | 1.2, 1.3 | verified | Existing PHP 8.5.9 fresh migration/seed validation passed; module factories resolve under SQLite tests. |
| Projects actor visibility and archived update rule | 2.1, 2.4 | verified | `ProjectAuthorizationTest`: 2 passing tests. |
| Tasks actor visibility, active-project and assignee-membership invariants | 2.2, 2.4, 5.1 | verified | `TaskAuthorizationTest`, `OwnershipAndAttachmentCompensationTest` and scope tests cover visibility, membership and owner-delete rules. |
| Sanctum authentication baseline | 3.2 | verified | `ApiSecurityTest` verifies token issue, plaintext non-persistence, authenticated `/me`, 401 and ability 403. |
| Task API validation, create status and pagination cap | 6.1 | verified | `ApiSecurityTest` verifies 422, 201 and `per_page` cap. |
| Activity old/new payload safety | 8.1 | verified | `ActivityAuditTest` verifies status old/new values and excludes password/token keys. |
| Guest/app layout baseline and registration | 3.1, 7.1 | verified | Shared guest layout/components compile; `RegistrationTest`: 2 passing tests. |
| Main workspace render baseline | 7.1, 8.2 | verified | `WorkspacePagesTest` verifies dashboard, projects, tasks and activity return 200 for an administrator. |
| Test infrastructure | 9.1 | verified | SQLite in-memory configuration; 26 Pest tests discover and run. |
| Source formatting and frontend build | 9.4 | verified | PHP 8.5.9 Pint source check passes; `npm run build` passes. |

## Implemented but still requiring broader acceptance coverage

| Requirement | Plan task | Status | Remaining verification |
| --- | --- | --- | --- |
| Full permission/policy matrix (admin, manager, member) | 3.3 | in_progress | Add explicit comment/attachment and all role/action tests. |
| Projects Web/API member and archive matrix | 4.1–4.3 | in_progress | Add all endpoint status/policy tests. |
| Tasks comments and attachment lifecycle | 5.2–5.3, 6.2 | in_progress | Nested ownership, invalid MIME, upload/delete, private-path non-exposure, authorized download and delete compensation are covered. |
| Activity scoped filters and API coverage | 6.3, 8.1 | in_progress | Add actor/project/task filter authorization tests. |
| Dashboard API and role-specific metrics | 6.4, 8.2 | in_progress | Add metric and distribution assertions for all roles. |
| Approved Livewire components | 7.2–7.5 | in_progress | Filter, comment, status and quick-create interaction tests pass. |
| JavaScript progressive enhancements | 7.6 | in_progress | Manual keyboard and JS-disabled checklist remains. |
| Full endpoint status/format matrix and manual browser review | 9.2–9.4 | in_progress | Add remaining endpoint family tests and complete manual browser/mobile review. |

## Current validation evidence

- PHP runtime: `D:\programs\laragon\bin\php\php-8.5.9-nts-Win32-vs17-x64\php.exe`
- `artisan test`: **26 passed, 92 assertions**.
- `vendor/bin/pint --test app database Modules routes tests`: passed.
- `npm run build`: passed.
- API route inventory: **31** routes under `/api/v1`.
- `sample/roadmap.md` has not been read, changed, or executed.

## Completion gate

The implementation plan is not fully complete while items in the second table remain `in_progress`. Roadmap work is prohibited until all applicable items are verified, a final implementation report is provided, and the user gives explicit approval.