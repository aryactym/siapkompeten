<?php

namespace Tests\Browser;

use App\Models\Institution;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class NavigasiAdminTest extends DuskTestCase
{
    use DatabaseMigrations;

    private Institution $institution;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->institution = Institution::create([
            'name' => 'LSP Dusk Navigasi',
            'slug' => 'lsp-dusk-navigasi',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);

        TenantContext::set($this->institution);

        $this->admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Admin Navigasi',
            'username' => 'admin.navigasi',
            'email' => 'admin.navigasi@example.test',
            'password' => 'password',
            'status' => 'aktif',
        ]);
        $this->admin->assignRole('admin');
        $this->admin->institutions()->attach($this->institution->id, ['role' => 'admin']);
    }

    public function test_sidebar_navigation_and_logout_work_for_admin(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/login')
                ->type('login', 'admin.navigasi@example.test')
                ->type('password', 'password')
                ->press('Masuk')
            ->waitForLocation('/dashboard')
                ->waitForLocation('/dashboard')
                ->assertSee($this->institution->name)
                ->assertVisible('#sidebar-menu')
                ->assertSee('Master Standar')
                ->assertSee('Asesi & Pendaftaran');

            // Buka submenu Asesi & Pendaftaran lalu masuk ke halaman impor.
            $browser->click('a.dropdown-toggle[href="#sidebar-standar"]')
                ->waitForText('Skema Sertifikasi')
                ->clickLink('Skema Sertifikasi')
                ->waitForLocation('/admin/skema')
                ->assertSee('Skema Sertifikasi')
                ->clickLink('Persyaratan Skema')
                ->waitForLocation('/admin/skema/' . $skema->id . '/persyaratan')
                ->assertSee('Persyaratan');

            $browser->clickLink('Unit Kompetensi')
                ->waitForLocation('/admin/unit')
                ->assertSee('Unit Kompetensi');

            $browser->clickLink('Pemetaan Unit (N:M)')
                ->waitForLocation('/admin/unit-mappings')
                ->assertSee('Pemetaan Unit');

            $browser->clickLink('Elemen & KUK')
                ->waitForLocation('/admin/elemen')
                ->assertSee('Elemen');

            $browser->clickLink('SKKNI')
                ->waitForLocation('/admin/stkkni')
                ->assertSee('SKKNI');

            $browser->clickLink('TUK')
                ->waitForLocation('/admin/tuk')
                ->assertSee('TUK');

            $browser->clickLink('Data LSP & Licensi')
                ->waitForLocation('/admin/institution')
                ->assertSee('LSP');

            $browser->clickLink('Data Wilayah')
                ->waitForLocation('/admin/wilayah')
                ->assertSee('Wilayah');

            $browser->screenshot('navigasi-admin-master');

            // Logout dari menu pengguna.
            $browser->click('a.dropdown-toggle[href="#sidebar-komunikasi"]')
                ->waitForText('Arsip Notifikasi Email')
                ->clickLink('Arsip Notifikasi Email')
                ->waitForLocation('/admin/notifikasi')
                ->assertSee('Notifikasi');

            $browser->clickLink('Biaya Skema')
                ->waitForLocation('/admin/biaya')
                ->assertSee('Biaya');

            $browser->clickLink('Rekening Bank LSP')
                ->waitForLocation('/admin/rekening-bank')
                ->assertSee('Rekening');

            $browser->screenshot('navigasi-admin-kelembagaan');

            $browser->click('a.dropdown-toggle[href="#sidebar-komunikasi"]')
                ->waitForText('Arsip Notifikasi Email')
                ->clickLink('Arsip Notifikasi Email')
                ->waitForLocation('/admin/notifikasi')
                ->assertSee('Notifikasi');

            $browser->screenshot('navigasi-admin-notifikasi');

            $browser->click('a.dropdown-toggle[href="#sidebar-jadwal"]')
                ->waitForText('Jadwal Uji & Multi-Asesor')
                ->clickLink('Jadwal Uji & Multi-Asesor')
                ->waitForLocation('/admin/jadwal')
                ->assertSee('Jadwal');

            $browser->clickLink('Daftar Asesor')
                ->waitForLocation('/admin/asesor')
                ->assertSee('Asesor');

            $browser->clickLink('Surat Tugas Asesor')
                ->waitForLocation('/admin/jadwal')
                ->assertSee('Jadwal');

            $browser->screenshot('navigasi-admin-jadwal');

            $browser->click('a.dropdown-toggle[href="#sidebar-asesi"]')
                ->waitForText('Verifikasi Dokumen')
                ->clickLink('Verifikasi Dokumen')
                ->waitForLocation('/admin/pendaftaran')
                ->assertSee('Pendaftaran');

            $browser->clickLink('Plotting Jadwal')
                ->waitForLocation('/admin/pendaftaran?tab=siap_plotting')
                ->assertSee('Plotting');

            $browser->clickLink('Validasi Pembayaran')
                ->waitForLocation('/admin/pembayaran')
                ->assertSee('Pembayaran');

            $browser->screenshot('navigasi-admin-asesi');

            $browser->click('a.dropdown-toggle[href="#sidebar-asesmen"]')
                ->waitForText('Monitoring APL-02')
                ->clickLink('Monitoring APL-02')
                ->waitForLocation('/admin/notifikasi')
                ->assertSee('Notifikasi');

            $browser->clickLink('Hasil Uji (MAK-02)')
                ->waitForLocation('/admin/notifikasi')
                ->assertSee('Notifikasi');

            $browser->clickLink('Sertifikat BNSP (Blangko/Reg)')
                ->waitForLocation('/admin/notifikasi')
                ->assertSee('Notifikasi');

            $browser->clickLink('Laporan & Rekapitulasi')
                ->waitForLocation('/admin/notifikasi')
                ->assertSee('Notifikasi');

            $browser->screenshot('navigasi-admin-asesmen');

            $browser->click('a.dropdown-toggle[href="#sidebar-komunikasi"]')
                ->waitForText('Konten Frontpage (CMS)')
                ->clickLink('Konten Frontpage (CMS)')
                ->waitForLocation('/admin/cms/konten')
                ->assertSee('CMS');

            $browser->screenshot('navigasi-admin-komunikasi');

            $browser->clickLink('Arsip Notifikasi Email')
                ->waitForLocation('/admin/notifikasi')
                ->assertSee('Notifikasi');

            // Logout dari menu pengguna.
            // Logout dari menu pengguna.
            $browser->click('a.dropdown-toggle[href="#sidebar-komunikasi"]')
                ->waitForText('Arsip Notifikasi Email')
                ->clickLink('Arsip Notifikasi Email')
                ->waitForLocation('/admin/notifikasi')
                ->assertSee('Notifikasi');

            $browser->clickLink('Biaya Skema')
                ->waitForLocation('/admin/biaya')
                ->assertSee('Biaya');

            $browser->clickLink('Rekening Bank LSP')
                ->waitForLocation('/admin/rekening-bank')
                ->assertSee('Rekening');

            $browser->screenshot('navigasi-admin-kelembagaan');


            // Logout dari menu pengguna.
            $browser->click('a[aria-label="Open user menu"]')
                ->waitForText('Keluar')
                ->press('Keluar')
                ->waitForLocation('/login')
                ->assertSee('Masuk');
        });
    }
}
