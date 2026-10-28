<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Automatically restore historical kasbon and salary adjustments if table is empty or missing old records
        $backupFile = public_path('backup_mstore_oktober2026.sql');
        if (! file_exists($backupFile)) {
            return;
        }

        try {
            Artisan::call('kasbon:restore');
        } catch (\Throwable $e) {
            Log::warning('Migration restore salary adjustments error: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep data intact
    }
};
