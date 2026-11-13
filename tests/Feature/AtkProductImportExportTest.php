<?php

namespace Tests\Feature;

use App\Models\AtkProduct;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AtkProductImportExportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'admin']);
        $role->givePermissionTo('atk.manage');

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_can_download_atk_product_template_xlsx_and_csv(): void
    {
        $responseXlsx = $this->actingAs($this->admin)->get(route('atk.products.template'));
        $responseXlsx->assertStatus(200);
        $responseXlsx->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $responseCsv = $this->actingAs($this->admin)->get(route('atk.products.template', ['format' => 'csv']));
        $responseCsv->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $responseCsv->headers->get('content-type'));
    }

    public function test_can_export_atk_products(): void
    {
        AtkProduct::create([
            'code' => 'ATK-TEST-001',
            'barcode' => 'ATK-TEST-001',
            'name' => 'Kertas A4 Test',
            'category' => 'ATK',
            'price' => 50000,
            'cost_price' => 40000,
            'stock' => 20,
            'unit' => 'Rim',
        ]);

        $responseXlsx = $this->actingAs($this->admin)->get(route('atk.products.export'));
        $responseXlsx->assertStatus(200);

        $responseCsv = $this->actingAs($this->admin)->get(route('atk.products.export', ['format' => 'csv']));
        $responseCsv->assertStatus(200);
    }

    public function test_can_import_products_from_csv_and_updates_on_reimport(): void
    {
        $csvContent = "Kode Barang;Nama Produk;Kategori;Harga Jual;Harga Beli / HPP;Stok;Satuan;Deskripsi\n" .
                      "ATK-PUL-01;Pulpen Gel Hitam;ATK;Rp 5.000;Rp 3.500;50;Pcs;Pulpen gel mantap\n" .
                      ";Kertas Kuarto 70gr;KERTAS;45000;35000;10;Rim;Kertas HVS\n";

        $file = UploadedFile::fake()->createWithContent('products.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('atk.products.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('atk.products.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('atk_products', [
            'code' => 'ATK-PUL-01',
            'name' => 'Pulpen Gel Hitam',
            'price' => 5000,
            'cost_price' => 3500,
            'stock' => 50,
        ]);

        // Product without code should have an auto-generated unique code
        $kuarto = AtkProduct::where('name', 'Kertas Kuarto 70gr')->first();
        $this->assertNotNull($kuarto);
        $this->assertNotEmpty($kuarto->code);
        $initialCode = $kuarto->code;

        // Re-import with updated price and no code: should update existing product without duplicating
        $csvUpdate = "Nama Produk;Harga Jual;Stok\n" .
                     "Kertas Kuarto 70gr;52000;15\n";

        $updateFile = UploadedFile::fake()->createWithContent('update.csv', $csvUpdate);
        $updateResp = $this->actingAs($this->admin)->post(route('atk.products.import'), [
            'file' => $updateFile,
        ]);

        $updateResp->assertRedirect(route('atk.products.index'));
        $this->assertEquals(2, AtkProduct::count());

        $kuarto->refresh();
        $this->assertEquals(52000, $kuarto->price);
        $this->assertEquals(15, $kuarto->stock);
        $this->assertEquals($initialCode, $kuarto->code);
    }
}
