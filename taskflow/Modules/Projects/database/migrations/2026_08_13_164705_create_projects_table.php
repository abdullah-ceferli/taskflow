<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'active', 'completed', 'archived'])->default('draft');
            $table->foreignId('owner_id')->constrained('users');
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['owner_id', 'status']);
            $table->index(['status', 'due_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
