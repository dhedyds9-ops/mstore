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
        if (Schema::hasTable('atk_transactions') && ! Schema::hasColumn('atk_transactions', 'atk_cash_register_id')) {
            Schema::table('atk_transactions', function (Blueprint $table) {
                if (Schema::hasTable('atk_cash_registers')) {
                    $table->foreignId('atk_cash_register_id')
                        ->nullable()
                        ->after('user_id')
                        ->constrained('atk_cash_registers')
                        ->nullOnDelete();
                } else {
                    $table->unsignedBigInteger('atk_cash_register_id')->nullable()->after('user_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('atk_transactions') && Schema::hasColumn('atk_transactions', 'atk_cash_register_id')) {
            Schema::table('atk_transactions', function (Blueprint $table) {
                try {
                    $table->dropForeign(['atk_cash_register_id']);
                } catch (\Throwable $e) {
                }
                $table->dropColumn('atk_cash_register_id');
            });
        }
    }
};
