<?php

namespace Tests\Feature;

use App\Models\Asesi;
use App\Models\EventAsesmen;
use App\Models\Institution;
use App\Models\JadwalAsesmen;
use App\Models\Pendaftaran;
use App\Models\Skema;
use App\Models\Tuk;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Apl01PdfSmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private Institution $institution;

    private Pendaftaran $pendaftaran;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $institution = Institution::create(['name' => 'LSP APL01', 'slug' => 'lsp-apl01', 'is_active' => true, 'is_setup_completed' => true]);
        TenantContext::set($institution);

        $admin = User::factory()->create(['institution_id' => $institution->id, 'status' => 'aktif']);
        $admin->assignRole('admin');
        $admin->institutions()->attach($institution->id, ['role' => 'admin']);

        $asesiUser = User::factory()->create(['institution_id' => $institution->id, 'name' => 'Peserta APL', 'status' => 'aktif']);
        $asesiUser->assignRole('asesi');
        $asesiUser->institutions()->attach($institution->id, ['role' => 'asesi']);

        $asesi = Asesi::create(['user_id' => $asesiUser->id, 'no_registrasi' => 'REG-APL01', 'jenis_kelamin' => 'L', 'pendidikan_terakhir' => 'SMA']);

        $skema = Skema::create(['kode_skema' => 'SKM-APL', 'nama_skema' => 'Skema APL', 'jenis' => 'okupasi']);
        $tuk = Tuk::create(['kode_tuk' => 'TUK-APL', 'nama_tuk' => 'TUK APL', 'jenis' => 'mandiri', 'penanggung_jawab' => 'PJ']);
        $event = EventAsesmen::create(['nama' => 'Event APL', 'tahun' => 2026]);
        $jadwal = JadwalAsesmen::create(['event_id' => $event->id, 'skema_id' => $skema->id, 'tuk_id' => $tuk->id, 'tanggal' => '2026-12-05', 'jam_mulai' => '08:00', 'jam_selesai' => '12:00', 'kuota' => 10, 'status' => 'terbuka']);

        Pendaftaran::create(['no_pendaftaran' => 'REG-APL01', 'asesi_id' => $asesi->id, 'skema_id' => $skema->id, 'jadwal_id' => $jadwal->id, 'status' => 'terjadwal']);

        $this->adminUser = $admin;
        $this->institution = $institution;
        $this->pendaftaran = Pendaftaran::first();
    }

    public function test_apl01_streams_pdf(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->withSession([TenantContext::SESSION_ID => $this->institution->id])
            ->get(route('admin.pendaftaran.apl01', $this->pendaftaran));

        $response->assertOk();
        $this->assertStringStartsWith('application/pdf', (string) $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', (string) $response->getContent());
    }
}
