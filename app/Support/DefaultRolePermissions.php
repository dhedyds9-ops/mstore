<?php

namespace App\Support;

use Illuminate\Support\Collection;

class DefaultRolePermissions
{
    public static function definitions(): array
    {
        return [
            'super-admin' => [
                'label' => 'Super Administrator',
                'grants_all' => true,
            ],

            'manager' => [
                'label' => 'Manager',
                'permissions' => [
                    'dashboard.view',
                    'finance.view',
                    'finance.report',
                    'investor.view',
                    'consolidation.view',
                    'atk.*',
                    'wash.*',
                    'wedding.*',
                    'cctv.*',
                    'inventory.*',
                    'attendance.*',
                    'employee.view',
                    'employee.create',
                    'employee.edit',
                    'leave.*',
                    'schedule.*',
                    'report.*',
                    'profile.view',
                    'profile.update',
                    'notification.view',
                    'notification.manage',
                    'telegram.*',
                    'kasbon.view',
                    'kasbon.manage',
                ],
            ],

            'supervisor' => [
                'label' => 'Supervisor Operasional',
                'permissions' => [
                    'dashboard.view',
                    'atk.*',
                    'wash.*',
                    'wedding.view',
                    'cctv.view',
                    'inventory.*',
                    'attendance.view',
                    'attendance.create',
                    'attendance.report',
                    'schedule.view',
                    'schedule.manage',
                    'leave.view',
                    'leave.create',
                    'employee.view',
                    'profile.view',
                    'profile.update',
                    'notification.view',
                    'notification.manage',
                    'telegram.*',
                ],
            ],

            'finance' => [
                'label' => 'Finance / Admin Keuangan',
                'permissions' => [
                    'dashboard.view',
                    'finance.*',
                    'investor.*',
                    'accounting.*',
                    'receipt.*',
                    'fee.*',
                    'payment.*',
                    'consolidation.view',
                    'attendance.view',
                    'attendance.create',
                    'attendance.edit',
                    'attendance.delete',
                    'attendance.report',
                    'leave.*',
                    'schedule.*',
                    'employee.view',
                    'employee.create',
                    'employee.edit',
                    'kasbon.view',
                    'kasbon.manage',
                    'report.*',
                    'profile.view',
                    'profile.update',
                    'notification.view',
                    'notification.manage',
                    'telegram.*',
                ],
            ],

            'wedding-organizer' => [
                'label' => 'Admin Wedding & Event',
                'permissions' => [
                    'dashboard.view',
                    'wedding.*',
                    'cctv.*',
                    'attendance.view',
                    'attendance.create',
                    'schedule.view',
                    'leave.view',
                    'leave.create',
                    'profile.view',
                    'profile.update',
                    'notification.view',
                ],
            ],

            'warehouse-staff' => [
                'label' => 'Staf Logistik & Gudang',
                'permissions' => [
                    'dashboard.view',
                    'inventory.*',
                    'atk.view',
                    'wash.view',
                    'attendance.view',
                    'attendance.create',
                    'schedule.view',
                    'leave.view',
                    'leave.create',
                    'profile.view',
                    'profile.update',
                    'notification.view',
                ],
            ],

            'wash-cashier' => [
                'label' => 'Kasir POS Wash',
                'permissions' => [
                    'dashboard.view',
                    'wash.*',
                    'inventory.view',
                    'attendance.view',
                    'attendance.create',
                    'schedule.view',
                    'leave.view',
                    'leave.create',
                    'profile.view',
                    'profile.update',
                    'notification.view',
                ],
            ],

            'atk-cashier' => [
                'label' => 'Kasir POS ATK',
                'permissions' => [
                    'dashboard.view',
                    'atk.*',
                    'inventory.view',
                    'attendance.view',
                    'attendance.create',
                    'schedule.view',
                    'leave.view',
                    'leave.create',
                    'profile.view',
                    'profile.update',
                    'notification.view',
                ],
            ],

            'wash-operator' => [
                'label' => 'Operator Wash / Tukang Cuci',
                'permissions' => [
                    'dashboard.view',
                    'wash.view',
                    'attendance.view',
                    'attendance.create',
                    'schedule.view',
                    'leave.view',
                    'leave.create',
                    'leave.edit',
                    'profile.view',
                    'profile.update',
                    'notification.view',
                ],
            ],

            'noc-operator' => [
                'label' => 'NOC / Network Monitoring',
                'permissions' => [
                    'dashboard.view',
                    'attendance.view',
                    'attendance.create',
                    'schedule.view',
                    'leave.view',
                    'leave.create',
                    'leave.edit',
                    'profile.view',
                    'profile.update',
                    'notification.view',
                ],
            ],

            'field-leader' => [
                'label' => 'Leader Jaringan',
                'permissions' => [
                    'dashboard.view',
                    'attendance.view',
                    'attendance.create',
                    'attendance.edit',
                    'schedule.view',
                    'leave.view',
                    'leave.create',
                    'leave.edit',
                    'profile.view',
                    'profile.update',
                    'notification.view',
                ],
            ],

            'field-technician' => [
                'label' => 'Teknisi Lapangan',
                'permissions' => [
                    'dashboard.view',
                    'attendance.view',
                    'attendance.create',
                    'schedule.view',
                    'leave.view',
                    'leave.create',
                    'leave.edit',
                    'profile.view',
                    'profile.update',
                    'notification.view',
                ],
            ],

            'partner' => [
                'label' => 'Mitra / Reseller',
                'permissions' => [
                    'dashboard.view',
                    'profile.view',
                    'profile.update',
                    'notification.view',
                ],
            ],

            'customer' => [
                'label' => 'Pelanggan',
                'permissions' => [
                    'profile.view',
                    'profile.update',
                    'notification.view',
                ],
            ],
        ];
    }

