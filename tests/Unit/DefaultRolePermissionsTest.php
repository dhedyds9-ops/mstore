<?php

namespace Tests\Unit;

use App\Support\DefaultRolePermissions;
use Tests\TestCase;

class DefaultRolePermissionsTest extends TestCase
{
    public function test_primary_role_templates_cover_all_default_labels(): void
    {
        $templates = collect(DefaultRolePermissions::primaryDefinitions())->pluck('label')->values()->all();

        $this->assertContains('Super Administrator', $templates);
        $this->assertContains('Manager', $templates);
        $this->assertContains('Supervisor Operasional', $templates);
        $this->assertContains('Finance / Admin Keuangan', $templates);
        $this->assertContains('Admin Wedding & Event', $templates);
        $this->assertContains('Staf Logistik & Gudang', $templates);
        $this->assertContains('Kasir POS Wash', $templates);
        $this->assertContains('Kasir POS ATK', $templates);
        $this->assertContains('Operator Wash / Tukang Cuci', $templates);
        $this->assertContains('NOC / Network Monitoring', $templates);
        $this->assertContains('Leader Jaringan', $templates);
        $this->assertContains('Teknisi Lapangan', $templates);
        $this->assertContains('Mitra / Reseller', $templates);
        $this->assertContains('Pelanggan', $templates);
    }
}
