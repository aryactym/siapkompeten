<?php

namespace Tests\Feature;

use App\Models\Asesi;
use App\Models\Asesor;
use App\Models\EventAsesmen;
use App\Models\Institution;
use App\Models\JadwalAsesmen;
use App\Models\JadwalAsesor;
use App\Models\Pendaftaran;
use App\Models\Skema;
use App\Models\Tuk;
use App\Models\User;
use App\Support\PdfFile;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Label dan penandatangan formulir PDF sesuai katalog BNSP (Fase 0).
 */
class PdfFormulirTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    private User $admin;

    private User $asesiUser;

    private User $asesorUser;

    private Pendaftaran $pendaftaran;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->institution = Institution::create([
            'name' => 'LSP Formulir PDF',
            'slug' => 'lsp-formulir-pdf',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);
        TenantContext::set($this->institution);

        $this->admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Admin Pencetak',
            'status' => 'aktif',
        ]);
        $this->admin->assignRole('admin');
        $this->admin->institutions()->attach($this->institution->id, ['role' => 'admin']);

        $this->asesiUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Peserta Formulir',
            'status' => 'aktif',
        ]);
        $this->asesiUser->assignRole('asesi');
        $this->asesiUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        $this->asesorUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesor Penilai',
            'status' => 'aktif',
        ]);
        $this->asesorUser->assignRole('asesor');
        $this->asesorUser->institutions()->attach($this->institution->id, ['role' => 'asesor']);

        $asesi = Asesi::create([
            'user_id' => $this->asesiUser->id,
            'no_registrasi' => 'REG-FORMULIR',
            'jenis_kelamin' => 'L',
            'pendidikan_terakhir' => 'SMA',
        ]);

        $asesor = Asesor::create([
            'institution_id' => $this->institution->id,
            'user_id' => $this->asesorUser->id,
            'no_register' => 'ASR-FORMULIR',
        ]);

        $skema = Skema::create([
            'kode_skema' => 'SKM-FORMULIR',
            'nama_skema' => 'Skema Formulir',
            'jenis' => 'okupasi',
        ]);
        $tuk = Tuk::create([
            'kode_tuk' => 'TUK-FORMULIR',
            'nama_tuk' => 'TUK Formulir',
            'jenis' => 'mandiri',
            'penanggung_jawab' => 'PJ',
        ]);
        $event = EventAsesmen::create(['nama' => 'Event Formulir', 'tahun' => 2026]);
        $jadwal = JadwalAsesmen::create([
            'event_id' => $event->id,
            'skema_id' => $skema->id,
            'tuk_id' => $tuk->id,
            'tanggal' => '2026-12-06',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
            'kuota' => 10,
            'status' => 'terbuka',
        ]);

        JadwalAsesor::create([
            'institution_id' => $this->institution->id,
            'jadwal_id' => $jadwal->id,
            'asesor_id' => $asesor->id,
            'peran' => 'utama',
        ]);

        Pendaftaran::create([
            'no_pendaftaran' => 'REG-FORMULIR',
            'asesi_id' => $asesi->id,
            'skema_id' => $skema->id,
            'jadwal_id' => $jadwal->id,
            'status' => 'terjadwal',
        ]);

        $this->pendaftaran = Pendaftaran::firstOrFail();
    }

    public function test_mak01_renders_official_fr_ak_01_template(): void
    {
        $pendaftaran = $this->pendaftaran->load([
            'asesi.user',
            'skema',
            'jadwal.tuk',
            'jadwal.asesors.user',
            'persetujuanAsesmens',
        ]);

        $this->view('pdf.mak01', [
            'pendaftaran' => $pendaftaran,
            'institution' => $this->institution,
            'skema' => $pendaftaran->skema,
            'jadwal' => $pendaftaran->jadwal,
            'asesiUser' => $pendaftaran->asesi?->user,
            'persetujuan' => null,
            'data' => [],
        ])
            ->assertSee('FR.AK.01.')
            ->assertSee('PERSETUJUAN ASESMEN DAN KERAHASIAAN')
            ->assertSee('Bukti yang akan dikumpulkan')
            ->assertDontSee('FR.IA.08');
    }

    public function test_mak03_renders_official_fr_ak_04_template(): void
    {
        $pendaftaran = $this->pendaftaran->load([
            'asesi.user',
            'skema',
            'jadwal.tuk',
            'jadwal.asesors.user',
            'persetujuanAsesmens',
        ]);

        $this->view('pdf.mak03', [
            'pendaftaran' => $pendaftaran,
            'institution' => $this->institution,
            'skema' => $pendaftaran->skema,
            'jadwal' => $pendaftaran->jadwal,
            'asesiUser' => $pendaftaran->asesi?->user,
            'persetujuan' => null,
            'data' => [],
        ])
            ->assertSee('FR.AK.04.')
            ->assertSee('BANDING ASESMEN')
            ->assertSee('Apakah Proses Banding telah dijelaskan')
            ->assertDontSee('Berita Acara Asesmen');
    }

    public function test_apl01_uses_fr_apl_01_label_and_explicit_admin_signer(): void
    {
        $pendaftaran = $this->pendaftaran->load([
            'asesi.user',
            'asesi.province',
            'asesi.city',
            'skema',
            'jadwal.tuk',
        ]);

        $this->view('pdf.apl01', [
            'pendaftaran' => $pendaftaran,
            'institution' => $this->institution,
            'adminLsp' => $this->admin,
        ])
            ->assertSee('FR.APL.01')
            ->assertDontSee('Formulir APL-01')
            ->assertSee('Admin LSP')
            ->assertSee('Admin Pencetak');
    }

    public function test_apl01_and_mak01_stream_pdf(): void
    {
        foreach (['admin.pendaftaran.apl01', 'admin.pendaftaran.mak01', 'admin.pendaftaran.mak03'] as $routeName) {
            $response = $this->actingAs($this->admin)
                ->withSession([TenantContext::SESSION_ID => $this->institution->id])
                ->get(route($routeName, $this->pendaftaran));

            $response->assertOk();
            $this->assertStringStartsWith('application/pdf', (string) $response->headers->get('content-type'));
            $this->assertStringStartsWith('%PDF', (string) $response->getContent());
        }
    }

    public function test_pdf_file_helper_returns_null_for_empty_or_missing_signature(): void
    {
        $this->assertNull(PdfFile::path(null));
        $this->assertNull(PdfFile::path(''));
        $this->assertNull(PdfFile::path('tanda-tangan/tidak-ada.png'));
    }
}
