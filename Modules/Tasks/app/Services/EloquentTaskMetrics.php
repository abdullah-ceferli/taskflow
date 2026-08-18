<?php

namespace Modules\Tasks\Services;

use App\Models\User;
use App\Models\WorkspaceMember;
use App\Services\CurrentWorkspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Tasks\Contracts\TaskMetricsInterface;
use Modules\Tasks\Data\TaskMetricsData;
use Modules\Tasks\Enums\TaskStatus;
use Modules\Tasks\Models\Task;

final class EloquentTaskMetrics implements TaskMetricsInterface
{
    public function forUser(User $user): TaskMetricsData
    {
        $counts = $this->countsFor($user);

        return new TaskMetricsData(
            total: $counts['total'],
            todo: $counts['todo'],
            inProgress: $counts['in_progress'],
            review: $counts['review'],
            overdue: $counts['overdue'],
            completedToday: $counts['completed_today'],
            myTasks: $this->myTasks($user),
            workload: $this->workloadFor($user),
        );
    }

    public function countsFor(User $user): array
    {
        $tasks = Task::query()->visibleTo($user);
        $aggregate = (clone $tasks)->toBase()->selectRaw(
            'COUNT(*) as total,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as todo,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as review,
            SUM(CASE WHEN due_at IS NOT NULL AND due_at < ? AND status NOT IN (?, ?) THEN 1 ELSE 0 END) as overdue,
            SUM(CASE WHEN status = ? AND completed_at >= ? AND completed_at < ? THEN 1 ELSE 0 END) as completed_today',
            [
                TaskStatus::Todo->value,
                TaskStatus::InProgress->value,
                TaskStatus::Review->value,
                now(),
                TaskStatus::Done->value,
                TaskStatus::Cancelled->value,
                TaskStatus::Done->value,
                today()->startOfDay(),
                today()->addDay()->startOfDay(),
            ],
        )->first();

        return [
            'total' => (int) ($aggregate->total ?? 0),
            'todo' => (int) ($aggregate->todo ?? 0),
            'in_progress' => (int) ($aggregate->in_progress ?? 0),
            'review' => (int) ($aggregate->review ?? 0),
            'overdue' => (int) ($aggregate->overdue ?? 0),
            'completed_today' => (int) ($aggregate->completed_today ?? 0),
        ];
    }

    public function myTasks(User $user): Collection
    {
        return Task::query()
            ->visibleTo($user)
            ->with(['project', 'assignee'])
            ->where('assignee_id', $user->id)
            ->orderByRaw(
                'CASE WHEN due_at IS NOT NULL AND due_at < ? AND status NOT IN (?, ?) THEN 0 ELSE 1 END',
                [now(), TaskStatus::Done->value, TaskStatus::Cancelled->value],
            )
            ->orderByRaw('due_at IS NULL')
            ->orderBy('due_at')
            ->latest()
            ->take(6)
            ->get();
    }

    public function overdueTasks(User $user): Collection
    {
        return $this->overdueQuery(Task::query()->visibleTo($user))
            ->with(['project', 'assignee'])
            ->orderBy('due_at')
            ->get();
    }

    public function workloadFor(User $user): Collection
    {
        $workspaceId = app(CurrentWorkspace::class)->idFor($user);
        if (! $workspaceId) {
            return collect();
        }

        $hours = Task::query()
            ->selectRaw('assignee_id, COUNT(*) as open_tasks, COALESCE(SUM(estimate_hours), 0) as allocated_hours')
            ->whereHas('project', fn (Builder $projects) => $projects->where('workspace_id', $workspaceId))
            ->whereNotNull('assignee_id')
            ->whereNotIn('status', [TaskStatus::Done->value, TaskStatus::Cancelled->value])
            ->groupBy('assignee_id')
            ->get()
            ->keyBy('assignee_id');

        $members = WorkspaceMember::query()->with('user')->where('workspace_id', $workspaceId)
            ->when(! app(CurrentWorkspace::class)->canManage($user), fn ($query) => $query->where('user_id', $user->id))
            ->orderBy('user_id')->get();

        return $members->map(function (WorkspaceMember $membership) use ($hours): object {
            $assigned = $hours->get($membership->user_id);
            $capacity = (float) $membership->weekly_capacity_hours;
            $allocated = (float) ($assigned?->allocated_hours ?? 0);

            return (object) ['user' => $membership->user, 'membership_id' => $membership->id, 'capacity_hours' => $capacity, 'allocated_hours' => $allocated, 'open_tasks' => (int) ($assigned?->open_tasks ?? 0), 'utilization' => $capacity > 0 ? (int) round(($allocated / $capacity) * 100) : 0];
        });
    }

    private function overdueQuery(Builder $tasks): Builder
    {
        return $tasks
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->whereNotIn('status', [TaskStatus::Done->value, TaskStatus::Cancelled->value]);
    }
}
