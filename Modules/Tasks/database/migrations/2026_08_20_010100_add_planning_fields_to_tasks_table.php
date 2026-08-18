<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->foreignId('milestone_id')->nullable()->after('assignee_id')->constrained('project_milestones')->nullOnDelete();
            $table->decimal('estimate_hours', 6, 2)->default(0)->after('priority');
            $table->index(['project_id', 'milestone_id', 'status']);
            $table->index(['assignee_id', 'status', 'due_at']);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropIndex(['project_id', 'milestone_id', 'status']);
            $table->dropIndex(['assignee_id', 'status', 'due_at']);
            $table->dropConstrainedForeignId('milestone_id');
            $table->dropColumn('estimate_hours');
        });
    }
};
