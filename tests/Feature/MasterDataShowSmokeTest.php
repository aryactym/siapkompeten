<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\Skema;
use App\Models\Skkni;
use App\Models\UnitKompetensi;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SkemaMenjahitPakaianWanitaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataShowSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(SkemaMenjahitPakaianWanitaSeeder::class);

        $institution = Institution::where('slug', 'bpvp-bandung-barat')->first();
        TenantContext::set($institution);

        $admin = User::factory()->create(['institution_id' => $institution->id, 'status' => 'aktif']);
        $admin->assignRole('admin');
        $admin->institutions()->attach($institution->id, ['role' => 'admin']);
        $this->actingAs($admin);
        $this->institution = $institution;
    }

    private Institution $institution;

    public function test_show_pages_render(): void
    {
        $skkni = Skkni::where('kode', 'SKKNI-GARMENT-2023')->first();
        $skema = Skema::where('kode_skema', 'SKM-MPW-01')->first();
        $unit = UnitKompetensi::where('kode_unit', 'GAR.CM01.003.01')->first();

        $this->get(route('admin.stkkni.show', $skkni))->assertStatus(200);
        $this->get(route('admin.skema.show', $skema))->assertStatus(200);
        $this->get(route('admin.unit.show', $unit))->assertStatus(200);
    }
}
