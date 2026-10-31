<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMasterDataSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $institution = Institution::create([
            'name' => 'LSP Smoke',
            'slug' => 'lsp-smoke',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);
        TenantContext::set($institution);

        $admin = User::factory()->create([
            'institution_id' => $institution->id,
            'status' => 'aktif',
        ]);
        $admin->assignRole('admin');
        $admin->institutions()->attach($institution->id, ['role' => 'admin']);

        $this->actingAs($admin);
    }

    public function test_master_data_pages_render(): void
    {
        foreach ([
            '/admin/stkkni',
            '/admin/skema',
            '/admin/unit',
            '/admin/elemen',
            '/admin/tuk',
            '/admin/institution',
            '/admin/pelaporan',
            '/admin/pelaporan/apl02',
            '/admin/cms',
        ] as $uri) {
            $this->get($uri)->assertStatus(200);
        }
    }
}
