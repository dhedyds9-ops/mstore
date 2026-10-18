<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantRegisterTest extends TestCase
{
    use RefreshDatabase;
    public function test_tenant_registration_page_is_accessible()
    {
        $response = $this->get(route('tenant.register.create'));
        $response->assertStatus(200);
        $response->assertSee('Daftar Toko & Coba Gratis 14 Hari');
    }

    public function test_new_store_can_register_and_starts_14_day_trial()
    {
        // Pastikan role admin tersedia
        Role::firstOrCreate(['name' => Role::ADMIN], ['label' => 'Admin Usaha', 'is_system' => true]);

        $randomEmail = 'pemilik_' . uniqid() . '@tokobaru.com';

        $response = $this->post(route('tenant.register.store'), [
            'store_name' => 'Toko Sembako Berkah Jaya',
            'owner_name' => 'Budi Santoso',
            'email' => $randomEmail,
            'phone' => '081234567899',
            'business_type' => 'starter',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();

        $user = User::where('email', $randomEmail)->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->company_id);

        $company = $user->company;
        $this->assertEquals('Toko Sembako Berkah Jaya', $company->name);
        $this->assertEquals('starter', $company->subscription_plan);
        $this->assertEquals('trial', $company->subscription_status);
        $this->assertNotNull($company->trial_ends_at);
        $this->assertTrue($company->hasActiveSubscription());
    }
}
