<?php

namespace App\Enums;

enum WebhookEvent: string
{
    case TaskCreated = 'task.created';
    case TaskAssigned = 'task.assigned';
    case TaskStatusChanged = 'task.status_changed';
    case ProjectMemberAdded = 'project.member_added';
}
