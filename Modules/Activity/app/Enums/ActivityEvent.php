<?php

namespace Modules\Activity\Enums;

enum ActivityEvent: string
{
    case AuthRegistered = 'auth.registered';
    case AuthLogin = 'auth.login';
    case AuthLogout = 'auth.logout';
    case UserRoleChanged = 'user.role_changed';
    case ProjectCreated = 'project.created';
    case ProjectUpdated = 'project.updated';
    case ProjectArchived = 'project.archived';
    case ProjectActivated = 'project.activated';
    case ProjectMemberAdded = 'project.member_added';
    case ProjectMemberRemoved = 'project.member_removed';
    case TaskCreated = 'task.created';
    case TaskUpdated = 'task.updated';
    case TaskAssigned = 'task.assigned';
    case TaskStatusChanged = 'task.status_changed';
    case TaskBoardConflict = 'task.board_conflict';
    case TaskDependencyAdded = 'task.dependency_added';
    case TaskDependencyRemoved = 'task.dependency_removed';
    case RecurringTaskGenerated = 'task.recurring_generated';
    case MilestoneCreated = 'project.milestone_created';
    case TaskDeleted = 'task.deleted';
    case CommentCreated = 'comment.created';
    case CommentDeleted = 'comment.deleted';
    case AttachmentUploaded = 'attachment.uploaded';
    case AttachmentDeleted = 'attachment.deleted';
    case AttachmentDownloaded = 'attachment.downloaded';
}
