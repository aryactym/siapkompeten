<?php

namespace Tests\Feature;

use App\Models\Asesi;
use App\Models\Asesor;
use App\Models\EventAsesmen;
use App\Models\Institution;
use App\Models\JadwalAsesmen;
use App\Models\JadwalAsesor;
use App\Models\Pendaftaran;
use App\Models\PersetujuanAsesmen;
use App\Models\PresensiAsesi;
use App\Models\Skema;
use App\Models\Tuk;
use App\Models\User;
use App\Services\PresensiService;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresensiAsesmenTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    private User $admin;

    private User $asesiUser;

    private Asesi $asesi;

    private User $asesorUser;

    private Asesor $asesor;

    private Pendaftaran $pendaftaran;

    private JadwalAsesmen $jadwal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->institution = Institution::create([
            'name' => 'LSP Presensi',
            'slug' => 'lsp-presensi',
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

        $this->asesiUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesi Presensi',
            'email' => 'asesi.presensi@example.test',
            'nik' => '3201123456780033',
            'phone' => '081234567833',
            'status' => 'aktif',
        ]);
        $this->asesiUser->assignRole('asesi');
        $this->asesiUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        $this->asesi = Asesi::create([
            'user_id' => $this->asesiUser->id,
            'no_registrasi' => '202611050033',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2000-01-01',
            'kebangsaan' => 'Indonesia',
            'alamat_rumah' => 'Jl. Presensi No. 1',
            'pendidikan_terakhir' => 'D3',
            'status_kompetensi' => 'belum_asesmen',
        ]);

        $this->asesorUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesor Presensi',
            'email' => 'asesor.presensi@example.test',
            'status' => 'aktif',
        ]);
        $this->asesorUser->assignRole('asesor');
        $this->asesorUser->institutions()->attach($this->institution->id, ['role' => 'asesor']);

        $this->asesor = Asesor::create([
            'user_id' => $this->asesorUser->id,
            'no_register' => 'MET.000.000022 2026',
            'status' => 'aktif',
            'kebangsaan' => 'Indonesia',
        ]);

        $skema = Skema::create([
            'kode_skema' => 'SKM-PRS',
            'nama_skema' => 'Skema Presensi',
            'jenis' => 'okupasi',
            'biaya' => 0,
            'is_active' => true,
        ]);

        $event = EventAsesmen::create(['nama' => 'Event Presensi', 'tahun' => 2026]);
        $tuk = Tuk::create([
            'kode_tuk' => 'TUK-PRS',
            'nama_tuk' => 'TUK Presensi',
            'jenis' => 'mandiri',
            'penanggung_jawab' => 'PJ',
        ]);

        $this->jadwal = JadwalAsesmen::create([
            'event_id' => $event->id,
            'skema_id' => $skema->id,
            'tuk_id' => $tuk->id,
            'tanggal' => '2026-12-10',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
            'kuota' => 10,
            'lingkup_asesor' => 'wilayah',
            'status' => 'terbuka',
        ]);

        JadwalAsesor::create([
            'jadwal_id' => $this->jadwal->id,
            'asesor_id' => $this->asesor->id,
            'peran' => 'utama',
        ]);

        $this->pendaftaran = Pendaftaran::create([
            'no_pendaftaran' => 'PEND-PRS-0001',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $skema->id,
            'jadwal_id' => $this->jadwal->id,
            'status' => Pendaftaran::STATUS_TERJADWAL,
        ]);

        $this->setApprovalStatus(PersetujuanAsesmen::STATUS_DISETUJUI);
    }

    private function setApprovalStatus(string $status): void
    {
        foreach (PersetujuanAsesmen::requiredJenis() as $jenis) {
            PersetujuanAsesmen::updateOrCreate(
                ['pendaftaran_id' => $this->pendaftaran->id, 'jenis' => $jenis],
                [
                    'institution_id' => $this->institution->id,
                    'status' => $status,
                    'disetujui_pada' => $status === PersetujuanAsesmen::STATUS_DISETUJUI ? now() : null,
                    'disetujui_oleh' => $status === PersetujuanAsesmen::STATUS_DISETUJUI ? $this->asesiUser->id : null,
                ],
            );
        }
    }

    public function test_asesor_can_record_presensi_for_assigned_peserta(): void
    {
        $response = $this->actingAs($this->asesorUser)
            ->post(route('asesor.presensi.catat', $this->pendaftaran), [
                'status' => PresensiAsesi::STATUS_HADIR,
                'catatan' => 'Hadir tepat waktu',
            ]);

        $response->assertRedirect()->assertSessionHas('success');

        $presensi = $this->pendaftaran->fresh()->presensi;
        $this->assertNotNull($presensi);
        $this->assertSame(PresensiAsesi::STATUS_HADIR, $presensi->status);
        $this->assertSame($this->asesorUser->id, $presensi->dicatat_oleh);
        $this->assertSame($this->institution->id, $presensi->institution_id);
        $this->assertFalse($presensi->isLocked());
    }

    public function test_other_asesor_cannot_record_presensi(): void
    {
        $otherUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'aktif',
        ]);
        $otherUser->assignRole('asesor');
        $otherUser->institutions()->attach($this->institution->id, ['role' => 'asesor']);

        Asesor::create([
            'user_id' => $otherUser->id,
            'no_register' => 'MET.000.000023 2026',
            'status' => 'aktif',
            'kebangsaan' => 'Indonesia',
        ]);

        $this->actingAs($otherUser)
            ->post(route('asesor.presensi.catat', $this->pendaftaran), [
                'status' => PresensiAsesi::STATUS_HADIR,
            ])
            ->assertForbidden();

        $this->assertNull($this->pendaftaran->fresh()->presensi);
    }

    public function test_mulai_asesmen_blocked_when_pre_asesmen_incomplete(): void
    {
        $this->setApprovalStatus(PersetujuanAsesmen::STATUS_MENUNGGU);

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.pendaftaran.mulai-asesmen', $this->pendaftaran))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(Pendaftaran::STATUS_TERJADWAL, $this->pendaftaran->fresh()->status);
    }

    public function test_asesor_can_start_asesmen_when_all_approved(): void
    {
        $this->actingAs($this->asesorUser)
            ->post(route('asesor.pendaftaran.mulai-asesmen', $this->pendaftaran))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(Pendaftaran::STATUS_ASESMEN_BERJALAN, $this->pendaftaran->fresh()->status);
    }

    public function test_mulai_asesmen_rejected_when_status_not_terjadwal(): void
    {
        $this->pendaftaran->update(['status' => Pendaftaran::STATUS_ASESMEN_BERJALAN]);

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.pendaftaran.mulai-asesmen', $this->pendaftaran))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(Pendaftaran::STATUS_ASESMEN_BERJALAN, $this->pendaftaran->fresh()->status);
    }

    public function test_asesor_cannot_change_locked_presensi(): void
    {
        PresensiAsesi::create([
            'institution_id' => $this->institution->id,
            'pendaftaran_id' => $this->pendaftaran->id,
            'jadwal_id' => $this->jadwal->id,
            'status' => PresensiAsesi::STATUS_HADIR,
            'dicatat_oleh' => $this->asesorUser->id,
            'dicatat_pada' => now(),
            'terkunci_pada' => now(),
        ]);

        $this->pendaftaran->update(['status' => Pendaftaran::STATUS_SELESAI]);

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.presensi.catat', $this->pendaftaran), [
                'status' => PresensiAsesi::STATUS_TIDAK_HADIR,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(PresensiAsesi::STATUS_HADIR, $this->pendaftaran->fresh()->presensi->status);
    }

    public function test_admin_can_correct_locked_presensi(): void
    {
        PresensiAsesi::create([
            'institution_id' => $this->institution->id,
            'pendaftaran_id' => $this->pendaftaran->id,
            'jadwal_id' => $this->jadwal->id,
            'status' => PresensiAsesi::STATUS_HADIR,
            'dicatat_oleh' => $this->asesorUser->id,
            'dicatat_pada' => now(),
            'terkunci_pada' => now(),
        ]);

        $this->pendaftaran->update(['status' => Pendaftaran::STATUS_SELESAI]);

        $this->actingAs($this->admin)
            ->post(route('admin.pendaftaran.presensi.catat', $this->pendaftaran), [
                'status' => PresensiAsesi::STATUS_IZIN,
                'catatan' => 'Koreksi admin: surat izin diterima',
            ])
            ->assertRedirect(route('admin.pendaftaran.show', $this->pendaftaran))
            ->assertSessionHas('success');

        $presensi = $this->pendaftaran->fresh()->presensi;
        $this->assertSame(PresensiAsesi::STATUS_IZIN, $presensi->status);
        $this->assertSame($this->admin->id, $presensi->dicatat_oleh);
        $this->assertNull($presensi->terkunci_pada);
    }

    public function test_asesor_peserta_page_renders_presensi_controls(): void
    {
        $this->actingAs($this->asesorUser)
            ->get(route('asesor.jadwal.peserta', $this->jadwal))
            ->assertOk()
            ->assertSee('Presensi')
            ->assertSee(route('asesor.presensi.catat', $this->pendaftaran), false)
            ->assertSee(route('asesor.pendaftaran.mulai-asesmen', $this->pendaftaran), false);
    }

    public function test_admin_pendaftaran_page_renders_presensi_card(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.pendaftaran.show', $this->pendaftaran))
            ->assertOk()
            ->assertSee('Presensi Asesi')
            ->assertSee(route('admin.pendaftaran.presensi.catat', $this->pendaftaran), false);
    }

    public function test_service_locks_all_presensi_for_pendaftaran(): void
    {
        PresensiAsesi::create([
            'institution_id' => $this->institution->id,
            'pendaftaran_id' => $this->pendaftaran->id,
            'jadwal_id' => $this->jadwal->id,
            'status' => PresensiAsesi::STATUS_HADIR,
            'dicatat_oleh' => $this->asesorUser->id,
            'dicatat_pada' => now(),
        ]);

        app(PresensiService::class)->kunci($this->pendaftaran);

        $this->assertTrue($this->pendaftaran->fresh()->presensi->isLocked());
    }
}
