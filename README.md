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

Configure MySQL in `.env`, then use the PHP runtime selected in Laragon:

```powershell
D:\programs\laragon\bin\php\php-8.5.9-nts-Win32-vs17-x64\php.exe artisan migrate:fresh --seed
npm run build
D:\programs\laragon\bin\php\php-8.5.9-nts-Win32-vs17-x64\php.exe artisan serve
```

Local seeded accounts:

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@taskflow.test` | `1234` |
| Project manager | `manager@taskflow.test` | `password` |
| Member | `member@taskflow.test` | `password` |

## Quality checks

```powershell
D:\programs\laragon\bin\php\php-8.5.9-nts-Win32-vs17-x64\php.exe artisan test
D:\programs\laragon\bin\php\php-8.5.9-nts-Win32-vs17-x64\php.exe vendor\bin\pint --test app database Modules routes tests
npm run build
```

See `docs/` for the project brief, architecture, API conventions, development guardrails, learning guide, and implementation compliance evidence.