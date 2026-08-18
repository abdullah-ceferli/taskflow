<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            Schema::table('tasks', fn (Blueprint $table) => $table->fullText(['number', 'title', 'description'], 'tasks_search_fulltext'));
            Schema::table('task_comments', fn (Blueprint $table) => $table->fullText('body', 'task_comments_search_fulltext'));
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            Schema::table('task_comments', fn (Blueprint $table) => $table->dropFullText('task_comments_search_fulltext'));
            Schema::table('tasks', fn (Blueprint $table) => $table->dropFullText('tasks_search_fulltext'));
        }
    }
};
