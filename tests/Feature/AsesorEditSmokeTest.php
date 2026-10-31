<?php

namespace Tests\Feature;

use App\Models\Asesor;
use App\Models\Institution;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Province;
use Tests\TestCase;

class AsesorEditSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $institution = Institution::create(['name' => 'LSP Asesor', 'slug' => 'lsp-asesor', 'is_active' => true, 'is_setup_completed' => true]);
        TenantContext::set($institution);

        Province::firstOrCreate(['code' => '31'], ['name' => 'DKI JAKARTA']);
        City::firstOrCreate(['code' => '3171'], ['province_code' => '31', 'name' => 'KOTA JAKARTA SELATAN']);

        $user = User::factory()->create(['institution_id' => $institution->id, 'name' => 'Asesor Uji', 'status' => 'aktif']);
        $user->assignRole('asesor');
        $user->institutions()->attach($institution->id, ['role' => 'asesor']);

        $this->asesor = Asesor::create([
            'institution_id' => $institution->id,
            'user_id' => $user->id,
            'no_register' => 'REG-001',
            'kebangsaan' => 'Indonesia',
            'status' => 'aktif',
        ]);
        $this->admin = User::factory()->create(['institution_id' => $institution->id, 'status' => 'aktif']);
        $this->admin->assignRole('admin');
        $this->admin->institutions()->attach($institution->id, ['role' => 'admin']);
    }

    private User $admin;

    private Asesor $asesor;

    public function test_asesor_edit_renders_and_saves_province_city(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.asesor.edit', $this->asesor))
            ->assertStatus(200);

        $this->actingAs($this->admin)
            ->put(route('admin.asesor.update', $this->asesor), [
                'no_register' => 'REG-001',
                'kebangsaan' => 'Indonesia',
                'status' => 'aktif',
                'province_code' => '31',
                'city_code' => '3171',
                'bidang_keahlian' => 'Teknologi Informasi',
            ])
            ->assertRedirect(route('admin.asesor.index'));

        $this->asesor->refresh();
        $this->assertSame('31', $this->asesor->province_code);
        $this->assertSame('3171', $this->asesor->city_code);
        $this->assertSame('Teknologi Informasi', $this->asesor->bidang_keahlian);
    }
}
