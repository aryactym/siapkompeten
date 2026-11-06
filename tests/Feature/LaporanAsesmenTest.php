<?php

namespace Tests\Feature;

use App\Models\Asesi;
use App\Models\Asesor;
use App\Models\EventAsesmen;
use App\Models\HasilAsesmen;
use App\Models\Institution;
use App\Models\JadwalAsesmen;
use App\Models\JadwalAsesor;
use App\Models\LaporanAsesmen;
use App\Models\Pendaftaran;
use App\Models\Skema;
use App\Models\Tuk;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanAsesmenTest extends TestCase
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
            'name' => 'LSP Laporan',
            'slug' => 'lsp-laporan',
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
            'name' => 'Asesi Laporan',
            'email' => 'asesi.laporan@example.test',
            'nik' => '3201123456780066',
            'phone' => '081234567866',
            'status' => 'aktif',
        ]);
        $this->asesiUser->assignRole('asesi');
        $this->asesiUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        $this->asesi = Asesi::create([
            'user_id' => $this->asesiUser->id,
            'no_registrasi' => '202611050066',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Semarang',
            'tanggal_lahir' => '2000-01-01',
            'kebangsaan' => 'Indonesia',
            'alamat_rumah' => 'Jl. Laporan No. 1',
            'pendidikan_terakhir' => 'D3',
            'status_kompetensi' => 'kompeten',
        ]);

        $this->asesorUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesor Laporan',
            'email' => 'asesor.laporan@example.test',
            'status' => 'aktif',
        ]);
        $this->asesorUser->assignRole('asesor');
        $this->asesorUser->institutions()->attach($this->institution->id, ['role' => 'asesor']);

        $this->asesor = Asesor::create([
            'user_id' => $this->asesorUser->id,
            'no_register' => 'MET.000.000055 2026',
            'status' => 'aktif',
            'kebangsaan' => 'Indonesia',
        ]);

        $skema = Skema::create([
            'kode_skema' => 'SKM-LAP',
            'nama_skema' => 'Skema Laporan',
            'jenis' => 'okupasi',
            'biaya' => 0,
            'is_active' => true,
        ]);

        $event = EventAsesmen::create(['nama' => 'Event Laporan', 'tahun' => 2026]);
        $tuk = Tuk::create([
            'kode_tuk' => 'TUK-LAP',
            'nama_tuk' => 'TUK Laporan',
            'jenis' => 'mandiri',
            'penanggung_jawab' => 'PJ',
        ]);

        $this->jadwal = JadwalAsesmen::create([
            'event_id' => $event->id,
            'skema_id' => $skema->id,
            'tuk_id' => $tuk->id,
            'tanggal' => '2026-12-18',
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
            'no_pendaftaran' => 'PEND-LAP-0001',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $skema->id,
            'jadwal_id' => $this->jadwal->id,
            'status' => Pendaftaran::STATUS_SELESAI,
        ]);

        HasilAsesmen::create([
            'institution_id' => $this->institution->id,
            'pendaftaran_id' => $this->pendaftaran->id,
            'asesor_id' => $this->asesor->id,
            'rekomendasi' => HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN,
            'persen_kompeten' => 100,
            'umpan_balik_diberikan' => true,
            'status' => HasilAsesmen::STATUS_FINAL,
            'finalized_at' => now(),
            'finalized_by' => $this->asesorUser->id,
        ]);
    }

    public function test_asesor_can_open_laporan_with_asesi_recap(): void
    {
        $this->actingAs($this->asesorUser)
            ->get(route('asesor.laporan-asesmen.show', $this->jadwal))
            ->assertOk()
            ->assertSee('Asesi Laporan')
            ->assertSee('Rekap Hasil Asesi')
            ->assertSee('Finalkan Laporan');
    }

    public function test_other_asesor_cannot_open_laporan(): void
    {
        $otherUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'aktif',
        ]);
        $otherUser->assignRole('asesor');
        $otherUser->institutions()->attach($this->institution->id, ['role' => 'asesor']);

        Asesor::create([
            'user_id' => $otherUser->id,
            'no_register' => 'MET.000.000056 2026',
            'status' => 'aktif',
            'kebangsaan' => 'Indonesia',
        ]);

        $this->actingAs($otherUser)
            ->get(route('asesor.laporan-asesmen.show', $this->jadwal))
            ->assertForbidden();
    }

    public function test_asesor_can_save_narrative_fields(): void
    {
        $this->actingAs($this->asesorUser)
            ->post(route('asesor.laporan-asesmen.store', $this->jadwal), [
                'aspek_positif' => 'Asesi kooperatif.',
                'aspek_negatif' => 'Waktu terbatas.',
                'pencatatan_penolakan' => 'Tidak ada penolakan.',
                'saran_perbaikan' => 'Tambah durasi praktik.',
                'catatan' => 'Laporan lengkap.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $laporan = LaporanAsesmen::where('jadwal_id', $this->jadwal->id)->firstOrFail();

        $this->assertSame('Asesi kooperatif.', $laporan->aspek_positif);
        $this->assertSame($this->asesor->id, $laporan->asesor_id);
        $this->assertSame(LaporanAsesmen::STATUS_DRAF, $laporan->status);
    }

    public function test_final_is_blocked_when_asesmen_not_finished(): void
    {
        $this->pendaftaran->update(['status' => Pendaftaran::STATUS_ASESMEN_BERJALAN]);

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.laporan-asesmen.final', $this->jadwal))
            ->assertRedirect()
            ->assertSessionHasErrors('laporan');

        $this->assertNull(LaporanAsesmen::where('jadwal_id', $this->jadwal->id)->first());
    }

    public function test_final_locks_laporan_and_blocks_edits(): void
    {
        $this->actingAs($this->asesorUser)
            ->post(route('asesor.laporan-asesmen.final', $this->jadwal))
            ->assertRedirect()
            ->assertSessionHas('success');

        $laporan = LaporanAsesmen::where('jadwal_id', $this->jadwal->id)->firstOrFail();

        $this->assertTrue($laporan->isFinal());
        $this->assertSame($this->asesorUser->id, $laporan->finalized_by);

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.laporan-asesmen.store', $this->jadwal), [
                'aspek_positif' => 'Perubahan setelah final.',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('laporan');

        $this->assertNull($laporan->fresh()->aspek_positif);
    }

    public function test_pdf_returns_404_before_laporan_is_created(): void
    {
        $this->actingAs($this->asesorUser)
            ->get(route('asesor.laporan-asesmen.pdf', $this->jadwal))
            ->assertNotFound();
    }

    public function test_asesor_can_download_pdf(): void
    {
        $this->actingAs($this->asesorUser)
            ->post(route('asesor.laporan-asesmen.store', $this->jadwal), [
                'aspek_positif' => 'Baik.',
            ]);

        $response = $this->actingAs($this->asesorUser)
            ->get(route('asesor.laporan-asesmen.pdf', $this->jadwal));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_can_download_pdf(): void
    {
        $this->actingAs($this->asesorUser)
            ->post(route('asesor.laporan-asesmen.store', $this->jadwal), [
                'aspek_positif' => 'Baik.',
            ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.jadwal.laporan-asesmen.pdf', $this->jadwal));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_asesor_peserta_page_links_to_laporan(): void
    {
        $this->actingAs($this->asesorUser)
            ->get(route('asesor.jadwal.peserta', $this->jadwal))
            ->assertOk()
            ->assertSee(route('asesor.laporan-asesmen.show', $this->jadwal), false);
    }
}
