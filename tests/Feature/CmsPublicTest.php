<?php

namespace Tests\Feature;

use App\Models\Asesi;
use App\Models\CmsContent;
use App\Models\Institution;
use App\Models\Pendaftaran;
use App\Models\Sertifikat;
use App\Models\Skema;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsPublicTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->institution = Institution::create([
            'name' => 'LSP Publik',
            'slug' => 'lsp-publik',
            'is_active' => true,
            'is_setup_completed' => true,
            'alamat' => 'Jl. Publik 1',
            'email' => 'info@lsp-publik.test',
            'telepon' => '022123456',
        ]);
        TenantContext::set($this->institution);

        $this->admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'aktif',
        ]);
        $this->admin->assignRole('admin');
        $this->admin->institutions()->attach($this->institution->id, ['role' => 'admin']);
    }

    public function test_root_shows_landing_for_guest(): void
    {
        $this->get('/')->assertOk()->assertSee('LSP Publik');
    }

    public function test_landing_renders_cms_sections(): void
    {
        CmsContent::create(['kategori' => 'welcome', 'judul' => 'Selamat Datang', 'isi' => '<p>Profil singkat LSP.</p>', 'is_active' => true]);
        CmsContent::create(['kategori' => 'layanan', 'judul' => 'Sertifikasi', 'isi' => '<p>Layanan sertifikasi.</p>', 'is_active' => true]);
        CmsContent::create(['kategori' => 'footer_menu', 'judul' => 'BNSP', 'isi' => 'https://bnsp.go.id', 'is_active' => true]);
        CmsContent::create(['kategori' => 'berita', 'judul' => 'Berita Terbaru', 'isi' => '<p>Isi berita.</p>', 'tanggal_terbit' => '2026-11-01', 'is_active' => true]);

        $response = $this->get('/beranda');

        $response->assertOk()
            ->assertSee('Selamat Datang')
            ->assertSee('Profil singkat LSP.', false)
            ->assertSee('Sertifikasi')
            ->assertSee('Berita Terbaru')
            ->assertSee('https://bnsp.go.id', false)
            ->assertSee('id="skema"', false)
            ->assertSee('id="jadwal"', false)
            ->assertSee('id="berita"', false)
            ->assertSee('id="kontak"', false);
    }

    public function test_inactive_cms_content_is_not_rendered(): void
    {
        CmsContent::create(['kategori' => 'welcome', 'judul' => 'Tersembunyi', 'isi' => '<p>Rahasia.</p>', 'is_active' => false]);

        $this->get('/beranda')->assertOk()->assertDontSee('Tersembunyi');
    }

    public function test_cms_content_is_sanitized_on_save(): void
    {
        CmsContent::create([
            'kategori' => 'berita',
            'judul' => 'XSS',
            'isi' => '<p>Aman</p><script>alert(1)</script><img src="x" onerror="alert(2)"><a href="javascript:alert(3)">Klik</a>',
            'is_active' => true,
        ]);

        $content = CmsContent::where('judul', 'XSS')->firstOrFail();

        $this->assertStringNotContainsString('<script', (string) $content->isi);
        $this->assertStringNotContainsString('onerror', (string) $content->isi);
        $this->assertStringNotContainsString('javascript:', (string) $content->isi);
        $this->assertStringContainsString('<p>Aman</p>', (string) $content->isi);

        $this->get('/beranda')
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('javascript:alert(3)', false);
    }

    public function test_admin_can_create_cms_content(): void
    {
        $this->actingAs($this->admin)->post(route('admin.cms.store'), [
            'kategori' => 'welcome',
            'judul' => 'Dari Admin',
            'isi' => '<p>Konten admin.</p>',
            'urutan' => 1,
            'is_active' => 1,
        ])->assertRedirect(route('admin.cms.index'));

        $this->assertDatabaseHas('cms_contents', ['judul' => 'Dari Admin', 'kategori' => 'welcome']);
        $this->get('/beranda')->assertSee('Dari Admin');
    }

    public function test_certificate_lookup_by_registration_number(): void
    {
        $asesi = $this->makeAsesi();
        $skema = Skema::create(['kode_skema' => 'SKM-PUB', 'nama_skema' => 'Skema Publik', 'jenis' => 'okupasi', 'biaya' => 0, 'is_active' => true]);
        $pendaftaran = Pendaftaran::create(['no_pendaftaran' => 'PEND-PUB-1', 'asesi_id' => $asesi->id, 'skema_id' => $skema->id, 'status' => Pendaftaran::STATUS_SELESAI]);
        $sertifikat = Sertifikat::create([
            'pendaftaran_id' => $pendaftaran->id,
            'asesi_id' => $asesi->id,
            'skema_id' => $skema->id,
            'no_seri_blangko' => 'BLG-001',
            'no_registrasi' => 'REG-001',
            'tanggal_terbit' => '2026-11-01',
            'status' => Sertifikat::STATUS_AKTIF,
        ]);

        $this->post(route('sertifikat.lookup'), ['nomor' => 'REG-001'])
            ->assertRedirect(route('sertifikat.verifikasi', $sertifikat->qr_token));

        $this->post(route('sertifikat.lookup'), ['nomor' => 'TIDAK-ADA'])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_verification_form_renders(): void
    {
        $this->get(route('sertifikat.form'))->assertOk()->assertSee('Verifikasi Keaslian Sertifikat');
    }

    private function makeAsesi(): Asesi
    {
        $user = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesi Publik',
            'email' => 'asesi.publik@example.test',
            'nik' => '3201123456789911',
            'phone' => '081234567899',
            'status' => 'aktif',
        ]);
        $user->assignRole('asesi');
        $user->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        return Asesi::create([
            'user_id' => $user->id,
            'no_registrasi' => '202611059999',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2000-01-01',
            'kebangsaan' => 'Indonesia',
            'alamat_rumah' => 'Jl. Publik',
            'pendidikan_terakhir' => 'D3',
            'status_kompetensi' => 'kompeten',
        ]);
    }
}
