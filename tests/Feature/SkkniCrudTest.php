<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\Skkni;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkkniCrudTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->institution = Institution::create([
            'name' => 'LSP SKKNI',
            'slug' => 'lsp-skkni',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);
        TenantContext::set($this->institution);

        $this->admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'aktif',
        ]);
        $this->admin->assignRole('admin');
        $this->admin->institutions()->attach($this->institution->id, ['role' => 'admin']);
    }

    public function test_admin_can_view_skkni_page(): void
    {
        $this->actingAs($this->admin)->get('/admin/stkkni')->assertStatus(200)->assertSee('SKKNI');
    }

    public function test_admin_can_create_skkni(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/stkkni', [
            'kode' => 'SKKNI-01',
            'judul' => 'Judul SKKNI',
            'nomor_penetapan' => 'Kepmen/2026',
            'tahun' => 2026,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.stkkni.index'));
        $this->assertDatabaseHas('skknis', ['kode' => 'SKKNI-01', 'institution_id' => $this->institution->id]);
    }

    public function test_skkni_is_tenant_isolated(): void
    {
        $other = Institution::create(['name' => 'LSP Lain', 'slug' => 'lsp-lain', 'is_active' => true]);

        TenantContext::set($other);
        Skkni::create([
            'kode' => 'SKKNI-X',
            'judul' => 'Lain',
            'tahun' => 2026,
            'is_active' => true,
        ]);
        TenantContext::set($this->institution);

        $this->actingAs($this->admin)->get('/admin/stkkni')->assertStatus(200)->assertDontSee('SKKNI-X');
    }
}
