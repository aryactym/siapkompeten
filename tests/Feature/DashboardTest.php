<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->institution = Institution::create([
            'name' => 'LSP Uji Coba',
            'slug' => 'lsp-uji-coba',
            'is_active' => true,
        ]);
    }

    public function test_admin_sees_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'aktif',
        ]);
        $admin->assignRole('admin');
        $admin->institutions()->attach($this->institution->id, ['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Admin Portal');
        $response->assertSee('Dashboard Lembaga');
        $response->assertSee('Master Data');
    }

    public function test_asesor_sees_asesor_dashboard(): void
    {
        $asesor = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'aktif',
        ]);
        $asesor->assignRole('asesor');
        $asesor->institutions()->attach($this->institution->id, ['role' => 'asesor']);

        $response = $this->actingAs($asesor)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Portal Asesor');
        $response->assertSee('Penugasan Uji Kompetensi Terdekat');
        $response->assertSee('FR.MAK.02');
    }

    public function test_asesi_sees_asesi_dashboard_with_phase_gating_and_guide(): void
    {
        $asesi = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'aktif',
        ]);
        $asesi->assignRole('asesi');
        $asesi->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        $response = $this->actingAs($asesi)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Portal Peserta');
        $response->assertSee('Alur Proses Sertifikasi Kompetensi (Phase-Gating)');
        $response->assertSee('Panduan Asesi');
        $response->assertSee('Unduh Panduan Sistem (PDF)');
    }
}
