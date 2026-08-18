<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

final class RestoreDrill extends Command
{
    protected $signature = 'taskflow:backup:restore-drill';

    protected $description = 'Run a disposable SQLite backup and restore integrity drill';

    public function handle(): int
    {
        $directory = storage_path('framework/testing/restore-drill-'.Str::uuid());
        $source = $directory.'/source.sqlite';
        $backup = $directory.'/backup.sqlite';
        $restored = $directory.'/restored.sqlite';
        $originalDefault = config('database.default');

        File::ensureDirectoryExists($directory);
        File::put($source, '');

        try {
            $this->configureDatabase($source);
            $exitCode = Artisan::call('migrate', ['--database' => 'taskflow_restore_drill', '--force' => true]);
            if ($exitCode !== self::SUCCESS) {
                throw new \RuntimeException('Disposable migration failed.');
            }

            $sentinel = 'restore-drill-'.Str::uuid().'@taskflow.invalid';
            DB::connection('taskflow_restore_drill')->table('users')->insert([
                'name' => 'Restore Drill',
                'email' => $sentinel,
                'password' => 'not-a-login-password',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::disconnect('taskflow_restore_drill');
            File::copy($source, $backup);
            File::copy($backup, $restored);

            $this->configureDatabase($restored);
            $verified = DB::connection('taskflow_restore_drill')->table('users')->where('email', $sentinel)->exists();
            $migrations = DB::connection('taskflow_restore_drill')->table('migrations')->count();
            if (! $verified || $migrations === 0) {
                throw new \RuntimeException('Restored database failed integrity verification.');
            }

            $this->info("Restore drill passed with {$migrations} migrations and a verified sentinel row.");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('Restore drill failed: '.$exception->getMessage());

            return self::FAILURE;
        } finally {
            DB::disconnect('taskflow_restore_drill');
            Config::set('database.default', $originalDefault);
            Config::set('database.connections.taskflow_restore_drill', null);
            File::deleteDirectory($directory);
        }
    }

    private function configureDatabase(string $database): void
    {
        Config::set('database.default', 'taskflow_restore_drill');
        Config::set('database.connections.taskflow_restore_drill', [
            'driver' => 'sqlite',
            'database' => $database,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        DB::purge('taskflow_restore_drill');
    }
}
