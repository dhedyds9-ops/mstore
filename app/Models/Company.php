<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    public const PLAN_STARTER = 'starter';
    public const PLAN_SERVICE = 'service';
    public const PLAN_PRO = 'pro';
    public const PLAN_ENTERPRISE = 'enterprise';

    public const STATUS_TRIAL = 'trial';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'name',
        'code',
        'tax_id',
        'currency',
        'country',
        'address',
        'is_active',
        'subscription_plan',
        'subscription_status',
        'trial_ends_at',
        'subscription_ends_at',
        'enabled_modules',
        'max_branches',
        'max_users',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'enabled_modules' => 'array',
        'max_branches' => 'integer',
        'max_users' => 'integer',
    ];

    /**
     * Modul standar per paket langganan
     */
    public static function defaultModulesForPlan(string $plan): array
    {
        return match ($plan) {
            self::PLAN_STARTER => ['pos', 'accounting', 'hr', 'messaging'],
            self::PLAN_SERVICE => ['wash', 'accounting', 'hr', 'messaging'],
            self::PLAN_PRO => ['pos', 'wash', 'wedding', 'accounting', 'finance', 'hr', 'messaging'],
            self::PLAN_ENTERPRISE => ['pos', 'wash', 'wedding', 'accounting', 'finance', 'hr', 'cctv', 'isp', 'ticketing', 'messaging'],
            default => ['pos', 'wash', 'wedding', 'accounting', 'finance', 'hr', 'messaging'],
        };
    }

    /**
     * Cek apakah masa langganan atau trial masih aktif
     */
    public function hasActiveSubscription(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->subscription_status === self::STATUS_ACTIVE) {
            if ($this->subscription_ends_at && $this->subscription_ends_at->isPast()) {
                return false;
            }
            return true;
        }

        if ($this->subscription_status === self::STATUS_TRIAL) {
            if ($this->trial_ends_at && $this->trial_ends_at->isPast()) {
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * Daftar modul aktif untuk tenant ini
     */
    public function getEffectiveModules(): array
    {
        if (!empty($this->enabled_modules) && is_array($this->enabled_modules)) {
            return $this->enabled_modules;
        }

        return self::defaultModulesForPlan($this->subscription_plan ?? self::PLAN_PRO);
    }

    /**
     * Cek apakah tenant diizinkan membuka modul tertentu
     */
    public function canAccessModule(string $module): bool
    {
        return in_array($module, $this->getEffectiveModules(), true);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(CompanyBranch::class);
    }

    public function generalTransactions(): HasMany
    {
        return $this->hasMany(GeneralTransaction::class);
    }

    public function journals(): HasMany
    {
        return $this->hasMany(Journal::class);
    }
}