    public static function primaryDefinitions(): array
    {
        return array_filter(
            self::definitions(),
            static fn (array $definition): bool => ! isset($definition['inherits'])
        );
    }

    public static function resolve(string $roleName): array
    {
        $definitions = self::definitions();
        $definition = $definitions[$roleName] ?? ['label' => ucfirst($roleName), 'permissions' => []];

        if (isset($definition['inherits'])) {
            $parent = self::resolve((string) $definition['inherits']);
            $parentPerms = $parent['permissions'] ?? [];
            $extra = $definition['extra_permissions'] ?? [];
            $exclude = $definition['exclude_permissions'] ?? [];

            if (! is_array($parentPerms)) {
                $parentPerms = [];
            }

            $merged = array_unique(array_merge($parentPerms, (array) $extra));
            if ($exclude) {
                $merged = array_values(array_diff($merged, (array) $exclude));
            }

            return [
                'label' => $definition['label'] ?? ($parent['label'] ?? ucfirst($roleName)),
                'permissions' => array_values($merged),
            ];
        }

        return $definition;
    }

    public static function permissionNames(string $roleName): array
    {
        $definition = self::resolve($roleName);

        return (array) ($definition['permissions'] ?? []);
    }

    public static function grantsAll(string $roleName): bool
    {
        $definition = self::resolve($roleName);

        return (bool) ($definition['grants_all'] ?? false);
    }

    public static function standardTemplatePermissionIds(Collection $allowedPermissions): array
    {
        $allowedIds = $allowedPermissions->pluck('id')->all();
        $allPermissionIds = $allowedPermissions->pluck('id')->values()->toArray();
        $allowedByName = $allowedPermissions->pluck('id', 'name');

        $templates = [];
        foreach (self::primaryDefinitions() as $roleName => $definition) {
            $label = $definition['label'];

            if (self::grantsAll($roleName)) {
                $templates[$label] = $allPermissionIds;
                continue;
            }

            $permissionNames = self::permissionNames($roleName);
            $ids = [];
            foreach ($permissionNames as $permName) {
                if (str_contains($permName, '.*') || str_ends_with($permName, '*')) {
                    $prefix = rtrim($permName, '*');
                    foreach ($allowedByName as $name => $id) {
                        if (str_starts_with((string) $name, $prefix)) {
                            $ids[] = $id;
                        }
                    }
                } else {
                    if (isset($allowedByName[$permName])) {
                        $ids[] = $allowedByName[$permName];
                    }
                }
            }

            $ids = array_values(array_unique(array_intersect($ids, $allowedIds)));
            $templates[$label] = $ids;
        }

        return $templates;
    }

    public static function expandPermissionNames(array $rawPermissionNames, Collection $allPermissions): array
    {
        $flatNames = $allPermissions->pluck('name')->all();
        $expanded = [];
        foreach ($rawPermissionNames as $perm) {
            if (! is_string($perm)) {
                continue;
            }
            if (str_contains($perm, '.*') || str_ends_with($perm, '*')) {
                $prefix = rtrim($perm, '.*');
                foreach ($flatNames as $dbName) {
                    if (str_starts_with((string) $dbName, $prefix)) {
                        $expanded[] = (string) $dbName;
                    }
                }
            } else {
                $expanded[] = $perm;
            }
        }
        return array_values(array_unique($expanded));
    }
}
