<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{






















    public const SUPER_ADMIN = 'super-admin';
    public const ADMIN = 'admin';
    public const DIREKTUR = 'direktur';
    public const LEADER = 'leader';
    public const NOC = 'noc';
    public const NOC_LEGACY = 'network-operations-center';
    public const TECHNICIAN = 'technician';
    public const COORDINATOR = 'coordinator';
    public const FINANCE = 'finance';
    public const HRD_MANAGER = 'hrd-manager';
    public const CUSTOMER_SERVICE = 'customer-service';
    public const CUSTOMER = 'customer';
    public const RESELLER = 'reseller';
    public const KASIR_ATK = 'kasir-atk';
    public const KASIR_WASH = 'kasir-wash';
    public const KARYAWAN_WASH = 'karyawan-wash';
    public const STAFF_GUDANG = 'staff-gudang';

    protected $fillable = ['name', 'label', 'is_system'];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function hasPermission($permissionName): bool
    {
        return $this->permissions->contains('name', $permissionName);
    }

    public static function allSystemRoleNames(): array
    {
        return array_keys((array) config('roles.definitions', []));
    }

    public static function allSystemRoleLabels(): array
    {
        return array_map(
            static fn (array $def): string => $def['label'] ?? '',
            (array) config('roles.definitions', [])
        );
    }

    public static function hiddenInUserManagement(): array
    {
        $defaultHidden = [
            'customer',
            'pelanggan',
            'partner',
            'mitra',
            'reseller',
            'noc-operator',
            'noc-legacy',
            'noc',
            'field-leader',
            'field-technician',
            'technician',
            'leader',
        ];

        return array_values(array_unique(array_merge(
            (array) config('roles.hidden_in_user_management', []),
            $defaultHidden
        )));
    }

    public static function superAdminRoleNames(): array
    {
        return (array) config('roles.super_admin_role_names', ['super-admin']);
    }
}
