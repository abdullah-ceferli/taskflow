# Architecture conventions checklist

> Roadmap Phase 2 — naming, exception, and API contract baseline.

Use this checklist when changing an existing use case or adding a new one. It describes the conventions already used by TaskFlow; it does not require speculative classes.

## Repository conventions

- Each persistence concern has a contract in `Repositories/Contracts` and an Eloquent implementation in `Repositories/Eloquent`.
- Contract names use `<Aggregate>RepositoryInterface`.
- Implementations use `Eloquent<Aggregate>Repository`.
- Repositories own persistence, eager loading, filters, sorting, pagination, and visibility queries.
- Repositories do not make policy, role, domain-transition, activity-log, or HTTP-response decisions.
- Only the owning module injects its repository contracts.

## DTO conventions

- Input DTOs use purposeful names: `Create<Project|Task>Data`, `Update<Project|Task>Data`, `TaskFiltersData`.
- DTOs are `final readonly` and contain typed values, including immutable dates where relevant.
- Form Requests validate HTTP shape before a DTO is created.
- Filter DTOs normalise allowed values and pagination limits; they do not decide actor authorization.
- Do not create a DTO until a real use case needs one.

## Exception conventions

- `App\Exceptions\DomainRuleViolation` is the base exception for user-safe domain invariant failures.
- Module-specific exceptions extend it only when callers or tests need a distinct rule type; `InvalidTaskStatusTransition` is the current example.
- Validation remains in Form Requests; exceptions are for valid-shaped input rejected by a domain rule.
- New domain exceptions must have a consistent Web redirect/validation message and API error mapping before being introduced.

## API response conventions

| Result | Shape/status |
| --- | --- |
| Single record | API Resource with top-level `data`, `200` |
| New record | API Resource with top-level `data`, `201` |
| Collection | Resource collection; paginator metadata when paginated, `200` |
| Deletion | Empty response, `204` |
| Validation failure | Laravel field-keyed errors, `422` |
| Missing authentication | `401` |
| Ability or policy denial | `403` |

API controllers return resources, never raw Eloquent models. Resources exclude passwords, tokens, attachment storage paths, and sensitive activity properties.

## Change review

Before accepting a change, confirm:

- [ ] Controller/Livewire has no long query or transaction.
- [ ] Form Request handles shape validation.
- [ ] Policy handles record authorization.
- [ ] Service owns the business rule and transaction.
- [ ] Repository owns persistence/query details.
- [ ] DTO and API Resource are purposeful and typed.
- [ ] Web, API, and Livewire reuse the same service behavior.
- [ ] Focused tests protect the changed contract.