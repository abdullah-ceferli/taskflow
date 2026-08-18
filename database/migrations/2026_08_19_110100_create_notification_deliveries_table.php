<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('event', 100);
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->date('delivery_date');
            $table->timestamps();
            $table->unique(['workspace_id', 'user_id', 'event', 'subject_type', 'subject_id', 'delivery_date'], 'notification_delivery_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');
    }
};
