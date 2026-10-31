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
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfExportTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    private User $admin;

    private JadwalAsesmen $jadwal;

    private JadwalAsesor $penugasan;

    private User $asesiUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->institution = Institution::create([
            'name' => 'LSP PDF',
            'slug' => 'lsp-pdf',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);

        TenantContext::set($this->institution);

        $this->admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'aktif',
        ]);
        $this->admin->assignRole('admin');
        $this->admin->institutions()->attach($this->institution->id, ['role' => 'admin']);

        $event = EventAsesmen::create(['nama' => 'Event PDF', 'tahun' => 2026]);

        $skema = Skema::create([
            'kode_skema' => 'SKM-PDF',
            'nama_skema' => 'Skema PDF',
            'jenis' => 'okupasi',
        ]);

        $tuk = Tuk::create([
            'kode_tuk' => 'TUK-PDF',
            'nama_tuk' => 'TUK PDF',
            'jenis' => 'mandiri',
            'penanggung_jawab' => 'PJ',
        ]);

        $asesorUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesor PDF',
            'status' => 'aktif',
        ]);
        $asesorUser->assignRole('asesor');

        $asesor = Asesor::create([
            'user_id' => $asesorUser->id,
            'no_register' => 'MET.000.000088 2026',
            'status' => 'aktif',
        ]);

        $this->asesiUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Peserta PDF',
            'status' => 'aktif',
        ]);
        $this->asesiUser->assignRole('asesi');
        $this->asesiUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        $asesi = Asesi::create([
            'user_id' => $this->asesiUser->id,
            'no_registrasi' => '202610310777',
        ]);

        $this->jadwal = JadwalAsesmen::create([
            'event_id' => $event->id,
            'skema_id' => $skema->id,
            'tuk_id' => $tuk->id,
            'tanggal' => '2026-12-05',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
            'kuota' => 10,
            'lingkup_asesor' => 'wilayah',
            'status' => 'terbuka',
        ]);

        $this->penugasan = JadwalAsesor::create([
            'jadwal_id' => $this->jadwal->id,
            'asesor_id' => $asesor->id,
            'peran' => 'utama',
        ]);

        Pendaftaran::create([
            'no_pendaftaran' => '202610310777',
            'asesi_id' => $asesi->id,
            'skema_id' => $skema->id,
            'jadwal_id' => $this->jadwal->id,
            'status' => Pendaftaran::STATUS_TERJADWAL,
        ]);
    }

    public function test_presensi_is_streamed_as_pdf(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession([TenantContext::SESSION_ID => $this->institution->id])
            ->get(route('admin.jadwal.presensi', $this->jadwal));

        $response->assertOk();
        $this->assertStringStartsWith('application/pdf', (string) $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', (string) $response->getContent());
    }

    public function test_surat_tugas_is_streamed_as_pdf(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession([TenantContext::SESSION_ID => $this->institution->id])
            ->get(route('admin.jadwal.surat-tugas', [$this->jadwal, $this->penugasan]));

        $response->assertOk();
        $this->assertStringStartsWith('application/pdf', (string) $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', (string) $response->getContent());
    }

    public function test_asesi_cannot_download_presensi(): void
    {
        $this->actingAs($this->asesiUser)
            ->withSession([TenantContext::SESSION_ID => $this->institution->id])
            ->get(route('admin.jadwal.presensi', $this->jadwal))
            ->assertForbidden();
    }
}
