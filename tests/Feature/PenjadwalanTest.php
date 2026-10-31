<?php

namespace Tests\Feature;

use App\Models\Asesi;
use App\Models\Asesor;
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

class PenjadwalanTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    private User $admin;

    private User $asesorUser;

    private Asesor $asesor;

    private EventAsesmen $event;

    private Skema $skema;

    private Tuk $tuk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->institution = Institution::create([
            'name' => 'LSP Penjadwalan',
            'slug' => 'lsp-penjadwalan',
            'is_active' => true,
        ]);

        TenantContext::set($this->institution);

        $this->admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'aktif',
        ]);
        $this->admin->assignRole('admin');
        $this->admin->institutions()->attach($this->institution->id, ['role' => 'admin']);

        $this->asesorUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesor Penjadwalan',
            'email' => 'asesor.penjadwalan@example.test',
            'status' => 'aktif',
        ]);
        $this->asesorUser->assignRole('asesor');
        $this->asesorUser->institutions()->attach($this->institution->id, ['role' => 'asesor']);

        $this->asesor = Asesor::create([
            'user_id' => $this->asesorUser->id,
            'no_register' => 'MET.000.000001 2026',
            'status' => 'aktif',
            'kebangsaan' => 'Indonesia',
        ]);

        $this->event = EventAsesmen::create([
            'nama' => 'Uji Kompetensi Gelombang I',
            'tahun' => 2026,
            'periode' => 10,
        ]);

        $this->skema = Skema::create([
            'kode_skema' => 'SKM-JADWAL',
            'nama_skema' => 'Skema Penjadwalan',
            'jenis' => 'okupasi',
            'biaya' => 0,
            'is_active' => true,
        ]);

        $this->tuk = Tuk::create([
            'kode_tuk' => 'TUK-JADWAL',
            'nama_tuk' => 'TUK Penjadwalan',
            'jenis' => 'mandiri',
            'penanggung_jawab' => 'Penanggung Jawab',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_jadwal_and_assign_multi_asesor_with_notification(): void
    {
        $jadwal = $this->createJadwal();

        $response = $this->asAdmin()->post(route('admin.jadwal.asesor.store', $jadwal), [
            'asesor_id' => $this->asesor->id,
            'peran' => 'utama',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('jadwal_asesors', [
            'jadwal_id' => $jadwal->id,
            'asesor_id' => $this->asesor->id,
            'peran' => 'utama',
        ]);

        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $this->asesorUser->id,
            'tipe' => 'jadwal_asesor',
        ]);
    }

    public function test_asesor_with_overlapping_schedule_is_rejected(): void
    {
        $jadwal = $this->createJadwal();

        $this->asAdmin()->post(route('admin.jadwal.asesor.store', $jadwal), [
            'asesor_id' => $this->asesor->id,
            'peran' => 'utama',
        ])->assertSessionHas('success');

        $otherJadwal = JadwalAsesmen::create([
            'event_id' => $this->event->id,
            'skema_id' => $this->skema->id,
            'tuk_id' => $this->tuk->id,
            'tanggal' => '2026-10-20',
            'jam_mulai' => '09:30',
            'jam_selesai' => '12:00',
            'kuota' => 10,
            'lingkup_asesor' => 'wilayah',
            'status' => 'terbuka',
        ]);

        $response = $this->asAdmin()->post(route('admin.jadwal.asesor.store', $otherJadwal), [
            'asesor_id' => $this->asesor->id,
            'peran' => 'pendamping',
        ]);

        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('jadwal_asesors', [
            'jadwal_id' => $otherJadwal->id,
            'asesor_id' => $this->asesor->id,
        ]);
    }

    public function test_asesor_can_only_open_peserta_of_assigned_schedule(): void
    {
        $jadwal = $this->createJadwal();

        $this->asAdmin()->post(route('admin.jadwal.asesor.store', $jadwal), [
            'asesor_id' => $this->asesor->id,
            'peran' => 'utama',
        ]);

        $this->actingAs($this->asesorUser)
            ->withSession([TenantContext::SESSION_ID => $this->institution->id])
            ->get(route('asesor.jadwal.peserta', $jadwal))
            ->assertOk()
            ->assertSee($this->skema->nama_skema);

        $otherAsesorUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'aktif',
        ]);
        $otherAsesorUser->assignRole('asesor');
        $otherAsesorUser->institutions()->attach($this->institution->id, ['role' => 'asesor']);
        Asesor::create([
            'user_id' => $otherAsesorUser->id,
            'no_register' => 'MET.000.000002 2026',
            'status' => 'aktif',
            'kebangsaan' => 'Indonesia',
        ]);

        $this->actingAs($otherAsesorUser)
            ->withSession([TenantContext::SESSION_ID => $this->institution->id])
            ->get(route('asesor.jadwal.peserta', $jadwal))
            ->assertForbidden();
    }

    public function test_presensi_contains_real_time_participants(): void
    {
        $jadwal = $this->createJadwal(kuota: 5);

        $asesiUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Peserta Presensi',
            'status' => 'aktif',
        ]);
        $asesiUser->assignRole('asesi');
        $asesiUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        $asesi = Asesi::create([
            'user_id' => $asesiUser->id,
            'no_registrasi' => '202610200001',
        ]);

        Pendaftaran::create([
            'no_pendaftaran' => '202610200001',
            'asesi_id' => $asesi->id,
            'skema_id' => $this->skema->id,
            'jadwal_id' => $jadwal->id,
            'status' => Pendaftaran::STATUS_TERJADWAL,
        ]);

        $response = $this->asAdmin()
            ->get(route('admin.jadwal.presensi', $jadwal));

        $response->assertOk();
        $this->assertStringStartsWith('application/pdf', (string) $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', (string) $response->getContent());
        $this->assertDatabaseHas('pendaftarans', [
            'jadwal_id' => $jadwal->id,
            'asesi_id' => $asesi->id,
        ]);
    }

    public function test_jadwal_from_another_tenant_is_not_accessible(): void
    {
        $otherInstitution = Institution::create([
            'name' => 'LSP Lain',
            'slug' => 'lsp-lain',
            'is_active' => true,
        ]);

        TenantContext::clear();
        TenantContext::set($otherInstitution);

        $otherEvent = EventAsesmen::create([
            'nama' => 'Event Lain',
            'tahun' => 2026,
        ]);
        $otherSkema = Skema::create([
            'kode_skema' => 'SKM-LAIN',
            'nama_skema' => 'Skema Lain',
            'jenis' => 'okupasi',
        ]);
        $otherTuk = Tuk::create([
            'kode_tuk' => 'TUK-LAIN',
            'nama_tuk' => 'TUK Lain',
            'jenis' => 'mandiri',
            'penanggung_jawab' => 'PJ',
        ]);
        $otherJadwal = JadwalAsesmen::create([
            'event_id' => $otherEvent->id,
            'skema_id' => $otherSkema->id,
            'tuk_id' => $otherTuk->id,
            'tanggal' => '2026-11-01',
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'kuota' => 10,
            'lingkup_asesor' => 'wilayah',
            'status' => 'terbuka',
        ]);

        TenantContext::set($this->institution);

        $this->asAdmin()
            ->get(route('admin.jadwal.show', $otherJadwal))
            ->assertNotFound();
    }

    private function asAdmin(): static
    {
        return $this->actingAs($this->admin)
            ->withSession([TenantContext::SESSION_ID => $this->institution->id]);
    }

    private function createJadwal(int $kuota = 10): JadwalAsesmen
    {
        return JadwalAsesmen::create([
            'event_id' => $this->event->id,
            'skema_id' => $this->skema->id,
            'tuk_id' => $this->tuk->id,
            'tanggal' => '2026-10-20',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
            'kuota' => $kuota,
            'lingkup_asesor' => 'wilayah',
            'status' => 'terbuka',
        ]);
    }
}
