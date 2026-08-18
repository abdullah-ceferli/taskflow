<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(<<<'SQL'
            ALTER TABLE activity_log
                ADD COLUMN workspace_id BIGINT UNSIGNED GENERATED ALWAYS AS (CAST(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(properties, '$.workspace_id')), 'null') AS UNSIGNED)) STORED,
                ADD COLUMN project_id BIGINT UNSIGNED GENERATED ALWAYS AS (CAST(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(properties, '$.project_id')), 'null') AS UNSIGNED)) STORED,
                ADD COLUMN task_id BIGINT UNSIGNED GENERATED ALWAYS AS (CAST(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(properties, '$.task_id')), 'null') AS UNSIGNED)) STORED,
                ADD INDEX activity_workspace_created_index (workspace_id, created_at),
                ADD INDEX activity_project_created_index (project_id, created_at),
                ADD INDEX activity_task_created_index (task_id, created_at),
                ADD INDEX activity_event_created_index (event, created_at)
            SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(<<<'SQL'
            ALTER TABLE activity_log
                DROP INDEX activity_workspace_created_index,
                DROP INDEX activity_project_created_index,
                DROP INDEX activity_task_created_index,
                DROP INDEX activity_event_created_index,
                DROP COLUMN workspace_id,
                DROP COLUMN project_id,
                DROP COLUMN task_id
            SQL);
    }
};
