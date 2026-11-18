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
        if (Schema::hasTable('atk_transactions')) {
            Schema::table('atk_transactions', function (Blueprint $table) {
                if (! Schema::hasColumn('atk_transactions', 'customer_name')) {
                    $table->string('customer_name')->nullable()->after('payment_method');
                }
                if (! Schema::hasColumn('atk_transactions', 'customer_phone')) {
                    $table->string('customer_phone')->nullable()->after('customer_name');
                }
                if (! Schema::hasColumn('atk_transactions', 'atk_customer_id')) {
                    if (Schema::hasTable('atk_customers')) {
                        $table->foreignId('atk_customer_id')->nullable()->after('customer_phone')->constrained('atk_customers')->nullOnDelete();
                    } else {
                        $table->unsignedBigInteger('atk_customer_id')->nullable()->after('customer_phone');
                    }
                }
                if (! Schema::hasColumn('atk_transactions', 'invoice_number')) {
                    $table->string('invoice_number')->nullable()->after('transaction_number');
                }
                if (! Schema::hasColumn('atk_transactions', 'queue_number')) {
                    $table->integer('queue_number')->nullable()->after('user_id');
                }
                if (! Schema::hasColumn('atk_transactions', 'grand_total')) {
                    $table->decimal('grand_total', 15, 2)->default(0)->after('total_amount');
                }
                if (! Schema::hasColumn('atk_transactions', 'is_debt')) {
                    $table->boolean('is_debt')->default(false)->after('amount_paid');
                }
                if (! Schema::hasColumn('atk_transactions', 'due_date')) {
                    $table->date('due_date')->nullable()->after('is_debt');
                }
                if (! Schema::hasColumn('atk_transactions', 'is_settled')) {
                    $table->boolean('is_settled')->default(false)->after('due_date');
                }
                if (! Schema::hasColumn('atk_transactions', 'settled_at')) {
                    $table->dateTime('settled_at')->nullable()->after('is_settled');
                }
                if (! Schema::hasColumn('atk_transactions', 'settled_amount')) {
                    $table->decimal('settled_amount', 15, 2)->default(0)->after('settled_at');
                }
                if (! Schema::hasColumn('atk_transactions', 'transaction_category')) {
                    $table->string('transaction_category')->nullable()->after('transaction_type');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('atk_transactions')) {
            Schema::table('atk_transactions', function (Blueprint $table) {
                $columns = [
                    'customer_name',
                    'customer_phone',
                    'invoice_number',
                    'queue_number',
                    'is_debt',
                    'due_date',
                    'is_settled',
                    'settled_at',
                    'settled_amount',
                    'transaction_category',
                ];

                if (Schema::hasColumn('atk_transactions', 'atk_customer_id')) {
                    try {
                        $table->dropForeign(['atk_customer_id']);
                    } catch (\Throwable $e) {
                    }
                    $columns[] = 'atk_customer_id';
                }

                $existingColumns = array_filter($columns, fn ($col) => Schema::hasColumn('atk_transactions', $col));
                if (! empty($existingColumns)) {
                    $table->dropColumn($existingColumns);
                }
            });
        }
    }
};
