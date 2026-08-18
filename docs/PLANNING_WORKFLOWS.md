# Planning and workflow rules

- The Kanban board is a read model over visible project tasks. Pointer drag/drop and keyboard-accessible forms both call `TaskStatusService`.
- `expected_updated_at` provides optimistic concurrency. A stale write returns HTTP 409 and records `task.board_conflict`.
- A dependency edge means `task_id` is blocked by `depends_on_task_id`. Same-project checks and graph traversal reject cycles.
- A task with unfinished dependencies cannot enter `in_progress`, `review`, or `done`; cancellation remains available.
- Recurring definitions store timezone and the next UTC run. Each scheduled occurrence has a unique ledger row, so queue retries cannot create duplicates.
- Generated tasks snapshot the template. Later definition edits affect only future occurrences.
- Milestone progress is completed tasks divided by linked tasks. Past-due, incomplete milestones are at risk.
- Workload sums open-task estimates against each workspace member's weekly capacity with constant-count aggregate queries.
