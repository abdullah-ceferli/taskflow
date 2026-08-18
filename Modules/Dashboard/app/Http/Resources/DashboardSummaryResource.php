<?php

namespace Modules\Dashboard\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Activity\Http\Resources\ActivityResource;
use Modules\Tasks\Http\Resources\TaskResource;

final class DashboardSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['active_projects' => $this['activeProjects'], 'archived_projects' => $this['archivedProjects'], 'total_tasks' => $this['totalTasks'], 'todo' => $this['todo'], 'in_progress' => $this['inProgress'], 'review' => $this['review'], 'overdue' => $this['overdue'], 'completed_today' => $this['completedToday'], 'my_tasks' => TaskResource::collection($this['myTasks']), 'workload' => $this['workload']->map(fn ($row) => ['user_id' => $row->user->id, 'name' => $row->user->name, 'capacity_hours' => $row->capacity_hours, 'allocated_hours' => $row->allocated_hours, 'open_tasks' => $row->open_tasks, 'utilization' => $row->utilization])->values(), 'recent_activity' => ActivityResource::collection($this['recentActivity']), 'project_distribution' => $this['projectDistribution']->map(fn ($project) => ['project_id' => $project->id, 'name' => $project->name, 'tasks' => $project->tasks_count])->values()];
    }
}
