<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Support\DefaultRolePermissions;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allPermissions = Permission::all();
        $allPermissionIds = $allPermissions->pluck('id')->all();
        $permissionIdByName = $allPermissions->pluck('id', 'name');

        foreach (DefaultRolePermissions::definitions() as $roleName => $definition) {
            $role = Role::firstOrCreate(
                ['name' => $roleName],
                ['label' => $definition['label']]
            );

            if (DefaultRolePermissions::grantsAll($roleName)) {
                if (! empty($allPermissionIds)) {
                    $role->permissions()->sync($allPermissionIds);
                }
                continue;
            }

            $rawPerms = DefaultRolePermissions::permissionNames($roleName);
            $expandedNames = DefaultRolePermissions::expandPermissionNames($rawPerms, $allPermissions);

            $permissionIds = $permissionIdByName
                ->only($expandedNames)
                ->values()
                ->all();

            if (! empty($permissionIds)) {
                $role->permissions()->sync($permissionIds);
            }
        }
    }
}
