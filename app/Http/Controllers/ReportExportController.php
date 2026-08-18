<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportExportRequest;
use App\Models\ReportExport;
use App\Services\ReportExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReportExportController extends Controller
{
    public function index(Request $request, ReportExportService $service): View
    {
        return view('reports.exports', ['exports' => $service->forActor($request->user()), 'service' => $service]);
    }

    public function store(StoreReportExportRequest $request, ReportExportService $service): RedirectResponse
    {
        $service->create($request->user(), $request->string('type')->toString() ?: 'tasks');

        return back()->with('success', 'Report export queued.');
    }

    public function download(Request $request, ReportExport $export, ReportExportService $service): StreamedResponse
    {
        $service->authorize($export, $request->user());
        abort_unless($export->status === 'ready' && $export->path && Storage::disk($export->disk)->exists($export->path), 404);

        return Storage::disk($export->disk)->download($export->path, 'taskflow-tasks.csv', ['Content-Type' => 'text/csv']);
    }
}
