<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recurring_tasks', fn (Blueprint $table) => $table->foreign('milestone_id')->references('id')->on('project_milestones')->nullOnDelete());
    }

    public function down(): void
    {
        Schema::table('recurring_tasks', fn (Blueprint $table) => $table->dropForeign(['milestone_id']));
    }
};
