<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\ElemenKompetensi;
use App\Models\Institution;
use App\Models\KriteriaUnjukKerja;
use App\Models\Skema;
use App\Models\SkemaUnit;
use App\Models\Skkni;
use App\Models\Tuk;
use App\Models\UnitKompetensi;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Province;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    private Institution $lsp1;

    private Institution $lsp2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lsp1 = Institution::create([
            'name' => 'LSP Pertama',
            'slug' => 'lsp-pertama',
            'is_active' => true,
        ]);

        $this->lsp2 = Institution::create([
            'name' => 'LSP Kedua',
            'slug' => 'lsp-kedua',
            'is_active' => true,
        ]);
    }

    public function test_skema_is_isolated_between_tenants(): void
    {
        TenantContext::set($this->lsp1);
        $skemaLsp1 = Skema::create([
            'kode_skema' => 'SKM-01',
            'nama_skema' => 'Skema LSP 1',
            'jenis' => 'okupasi',
            'biaya' => 500000,
        ]);

        TenantContext::set($this->lsp2);
        $this->assertCount(0, Skema::all());

        $skemaLsp2 = Skema::create([
            'kode_skema' => 'SKM-01', // Kode sama diperbolehkan antar tenant berbeda
            'nama_skema' => 'Skema LSP 2',
            'jenis' => 'okupasi',
            'biaya' => 600000,
        ]);

        $this->assertCount(1, Skema::all());
        $this->assertSame('Skema LSP 2', Skema::first()->nama_skema);

        TenantContext::set($this->lsp1);
        $this->assertCount(1, Skema::all());
        $this->assertSame('Skema LSP 1', Skema::first()->nama_skema);
    }

    public function test_many_to_many_skema_and_unit_kompetensi_relationship(): void
    {
        TenantContext::set($this->lsp1);

        $skkni = Skkni::create([
            'kode' => 'SKKNI-TIK-2020',
            'judul' => 'SKKNI TIK',
            'tahun' => 2020,
        ]);

        $sharedUnit = UnitKompetensi::create([
            'skkni_id' => $skkni->id,
            'kode_unit' => 'J.620100.005.02',
            'judul_unit' => 'Mengimplementasikan User Interface',
            'standar_kompetensi' => 'SKKNI',
        ]);

        $unitLain = UnitKompetensi::create([
            'skkni_id' => $skkni->id,
            'kode_unit' => 'J.620100.017.02',
            'judul_unit' => 'Mengimplementasikan Basis Data',
            'standar_kompetensi' => 'SKKNI',
        ]);

        $skemaA = Skema::create([
            'kode_skema' => 'SKEMA-A',
            'nama_skema' => 'Junior Web Developer',
            'jenis' => 'okupasi',
        ]);

        $skemaB = Skema::create([
            'kode_skema' => 'SKEMA-B',
            'nama_skema' => 'Fullstack Web Developer',
            'jenis' => 'okupasi',
        ]);

        // Hubungkan Many-to-Many
        SkemaUnit::create([
            'skema_id' => $skemaA->id,
            'unit_kompetensi_id' => $sharedUnit->id,
            'jenis' => 'inti',
            'urutan' => 1,
        ]);

        SkemaUnit::create([
            'skema_id' => $skemaB->id,
            'unit_kompetensi_id' => $sharedUnit->id,
            'jenis' => 'inti',
            'urutan' => 1,
        ]);

        SkemaUnit::create([
            'skema_id' => $skemaB->id,
            'unit_kompetensi_id' => $unitLain->id,
            'jenis' => 'pilihan',
            'urutan' => 2,
        ]);

        // Verifikasi relasi
        $this->assertCount(1, $skemaA->unitKompetensis);
        $this->assertCount(2, $skemaB->unitKompetensis);
        $this->assertCount(2, $sharedUnit->skemas);
        $this->assertSame('inti', $skemaA->unitKompetensis->first()->pivot->jenis);
    }

    public function test_unit_kompetensi_hierarchy_with_elemen_and_kuk(): void
    {
        TenantContext::set($this->lsp1);

        $unit = UnitKompetensi::create([
            'kode_unit' => 'TEST-001',
            'judul_unit' => 'Unit Uji Coba',
        ]);

        $elemen = ElemenKompetensi::create([
            'unit_kompetensi_id' => $unit->id,
            'urutan' => 1,
            'nama_elemen' => 'Elemen Pertama',
        ]);

        $kuk1 = KriteriaUnjukKerja::create([
            'elemen_kompetensi_id' => $elemen->id,
            'urutan' => '1.1',
            'pernyataan_kuk' => 'Pernyataan KUK 1.1',
            'pertanyaan_observasi' => 'Pertanyaan Observasi',
        ]);

        $this->assertCount(1, $unit->elemenKompetensis);
        $this->assertCount(1, $unit->elemenKompetensis->first()->kriteriaUnjukKerjas);
        $this->assertSame('Pernyataan KUK 1.1', $elemen->kriteriaUnjukKerjas->first()->pernyataan_kuk);
    }

    public function test_tuk_belongs_to_tenant_and_links_to_laravolt_indonesia(): void
    {
        TenantContext::set($this->lsp1);

        // Buat mock data provinsi dan kota bila belum ada di DB test
        $province = Province::firstOrCreate(['code' => '31'], ['name' => 'DKI JAKARTA']);
        $city = City::firstOrCreate(
            ['code' => '3171'],
            ['province_code' => '31', 'name' => 'KOTA JAKARTA SELATAN']
        );

        $tuk = Tuk::create([
            'kode_tuk' => 'TUK-JKT-01',
            'nama_tuk' => 'TUK Mandiri Tebet',
            'jenis' => 'mandiri',
            'penanggung_jawab' => 'Budi Sudarsono',
            'province_code' => '31',
            'city_code' => '3171',
        ]);

        $this->assertSame('TUK-JKT-01', $tuk->kode_tuk);
        $this->assertNotNull($tuk->province);
        $this->assertSame('DKI JAKARTA', $tuk->province->name);
        $this->assertNotNull($tuk->city);
        $this->assertSame('KOTA JAKARTA SELATAN', $tuk->city->name);
    }

    public function test_app_settings_supports_global_and_tenant_fallback(): void
    {
        // Global setting (institution_id = null)
        AppSetting::create([
            'institution_id' => null,
            'key' => 'app_timezone',
            'value' => 'Asia/Jakarta',
        ]);

        TenantContext::clear();
        $this->assertSame('Asia/Jakarta', AppSetting::get('app_timezone'));

        // LSP1 pakai default global
        TenantContext::set($this->lsp1);
        $this->assertSame('Asia/Jakarta', AppSetting::get('app_timezone'));

        // LSP1 override setting
        AppSetting::set('app_timezone', 'Asia/Makassar');
        $this->assertSame('Asia/Makassar', AppSetting::get('app_timezone'));

        // LSP2 tetap mendapat default global
        TenantContext::set($this->lsp2);
        $this->assertSame('Asia/Jakarta', AppSetting::get('app_timezone'));
    }
}
