<?php

namespace App\Services;

use App\Contracts\GlobalSearchInterface;
use App\Enums\PermissionName;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Projects\Models\Project;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskComment;

final class DatabaseGlobalSearch implements GlobalSearchInterface
{
    public function search(User $actor, string $query, int $limitPerType = 10): Collection
    {
        $query = trim($query);
        $needle = '%'.addcslashes($query, '%_\\').'%';
        $results = collect();

        if ($actor->hasPermissionTo(PermissionName::ProjectsView->value)) {
            $results->push(...Project::query()->visibleTo($actor)
                ->where(fn (Builder $projects) => $this->textSearch($projects, ['name', 'description'], $query, $needle))
                ->limit($limitPerType)->get()->map(fn (Project $project) => [
                    'type' => 'project', 'id' => $project->id, 'title' => $project->name,
                    'excerpt' => Str::limit(strip_tags((string) $project->description), 160), 'url' => route('projects.show', $project),
                ])->all());
        }

        if ($actor->hasPermissionTo(PermissionName::TasksView->value)) {
            $results->push(...Task::query()->visibleTo($actor)
                ->where(fn (Builder $tasks) => $this->textSearch($tasks, ['number', 'title', 'description'], $query, $needle))
                ->limit($limitPerType)->get()->map(fn (Task $task) => [
                    'type' => 'task', 'id' => $task->id, 'title' => $task->number.' · '.$task->title,
                    'excerpt' => Str::limit(strip_tags((string) $task->description), 160), 'url' => route('tasks.show', $task),
                ])->all());

            $results->push(...TaskComment::query()
                ->whereHas('task', fn (Builder $tasks) => $tasks->visibleTo($actor))
                ->where(fn (Builder $comments) => $this->textSearch($comments, ['body'], $query, $needle))->with('task')->limit($limitPerType)->get()
                ->map(fn (TaskComment $comment) => [
                    'type' => 'comment', 'id' => $comment->id, 'title' => 'Comment on '.$comment->task->number,
                    'excerpt' => Str::limit(strip_tags($comment->body), 160), 'url' => route('tasks.show', $comment->task_id).'#comments',
                ])->all());
        }

        return $results->values();
    }

    private function textSearch(Builder $builder, array $columns, string $query, string $needle): Builder
    {
        if (DB::getDriverName() === 'mysql') {
            return $builder->whereFullText($columns, $query);
        }

        return $builder->where(function (Builder $fallback) use ($columns, $needle): void {
            foreach ($columns as $index => $column) {
                $index === 0 ? $fallback->where($column, 'like', $needle) : $fallback->orWhere($column, 'like', $needle);
            }
        });
    }
}
