<?php

namespace Tests\Browser;

use App\Models\CmsContent;
use App\Models\Institution;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Landing publik yang dirender dari CMS dan form verifikasi sertifikat.
 */
class CmsPublicTest extends DuskTestCase
{
    use DatabaseMigrations;

    private Institution $institution;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->institution = Institution::create([
            'name' => 'LSP Dusk Publik',
            'slug' => 'lsp-dusk-publik',
            'is_active' => true,
            'is_setup_completed' => true,
            'alamat' => 'Jl. Dusk 1',
        ]);

        TenantContext::set($this->institution);

        CmsContent::create([
            'kategori' => 'welcome',
            'judul' => 'Selamat Datang Dusk',
            'isi' => '<p>Profil publik.</p>',
            'is_active' => true,
        ]);

        CmsContent::create([
            'kategori' => 'footer_menu',
            'judul' => 'BNSP Dusk',
            'isi' => 'https://bnsp.go.id',
            'is_active' => true,
        ]);
    }

    public function test_public_landing_renders_cms_content(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/')
                ->assertSee('LSP Dusk Publik')
                ->assertSee('Selamat Datang Dusk')
                ->assertSee('Skema Sertifikasi')
                ->assertSee('Verifikasi Sertifikat')
                ->assertSee('BNSP Dusk')
                ->assertSee('Tempat Uji Kompetensi')
                ->assertSee('Hubungi Kami');
        });
    }

    public function test_certificate_verification_form_is_accessible(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/')
                ->clickLink('Verifikasi Sertifikat')
                ->assertPathIs('/verifikasi-sertifikat')
                ->assertSee('Verifikasi Keaslian Sertifikat')
                ->assertSee('Nomor Registrasi / Nomor Seri Blangko');
        });
    }
}
