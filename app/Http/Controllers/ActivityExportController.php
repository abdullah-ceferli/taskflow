<?php

namespace App\Http\Controllers;

use App\Enums\PermissionName;
use Illuminate\Http\Request;
use Modules\Activity\Services\ActivityQueryService;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ActivityExportController extends Controller
{
    public function __invoke(Request $request, ActivityQueryService $activity): StreamedResponse
    {
        abort_unless($request->user()->hasPermissionTo(PermissionName::UserRolesManage->value), 403);
        $rows = $activity->exportForUser($request->user());

        return response()->streamDownload(function () use ($rows): void {
            $stream = fopen('php://output', 'wb');
            fputcsv($stream, ['id', 'event', 'actor_id', 'subject_type', 'subject_id', 'properties', 'created_at'], ',', '"', '');
            foreach ($rows as $row) {
                fputcsv($stream, [$row->id, $row->event, $row->causer_id, $row->subject_type, $row->subject_id, $row->properties->toJson(), $row->created_at->toISOString()], ',', '"', '');
            }
            fclose($stream);
        }, 'taskflow-audit-'.today()->toDateString().'.csv', ['Content-Type' => 'text/csv']);
    }
}
