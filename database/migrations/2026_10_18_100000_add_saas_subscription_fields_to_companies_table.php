<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('subscription_plan', 50)->default('starter')->after('is_active');
            $table->string('subscription_status', 30)->default('trial')->after('subscription_plan');
            $table->timestamp('trial_ends_at')->nullable()->after('subscription_status');
            $table->timestamp('subscription_ends_at')->nullable()->after('trial_ends_at');
            $table->json('enabled_modules')->nullable()->after('subscription_ends_at');
            $table->unsignedInteger('max_branches')->default(1)->after('enabled_modules');
            $table->unsignedInteger('max_users')->default(5)->after('max_branches');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'subscription_plan',
                'subscription_status',
                'trial_ends_at',
                'subscription_ends_at',
                'enabled_modules',
                'max_branches',
                'max_users',
            ]);
        });
    }
};
