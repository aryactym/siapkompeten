<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RbacAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'asesor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'asesi', 'guard_name' => 'web']);

        Route::middleware(['web', 'role:admin'])->get('/test-admin-only', function () {
            return 'admin-ok';
        });

        Route::middleware(['web', 'role:asesor'])->get('/test-asesor-only', function () {
            return 'asesor-ok';
        });
    }

    public function test_guest_is_redirected_to_login_when_accessing_role_protected_route(): void
    {
        $response = $this->get('/test-admin-only');
        $response->assertRedirect('/login');
    }

    public function test_user_without_required_role_receives_403_forbidden(): void
    {
        $institution = Institution::create([
            'name' => 'LSP RBAC',
            'slug' => 'lsp-rbac',
            'is_active' => true,
        ]);

        $asesi = User::factory()->create(['institution_id' => $institution->id]);
        $asesi->institutions()->attach($institution->id, ['role' => 'asesi']);
        $asesi->assignRole('asesi');

        $response = $this->actingAs($asesi)->get('/test-admin-only');
        $response->assertForbidden();
    }

    public function test_user_with_required_role_can_access(): void
    {
        $institution = Institution::create([
            'name' => 'LSP RBAC 2',
            'slug' => 'lsp-rbac-2',
            'is_active' => true,
        ]);

        $admin = User::factory()->create(['institution_id' => $institution->id]);
        $admin->institutions()->attach($institution->id, ['role' => 'admin']);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/test-admin-only');
        $response->assertOk();
        $response->assertSee('admin-ok');
    }

    public function test_super_admin_can_access_any_role_route(): void
    {
        $superAdmin = User::factory()->create(['institution_id' => null]);
        $superAdmin->assignRole('super-admin');

        $response = $this->actingAs($superAdmin)->get('/test-asesor-only');
        $response->assertOk();
        $response->assertSee('asesor-ok');
    }
}
