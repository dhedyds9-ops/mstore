<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('atk_stock_movements')) {
            Schema::table('atk_stock_movements', function (Blueprint $table) {
                if (! Schema::hasColumn('atk_stock_movements', 'unit_price')) {
                    $table->decimal('unit_price', 15, 2)->default(0)->after('quantity');
                }
                if (! Schema::hasColumn('atk_stock_movements', 'total_cost')) {
                    $table->decimal('total_cost', 15, 2)->default(0)->after('unit_price');
                }
                if (! Schema::hasColumn('atk_stock_movements', 'supplier_id')) {
                    $table->foreignId('supplier_id')->nullable()->after('total_cost')->constrained('atk_suppliers')->nullOnDelete();
                }
                if (! Schema::hasColumn('atk_stock_movements', 'invoice_number')) {
                    $table->string('invoice_number')->nullable()->after('supplier_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('atk_stock_movements')) {
            Schema::table('atk_stock_movements', function (Blueprint $table) {
                if (Schema::hasColumn('atk_stock_movements', 'supplier_id')) {
                    $table->dropForeign(['supplier_id']);
                    $table->dropColumn('supplier_id');
                }
                if (Schema::hasColumn('atk_stock_movements', 'invoice_number')) {
                    $table->dropColumn('invoice_number');
                }
                if (Schema::hasColumn('atk_stock_movements', 'total_cost')) {
                    $table->dropColumn('total_cost');
                }
                if (Schema::hasColumn('atk_stock_movements', 'unit_price')) {
                    $table->dropColumn('unit_price');
                }
            });
        }
    }
};
