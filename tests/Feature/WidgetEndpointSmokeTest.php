<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WidgetEndpointSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $institution = Institution::create(['name' => 'LSP W', 'slug' => 'lsp-w', 'is_active' => true, 'is_setup_completed' => true]);
        TenantContext::set($institution);

        $admin = User::factory()->create(['institution_id' => $institution->id, 'status' => 'aktif']);
        $admin->assignRole('admin');
        $admin->institutions()->attach($institution->id, ['role' => 'admin']);

        $this->actingAs($admin)->withSession([TenantContext::SESSION_ID => $institution->id]);
        $this->admin = $admin;
    }

    private User $admin;

    public function test_widget_endpoints_return_json(): void
    {
        foreach (['stat_ringkas', 'pendaftaran_tren', 'asesi_usia', 'asesi_gender', 'pendaftaran_funnel', 'hasil_per_skema', 'jadwal_keterisian', 'asesi_wilayah', 'antrean_tindakan', 'jadwal_7_hari'] as $key) {
            $this->get("/dashboard/widgets/{$key}")->assertOk()->assertJsonStructure(['key', 'type', 'title', 'meta']);
        }
    }
}
