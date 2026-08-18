<?php

namespace Modules\Tasks\Services;

use App\Models\User;
use App\Models\WorkspaceMember;
use App\Notifications\MentionedInCommentNotification;
use App\Services\NotificationPreferenceService;
use Illuminate\Support\Facades\DB;
use Modules\Activity\Enums\ActivityEvent;
use Modules\Activity\Services\ActivityRecorder;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskComment;
use Modules\Tasks\Repositories\Contracts\TaskCommentRepositoryInterface;

class TaskCommentService
{
    public function __construct(private readonly TaskCommentRepositoryInterface $comments, private readonly ActivityRecorder $activity, private readonly NotificationPreferenceService $preferences) {}

    public function create(Task $task, User $actor, string $body): TaskComment
    {
        $comment = DB::transaction(function () use ($task, $actor, $body) {
            $comment = $this->comments->save(new TaskComment(['task_id' => $task->id, 'user_id' => $actor->id, 'body' => $body]));
            $this->activity->record(ActivityEvent::CommentCreated, $actor, $comment, ['project_id' => $task->project_id, 'task_id' => $task->id, 'comment_id' => $comment->id]);

            return $comment;
        });

        $this->notifyMentions($comment, $actor);

        return $comment;
    }

    public function delete(TaskComment $comment, User $actor): void
    {
        DB::transaction(function () use ($comment, $actor) {
            $taskId = $comment->task_id;
            $projectId = $comment->task->project_id;
            $commentId = $comment->id;
            $this->comments->delete($comment);
            $this->activity->record(ActivityEvent::CommentDeleted, $actor, $comment, ['project_id' => $projectId, 'task_id' => $taskId, 'comment_id' => $commentId]);
        });
    }

    private function notifyMentions(TaskComment $comment, User $actor): void
    {
        preg_match_all('/@([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,})/i', $comment->body, $matches);
        $emails = collect($matches[1] ?? [])->map(fn (string $email): string => strtolower($email))->unique();
        if ($emails->isEmpty()) {
            return;
        }

        $workspaceId = (int) $comment->task->project()->value('workspace_id');
        User::query()
            ->whereIn('email', $emails)
            ->whereKeyNot($actor->id)
            ->whereIn('id', WorkspaceMember::query()->where('workspace_id', $workspaceId)->select('user_id'))
            ->each(function (User $user) use ($comment, $workspaceId): void {
                $channels = $this->preferences->channels($user, $workspaceId, 'comment.mentioned');
                if ($channels !== []) {
                    $user->notify(new MentionedInCommentNotification($comment, $channels));
                }
            });
    }
}
