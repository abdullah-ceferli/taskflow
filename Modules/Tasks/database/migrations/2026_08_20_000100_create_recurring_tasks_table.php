<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_tasks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('milestone_id')->nullable();
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->string('priority', 20);
            $table->decimal('estimate_hours', 6, 2)->default(0);
            $table->string('frequency', 20);
            $table->unsignedSmallInteger('interval')->default(1);
            $table->string('timezone', 64);
            $table->unsignedSmallInteger('due_offset_days')->default(0);
            $table->timestamp('next_run_at');
            $table->timestamp('last_generated_at')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->index(['active', 'next_run_at']);
            $table->index(['workspace_id', 'project_id']);
        });

        Schema::create('recurring_task_occurrences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('recurring_task_id')->constrained('recurring_tasks')->cascadeOnDelete();
            $table->timestamp('scheduled_for');
            $table->foreignId('task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->timestamps();
            $table->unique(['recurring_task_id', 'scheduled_for'], 'recurring_occurrence_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_task_occurrences');
        Schema::dropIfExists('recurring_tasks');
    }
};
