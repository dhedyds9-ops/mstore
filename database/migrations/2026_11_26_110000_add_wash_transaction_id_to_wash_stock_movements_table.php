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
        Schema::table('wash_stock_movements', function (Blueprint $table) {
            if (! Schema::hasColumn('wash_stock_movements', 'wash_transaction_id')) {
                $table->foreignId('wash_transaction_id')
                    ->nullable()
                    ->after('transaction_id')
                    ->constrained('wash_transactions')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wash_stock_movements', function (Blueprint $table) {
            if (Schema::hasColumn('wash_stock_movements', 'wash_transaction_id')) {
                $table->dropConstrainedForeignId('wash_transaction_id');
            }
        });
    }
};
