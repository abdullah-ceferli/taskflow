# TaskFlow

TaskFlow is a Laravel modular-monolith workspace for managing projects, memberships, tasks, comments, attachments, activity history, dashboards, and REST API integrations.

## Stack

- Laravel 13 and PHP 8.5+
- Nwidart Laravel Modules
- Blade, Tailwind CSS, Vite, and limited Livewire
- Sanctum personal access tokens
- Spatie Permission and Activitylog
- Pest and Laravel Pint

## Modules

- `Projects`: projects, memberships, archive lifecycle
- `Tasks`: tasks, status changes, comments, attachments
- `Activity`: scoped audit history
- `Dashboard`: role-aware metrics and work queues

## Local setup

Configure MySQL in `.env`, select a supported PHP version in Laragon (PHP 8.4.1 or newer), and run these commands from the project directory:

```powershell
php artisan migrate --seed
npm run build
php artisan serve
```

Local seeded accounts:

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@taskflow.test` | `1234` |
| Project manager | `manager@taskflow.test` | `password` |
| Member | `member@taskflow.test` | `password` |

## Quality checks

```powershell
php artisan test
composer analyse
composer mutation:critical
php vendor/bin/pint --test
npm run build
```

See `docs/` for the project brief, architecture, API conventions, development guardrails, learning guide, and implementation compliance evidence.
