<?php

namespace Modules\Tasks\Services;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Modules\Tasks\Enums\RecurrenceFrequency;

final class RecurrenceCalculator
{
    public function next(DateTimeInterface $scheduledFor, RecurrenceFrequency $frequency, int $interval, string $timezone): CarbonImmutable
    {
        $local = CarbonImmutable::instance($scheduledFor)->setTimezone($timezone);
        $next = match ($frequency) {
            RecurrenceFrequency::Daily => $local->addDays($interval),
            RecurrenceFrequency::Weekly => $local->addWeeks($interval),
            RecurrenceFrequency::Monthly => $local->addMonthsNoOverflow($interval),
        };

        return $next->utc();
    }
}
