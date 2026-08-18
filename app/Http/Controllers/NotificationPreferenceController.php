<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateNotificationPreferencesRequest;
use App\Services\CurrentWorkspace;
use App\Services\NotificationPreferenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class NotificationPreferenceController extends Controller
{
    private const EVENTS = ['task.assigned', 'task.status_changed', 'task.due_soon', 'comment.mentioned'];

    public function index(Request $request, CurrentWorkspace $current, NotificationPreferenceService $preferences): View
    {
        $workspaceId = $current->idFor($request->user());

        return view('notifications.preferences', ['events' => self::EVENTS, 'preferences' => $preferences->forUser($request->user(), $workspaceId)]);
    }

    public function update(UpdateNotificationPreferencesRequest $request, CurrentWorkspace $current, NotificationPreferenceService $preferences): RedirectResponse
    {
        $preferences->update(
            $request->user(),
            (int) $current->idFor($request->user()),
            $request->string('event')->toString(),
            $request->boolean('in_app'),
            $request->boolean('email'),
        );

        return back()->with('success', 'Notification preference updated.');
    }
}
