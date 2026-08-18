<?php

use App\Jobs\ArchiveActivityLog;
use App\Jobs\PruneExpiredReportExportsJob;
use App\Jobs\PruneOrphanTaskAttachmentsJob;
use Illuminate\Support\Facades\Schedule;

Schedule::command('taskflow:tasks:notify-due-soon')->dailyAt('08:00')->withoutOverlapping();
Schedule::job(new PruneOrphanTaskAttachmentsJob)->dailyAt('03:00')->withoutOverlapping();
Schedule::command('taskflow:tasks:dispatch-recurring')->everyMinute()->withoutOverlapping();
Schedule::job(new PruneExpiredReportExportsJob)->dailyAt('04:00')->withoutOverlapping();
Schedule::job(new ArchiveActivityLog(now()->subDays((int) config('taskflow.activity_retention.days', 365))->startOfDay()->toDateTimeImmutable()))
    ->dailyAt('02:00')
    ->when(fn (): bool => (bool) config('taskflow.activity_retention.enabled', false))
    ->withoutOverlapping();
Schedule::command('taskflow:operations:check')->everyFiveMinutes()->withoutOverlapping();

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
