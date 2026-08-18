<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Tasks\Models\Task;

final class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @param list<string> $channels */
    public function __construct(public readonly Task $task, public readonly int $actorId, private readonly array $channels = ['database']) {}

    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    public function toArray(object $notifiable): array
    {
        return ['event' => 'task.assigned', 'task_id' => $this->task->id, 'task_number' => $this->task->number, 'title' => $this->task->title, 'actor_id' => $this->actorId];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('Task assigned: '.$this->task->number)->line($this->task->title)->action('Open task', route('tasks.show', $this->task));
    }
}
