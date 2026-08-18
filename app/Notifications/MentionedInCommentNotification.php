<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Tasks\Models\TaskComment;

final class MentionedInCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @param list<string> $channels */
    public function __construct(public readonly TaskComment $comment, private readonly array $channels = ['database']) {}

    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    public function toArray(object $notifiable): array
    {
        return ['event' => 'comment.mentioned', 'comment_id' => $this->comment->id, 'task_id' => $this->comment->task_id, 'mentioned_by' => $this->comment->user_id];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('You were mentioned in TaskFlow')->line('A teammate mentioned you in a task comment.')->action('Open task', route('tasks.show', $this->comment->task_id));
    }
}
