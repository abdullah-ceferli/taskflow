<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workspace_members', function (Blueprint $table): void {
            $table->decimal('weekly_capacity_hours', 6, 2)->default(40)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('workspace_members', fn (Blueprint $table) => $table->dropColumn('weekly_capacity_hours'));
    }
};
