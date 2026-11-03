<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarMenuTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->institution = Institution::create([
            'name' => 'LSP Sidebar Menu',
            'slug' => 'lsp-sidebar-menu',
            'is_active' => true,
        ]);
    }

    private function admin(): User
    {
        $admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'aktif',
        ]);
        $admin->assignRole('admin');
        $admin->institutions()->attach($this->institution->id, ['role' => 'admin']);

        return $admin;
    }

    public function test_sidebar_links_to_implemented_admin_features(): void
    {
        $response = $this->actingAs($this->admin())->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Biaya & Pembayaran');
        $response->assertSee('/admin/biaya"', false);
        $response->assertSee('/admin/rekening-bank"', false);
        $response->assertSee('/admin/pembayaran"', false);
        $response->assertSee('/admin/notifikasi/keluar"', false);
        $response->assertSee('/admin/jadwal"', false);
        $response->assertSee('/admin/event"', false);
    }

    public function test_sidebar_disables_unbuilt_menu_items_instead_of_dead_links(): void
    {
        $response = $this->actingAs($this->admin())->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Segera');
        $response->assertSee('aria-disabled="true"', false);

        // Halaman masih stub (501) atau routenya belum ada: tidak boleh ditautkan.
    }

    public function test_sidebar_expands_group_containing_active_page(): void
    {
        $admin = $this->admin();

        $onDashboard = $this->actingAs($admin)->get('/dashboard');
        $onDashboard->assertStatus(200);
        $onDashboard->assertDontSee('dropdown-menu show', false);

        $onJadwal = $this->actingAs($admin)->get('/admin/jadwal');
        $onJadwal->assertStatus(200);
        $onJadwal->assertSee('dropdown-menu show', false);
        $onJadwal->assertSee('aria-expanded="true"', false);
    }

    public function test_sidebar_follows_tabler_vertical_layout_structure(): void
    {
        $response = $this->actingAs($this->admin())->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('data-bs-navbar-position="vertical"', false);
        $response->assertSee('navbar navbar-vertical navbar-expand-lg', false);
        $response->assertSee('nav-section-title', false);
        $response->assertSee('Ringkasan');
        $response->assertSee('Sertifikasi');
        $response->assertSee('Keuangan');
        $response->assertSee('Master Data');
        $response->assertSee('Sistem');
    }

    public function test_asesor_portal_uses_same_vertical_sidebar_with_asesor_menu(): void
    {
        $asesor = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'aktif',
        ]);
        $asesor->assignRole('asesor');
        $asesor->institutions()->attach($this->institution->id, ['role' => 'asesor']);

        $response = $this->actingAs($asesor)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('data-bs-navbar-position="vertical"', false);
        $response->assertSee('id="sidebar-menu"', false);
        $response->assertSee('Portal Asesor');
        $response->assertSee('Jadwal Uji Saya');
        $response->assertSee('/asesor/jadwal"', false);
        $response->assertSee('Pra-Asesmen (APL-02)');
        $response->assertSee('Hasil Asesmen (FR.AK.02)');
        $response->assertSee('Tanda Tangan');
        $response->assertDontSee('Master Data');
    }

    public function test_asesi_portal_uses_same_vertical_sidebar_with_asesi_menu(): void
    {
        $asesi = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'aktif',
        ]);
        $asesi->assignRole('asesi');
        $asesi->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        $response = $this->actingAs($asesi)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('data-bs-navbar-position="vertical"', false);
        $response->assertSee('id="sidebar-menu"', false);
        $response->assertSee('Portal Peserta');
        $response->assertSee('Status Pendaftaran');
        $response->assertSee('/asesi/pendaftaran"', false);
        $response->assertSee('Hasil &amp; Sertifikat', false);
        $response->assertDontSee('Validasi Pembayaran');
    }
}
