<?php

namespace Tests\Feature;

use App\Models\Asesi;
use App\Models\EventAsesmen;
use App\Models\Institution;
use App\Models\JadwalAsesmen;
use App\Models\Pendaftaran;
use App\Models\Pleno;
use App\Models\PlenoPeserta;
use App\Models\Sertifikat;
use App\Models\Skema;
use App\Models\Tuk;
use App\Models\User;
use App\Models\ValidasiAsesmen;
use App\Support\SignatureImage;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SertifikatPdfTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    private User $admin;

    private Skema $skema;

    private JadwalAsesmen $jadwal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->institution = Institution::create([
            'name' => 'LSP Sertifikat PDF',
            'slug' => 'lsp-sertifikat-pdf',
            'is_active' => true,
            'is_setup_completed' => true,
            'nomor_lisensi' => 'BNSP-LSP-0002-ID',
            'nama_ketua' => 'Ketua LSP Test',
            'logo_lsp_path' => null,
        ]);
        TenantContext::set($this->institution);

        $this->admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'aktif',
        ]);
        $this->admin->assignRole('admin');
        $this->admin->institutions()->attach($this->institution->id, ['role' => 'admin']);

        $this->skema = Skema::create([
            'kode_skema' => 'SKM-PDF',
            'nama_skema' => 'Skema PDF',
            'jenis' => 'okupasi',
            'biaya' => 500000,
            'is_active' => true,
        ]);
        $event = EventAsesmen::create(['nama' => 'Event PDF', 'tahun' => 2026]);
        $tuk = Tuk::create([
            'kode_tuk' => 'TUK-PDF',
            'nama_tuk' => 'TUK PDF',
            'jenis' => 'mandiri',
            'penanggung_jawab' => 'PJ',
        ]);
        $this->jadwal = JadwalAsesmen::create([
            'event_id' => $event->id,
            'skema_id' => $this->skema->id,
            'tuk_id' => $tuk->id,
            'tanggal' => now()->subDays(7)->toDateString(),
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
            'kuota' => 10,
            'status' => 'selesai',
        ]);

        $this->setInstitutionSignature();
    }

    public function test_certificate_pdf_is_generated_end_to_end(): void
    {
        $pendaftaran = $this->createKompetenPendaftaran();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.sertifikat.store'), [
                'pendaftaran_id' => $pendaftaran->id,
                'no_seri_blangko' => 'LSP.PDF.00001.2026',
            ]);

        $response->assertRedirect();
        $sertifikat = Sertifikat::where('pendaftaran_id', $pendaftaran->id)->firstOrFail();

        $pdfResponse = $this->actingAs($this->admin)->get(route('admin.sertifikat.pdf', $sertifikat));

        $pdfResponse->assertOk();
        $this->assertStringStartsWith('application/pdf', (string) $pdfResponse->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', (string) $pdfResponse->getContent());
    }

    private function createKompetenPendaftaran(): Pendaftaran
    {
        $asesiUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesi PDF',
            'status' => 'aktif',
        ]);
        $asesiUser->assignRole('asesi');
        $asesiUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        $asesi = Asesi::create([
            'user_id' => $asesiUser->id,
            'no_registrasi' => 'REG-PDF',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1995-01-01',
            'kebangsaan' => 'Indonesia',
            'alamat_rumah' => 'Jl. PDF',
            'pendidikan_terakhir' => 'S1',
            'status_kompetensi' => 'kompeten',
        ]);

        $pendaftaran = Pendaftaran::create([
            'no_pendaftaran' => 'PDN-PDF',
            'asesi_id' => $asesi->id,
            'skema_id' => $this->skema->id,
            'jadwal_id' => $this->jadwal->id,
            'status' => Pendaftaran::STATUS_SELESAI,
        ]);

        ValidasiAsesmen::create([
            'institution_id' => $this->institution->id,
            'pendaftaran_id' => $pendaftaran->id,
            'validator_id' => $this->admin->id,
            'keputusan' => ValidasiAsesmen::KEPUTUSAN_DISETUJUI,
            'status' => ValidasiAsesmen::STATUS_FINAL,
            'finalized_at' => now(),
            'finalized_by' => $this->admin->id,
        ]);

        $pleno = Pleno::create([
            'institution_id' => $this->institution->id,
            'jadwal_id' => $this->jadwal->id,
            'nomor_ba' => 'BA-PDF',
            'tanggal' => now()->toDateString(),
            'keputusan' => Pleno::KEPUTUSAN_DISETUJUI,
            'status' => Pleno::STATUS_FINAL,
            'finalized_at' => now(),
            'finalized_by' => $this->admin->id,
        ]);

        PlenoPeserta::create([
            'institution_id' => $this->institution->id,
            'pleno_id' => $pleno->id,
            'pendaftaran_id' => $pendaftaran->id,
            'keputusan' => PlenoPeserta::KEPUTUSAN_KOMPETEN,
        ]);

        return $pendaftaran;
    }

    private function setInstitutionSignature(): void
    {
        $image = imagecreatetruecolor(10, 10);
        ob_start();
        imagepng($image);
        $binary = ob_get_clean();
        imagedestroy($image);
        $payload = SignatureImage::encodePng($binary);

        $this->institution->forceFill([
            'tanda_tangan_data' => $payload['data'],
            'tanda_tangan_mime' => 'image/png',
            'tanda_tangan_hash' => $payload['hash'],
            'tanda_tangan_uploaded_at' => now(),
            'tanda_tangan_path' => null,
        ])->save();
    }
}
