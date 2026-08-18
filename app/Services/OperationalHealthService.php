<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

final class OperationalHealthService
{
    /** @return array{healthy: bool, components: array<string, bool>} */
    public function readiness(): array
    {
        $components = [
            'database' => $this->databaseIsReady(),
            'cache' => $this->cacheIsReady(),
            'queue' => $this->queueIsReady(),
            'storage' => $this->storageIsReady(),
        ];

        return ['healthy' => ! in_array(false, $components, true), 'components' => $components];
    }

    private function databaseIsReady(): bool
    {
        try {
            DB::select('SELECT 1');

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function cacheIsReady(): bool
    {
        $key = 'taskflow:health:'.Str::uuid();

        try {
            Cache::put($key, 'ready', 10);

            return Cache::get($key) === 'ready';
        } catch (Throwable) {
            return false;
        } finally {
            try {
                Cache::forget($key);
            } catch (Throwable) {
                // A failed cleanup must not replace the original readiness result.
            }
        }
    }

    private function queueIsReady(): bool
    {
        try {
            $connection = (string) config('queue.default', 'sync');
            $driver = config("queue.connections.{$connection}.driver");
            if ($driver === 'database') {
                return Schema::hasTable((string) config("queue.connections.{$connection}.table", 'jobs'));
            }

            return is_string($driver) && $driver !== '';
        } catch (Throwable) {
            return false;
        }
    }

    private function storageIsReady(): bool
    {
        $path = 'health/'.Str::uuid().'.txt';

        try {
            Storage::disk('local')->put($path, 'ready');

            return Storage::disk('local')->get($path) === 'ready';
        } catch (Throwable) {
            return false;
        } finally {
            try {
                Storage::disk('local')->delete($path);
            } catch (Throwable) {
                // A failed cleanup must not replace the original readiness result.
            }
        }
    }
}
