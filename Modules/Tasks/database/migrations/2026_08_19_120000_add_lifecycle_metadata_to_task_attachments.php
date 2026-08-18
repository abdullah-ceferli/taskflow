<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_attachments', function (Blueprint $table): void {
            $table->unsignedSmallInteger('metadata_version')->default(1);
            $table->string('checksum', 64)->nullable()->index();
            $table->string('scan_status')->default('not_scanned')->index();
            $table->unsignedBigInteger('download_count')->default(0);
            $table->timestamp('last_downloaded_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('task_attachments', function (Blueprint $table): void {
            $table->dropColumn(['metadata_version', 'checksum', 'scan_status', 'download_count', 'last_downloaded_at']);
        });
    }
};
