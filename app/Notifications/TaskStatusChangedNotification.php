<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Tasks\Models\Task;

final class TaskStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @param list<string> $channels */
    public function __construct(public readonly Task $task, public readonly string $from, public readonly string $to, private readonly array $channels = ['database']) {}

    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    public function toArray(object $notifiable): array
    {
        return ['event' => 'task.status_changed', 'task_id' => $this->task->id, 'task_number' => $this->task->number, 'old' => $this->from, 'new' => $this->to];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('Task status changed: '.$this->task->number)->line("Status changed from {$this->from} to {$this->to}.")->action('Open task', route('tasks.show', $this->task));
    }
}
