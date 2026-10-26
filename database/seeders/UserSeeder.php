<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::whereIn('name', ['admin', 'super-admin'])->first()
            ?? Role::firstOrCreate(['name' => 'super-admin'], ['label' => 'Super Administrator']);
        $nocRole = Role::whereIn('name', ['noc-operator', 'noc'])->first()
            ?? Role::firstOrCreate(['name' => 'noc-operator'], ['label' => 'NOC Operator']);
        $techRole = Role::whereIn('name', ['field-technician', 'technician'])->first()
            ?? Role::firstOrCreate(['name' => 'field-technician'], ['label' => 'Teknisi Lapangan']);
        $defaultCompanyId = Company::where('is_active', true)->orderBy('id')->value('id');

        User::updateOrCreate(
            ['email' => 'admin@mstore.local'],
            [
                'name' => 'Super Admin',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role_id' => $adminRole?->id,
                'company_id' => $defaultCompanyId,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'noc@mstore.local'],
            [
                'name' => 'NOC Officer',
                'username' => 'noc-operator',
                'password' => Hash::make('password'),
                'role_id' => $nocRole?->id,
                'company_id' => $defaultCompanyId,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'tech1@mstore.local'],
            [
                'name' => 'Technician One',
                'username' => 'tech1',
                'password' => Hash::make('password'),
                'role_id' => $techRole?->id,
                'company_id' => $defaultCompanyId,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'testuser@mstore.id'],
            [
                'name' => 'Test User',
                'username' => 'testuser',
                'password' => Hash::make('Test12345!'),
                'role_id' => $adminRole?->id,
                'company_id' => $defaultCompanyId,
                'is_active' => true,
            ]
        );
    }
}
