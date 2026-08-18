<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_milestones', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->date('due_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['project_id', 'due_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_milestones');
    }
};
