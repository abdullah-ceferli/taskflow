<?php

namespace App\Enums;

enum TokenAbility: string
{
    case ProjectsRead = 'projects:read';
    case ProjectsWrite = 'projects:write';
    case TasksRead = 'tasks:read';
    case TasksWrite = 'tasks:write';
    case CommentsWrite = 'comments:write';
    case ActivityRead = 'activity:read';
    case DashboardRead = 'dashboard:read';
}
