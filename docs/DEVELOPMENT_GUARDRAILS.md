# TaskFlow development guardrails

## Source documents and execution order

- `sample/implementation-plan.md` is the authoritative implementation plan.
- `sample/project-analysis.md` and `sample/TaskFlow.md` provide supporting analysis and product requirements.
- `sample/roadmap.md` is explicitly out of scope until every implementation-plan task is complete.
- After the implementation plan is complete, report the completed work and request explicit user permission before reading, changing, or executing any roadmap task.

## Required task workflow

For every small, logical task:

1. Explain the task, planned changes, rationale, and expected outcome before making changes.
2. Perform only that task's scoped work.
3. Report created and changed files, concrete work completed, commands run, verification result, and the next task.
4. Move to the next task only after this report.

## User decisions and approvals

Do not assume or choose on the user's behalf when a decision, credential, API key, additional file, manual action, or other user intervention is required. State the exact required input and pause the affected work until it is provided.

## Initial technical constraint

The root application starts as a clean Laravel project. Its first technical task is to install and configure `nwidart/laravel-modules` from scratch; sample-project configuration must be used only as reference, never copied blindly.

## Scope protection

- Keep work within the active task.
- Do not change `sample/roadmap.md`.
- Do not start roadmap work without explicit user permission.
- Follow the root `AGENTS.md` repository instructions, including safeguards for dependencies, environment files, database commands, Git, browser use, security, testing, and reporting.
