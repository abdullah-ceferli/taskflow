<?php

namespace App\Services;

use App\Enums\PermissionName;
use App\Exceptions\DomainRuleViolation;
use App\Jobs\GenerateTaskReportExport;
use App\Models\ReportExport;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\URL;

final class ReportExportService
{
    public function __construct(private readonly CurrentWorkspace $current) {}

    public function create(User $actor, string $type = 'tasks'): ReportExport
    {
        if (! $actor->hasPermissionTo(PermissionName::ReportsExport->value)) {
            throw new DomainRuleViolation('The actor cannot export reports.');
        }
        $export = ReportExport::query()->create(['workspace_id' => $this->current->idFor($actor), 'user_id' => $actor->id, 'type' => $type, 'status' => 'pending', 'disk' => 'local', 'expires_at' => now()->addHour()]);
        GenerateTaskReportExport::dispatch($export->id);

        return $export;
    }

    /** @return Collection<int, ReportExport> */
    public function forActor(User $actor): Collection
    {
        return ReportExport::query()
            ->where('workspace_id', $this->current->idFor($actor))
            ->where('user_id', $actor->id)
            ->latest()
            ->get();
    }

    public function downloadUrl(ReportExport $export): ?string
    {
        if ($export->status !== 'ready' || $export->expires_at->isPast()) {
            return null;
        }

        $linkExpiresAt = $export->expires_at->lt(now()->addMinutes(15)) ? $export->expires_at : now()->addMinutes(15);

        return URL::temporarySignedRoute('reports.exports.download', $linkExpiresAt, ['export' => $export->id]);
    }

    public function authorize(ReportExport $export, User $actor): void
    {
        if ($export->user_id !== $actor->id || $export->workspace_id !== $this->current->idFor($actor) || $export->expires_at->isPast()) {
            throw new AuthorizationException('The report export is unavailable.');
        }
    }
}
