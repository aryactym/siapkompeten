<?php

namespace Tests\Browser;

use App\Models\Institution;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Navigasi sidebar bersama untuk portal asesor dan asesi.
 *
 * Memastikan kedua portal memakai struktur sidebar vertikal Tabler yang sama
 * dengan portal admin (komponen x-portal-sidebar + layouts/portal).
 */
class NavigasiPortalSidebarTest extends DuskTestCase
{
    use DatabaseMigrations;

    private Institution $institution;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->institution = Institution::create([
            'name' => 'LSP Dusk Sidebar Portal',
            'slug' => 'lsp-dusk-sidebar-portal',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);

        TenantContext::set($this->institution);
    }

    public function test_asesor_portal_uses_shared_vertical_sidebar(): void
    {
        $asesor = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesor Sidebar',
            'username' => 'asesor.sidebar',
            'email' => 'asesor.sidebar@example.test',
            'password' => 'password',
            'status' => 'aktif',
        ]);
        $asesor->assignRole('asesor');
        $asesor->institutions()->attach($this->institution->id, ['role' => 'asesor']);

        $this->browse(function (Browser $browser): void {
            $browser->visit('/login')
                ->type('login', 'asesor.sidebar@example.test')
                ->type('password', 'password')
                ->press('Masuk Sekarang')
                ->waitForLocation('/dashboard')
                ->assertVisible('#sidebar-menu')
                ->assertSee('Portal Asesor')
                ->assertSee('Jadwal Uji Saya')
                ->assertSee('Pra-Asesmen (APL-02)')
                ->assertSee('Hasil Asesmen (FR.AK.02)')
                ->assertDontSee('Master Data');

            $browser->clickLink('Jadwal Uji Saya')
                ->waitForLocation('/asesor/jadwal')
                ->assertSee('Jadwal Uji Saya');
        });
    }

    public function test_asesi_portal_uses_shared_vertical_sidebar(): void
    {
        $asesi = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesi Sidebar',
            'username' => 'asesi.sidebar',
            'email' => 'asesi.sidebar@example.test',
            'password' => 'password',
            'status' => 'aktif',
        ]);
        $asesi->assignRole('asesi');
        $asesi->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        $this->browse(function (Browser $browser): void {
            $browser->visit('/login')
                ->type('login', 'asesi.sidebar@example.test')
                ->type('password', 'password')
                ->press('Masuk Sekarang')
                ->waitForLocation('/dashboard')
                ->assertVisible('#sidebar-menu')
                ->assertSee('Portal Peserta')
                ->assertSee('Status Pendaftaran')
                ->assertSee('Asesmen Mandiri (APL-02)')
                ->assertDontSee('Validasi Pembayaran');

            $browser->clickLink('Status Pendaftaran')
                ->waitForLocation('/asesi/pendaftaran')
                ->assertSee('Status Pendaftaran');
        });
    }
}
