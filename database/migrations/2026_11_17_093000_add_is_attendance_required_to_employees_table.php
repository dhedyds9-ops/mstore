<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('employees', 'is_attendance_required')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->boolean('is_attendance_required')->default(true)->after('employment_status');
            });

            // Set false for Super Admin or Executive/Director if any
            DB::table('employees')
                ->where('position', 'like', '%Super Admin%')
                ->orWhere('position', 'like', '%Direktur%')
                ->update(['is_attendance_required' => false]);

            // Ensure other active employees are set to true
            DB::table('employees')
                ->where('position', 'not like', '%Super Admin%')
                ->where('position', 'not like', '%Direktur%')
                ->update(['is_attendance_required' => true]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('employees', 'is_attendance_required')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('is_attendance_required');
            });
        }
    }
};
