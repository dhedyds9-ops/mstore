<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $unusedRoleNames = ['noc-operator', 'partner'];

        foreach ($unusedRoleNames as $name) {
            $role = Role::where('name', $name)->first();
            if ($role) {
                // Ensure no users are assigned to this role before deleting
                $userCount = DB::table('users')->where('role_id', $role->id)->count();
                if ($userCount === 0) {
                    $role->permissions()->detach();
                    Cache::forget("sidebar.permission_map.role.{$role->id}");
                    $role->delete();
                }
            }
        }

        Cache::forget('sidebar.menu.tree.v11');
        Cache::forget('sidebar.menu.tree.v14');
        Cache::forget('sidebar.menu.tree.v18');
        Cache::forget('sidebar.menu.tree.v19');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to restore retired unused roles
    }
};
