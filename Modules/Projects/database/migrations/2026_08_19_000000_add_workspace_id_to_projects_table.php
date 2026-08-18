<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->foreignId('workspace_id')->nullable()->after('id')->constrained('workspaces')->restrictOnDelete();
            $table->index(['workspace_id', 'status']);
        });

        DB::table('projects')->select('owner_id')->distinct()->orderBy('owner_id')->each(function (object $row): void {
            $workspaceId = DB::table('workspace_members')->where('user_id', $row->owner_id)->value('workspace_id');

            if (! $workspaceId) {
                $workspaceId = DB::table('workspaces')->insertGetId([
                    'name' => 'Legacy Workspace '.$row->owner_id,
                    'slug' => 'legacy-workspace-'.$row->owner_id,
                    'owner_id' => $row->owner_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                DB::table('workspace_members')->insert([
                    'workspace_id' => $workspaceId,
                    'user_id' => $row->owner_id,
                    'role' => 'owner',
                    'joined_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('projects')->where('owner_id', $row->owner_id)->whereNull('workspace_id')->update(['workspace_id' => $workspaceId]);
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('workspace_id');
        });
    }
};
