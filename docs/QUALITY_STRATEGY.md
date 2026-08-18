# Testing and quality strategy

## Risk-based pyramid

| Risk | Protection | Layer | Evidence |
| --- | --- | --- | --- |
| Workspace data leakage | Scope and API feature tests | Feature/integration | `WorkspaceIsolationTest`, `PhaseSevenIntegrationTest` |
| Role, token ability, policy mismatch | Dataset-driven permission matrix | Feature | `PhaseEightQualityTest` |
| Invalid task transitions or reopen rules | Exhaustive transition matrix and mutation pilot | Service/domain | `MutationGuardTest`, `infection.json5` |
| Duplicate queue/listener side effects | Unique occurrence/delivery records and replay tests | Integration | `PhaseSixPlanningTest`, `PhaseSevenIntegrationTest` |
| API breaking response changes | Resource envelope plus OpenAPI parity tests | Contract | `ApiContractTest`, `PhaseSevenIntegrationTest` |
| Storage, clock, notification and integration behavior | Laravel fakes and frozen-clock tests | Feature | Phase 5-7 feature suites |
| Layer and module erosion | Source-level architecture tests | Architecture | `LayerBoundaryTest` |
| Login/register accessibility regression | Stable landmark and label smoke tests | Browser-facing feature | `PhaseEightQualityTest` |

## Static analysis

Larastan starts at level 5 on enums, exceptions, all record policies and the critical task-transition service. The next expansion is repositories, then remaining services, controllers and resources after their model types are documented. Run `composer analyse`. A new path is added only while the current set remains clean; a baseline file is not used.

## Mutation pilot

The mandatory local pilot targets `TaskStatusService`, where a changed comparison or transition edge changes business behavior. It creates real temporary source mutants and starts the focused Pest behavior suite for each mutant without requiring a coverage extension:

```powershell
& "D:\programs\laragon\bin\php\php-8.5.9-nts-Win32-vs17-x64\php.exe" scripts/run-mutation-pilot.php
```

`infection.json5` is also ready for CI or a local runtime with Xdebug/PCOV. Run `composer mutation:infection` there. Infection's minimum MSI is 70 and minimum covered MSI is 80; the deterministic local pilot requires at least 80. Surviving mutants must result in a stronger behavior assertion or an explicitly documented equivalent mutant.

## Flaky-test policy

- Tests own their clock, queue, notification, storage and HTTP state through Laravel fakes.
- Random sleeps, real network calls and shared external databases are forbidden.
- An intermittent test is quarantined only with an issue reference and owner; it is not silently retried.
- The main quality command must pass once without retry before merge.
- Coverage percentage is informational; the risk table above is the acceptance authority.
