<?php

namespace Tests\Feature;

use App\Models\Asesi;
use App\Models\Asesor;
use App\Models\EventAsesmen;
use App\Models\HasilAsesmen;
use App\Models\Institution;
use App\Models\JadwalAsesmen;
use App\Models\JadwalAsesor;
use App\Models\Pendaftaran;
use App\Models\Skema;
use App\Models\TinjauanProsesAsesmen;
use App\Models\Tuk;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TinjauanProsesAsesmenTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    private User $admin;

    private User $asesorUser;

    private Asesor $asesor;

    private Pendaftaran $pendaftaran;

    private JadwalAsesmen $jadwal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->institution = Institution::create([
            'name' => 'LSP Tinjauan',
            'slug' => 'lsp-tinjauan',
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

        $asesiUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesi Tinjauan',
            'email' => 'asesi.tinjauan@example.test',
            'nik' => '3201123456780077',
            'phone' => '081234567877',
            'status' => 'aktif',
        ]);
        $asesiUser->assignRole('asesi');
        $asesiUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        $asesi = Asesi::create([
            'user_id' => $asesiUser->id,
            'no_registrasi' => '202611050077',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Yogyakarta',
            'tanggal_lahir' => '2000-01-01',
            'kebangsaan' => 'Indonesia',
            'alamat_rumah' => 'Jl. Tinjauan No. 1',
            'pendidikan_terakhir' => 'D3',
            'status_kompetensi' => 'kompeten',
        ]);

        $this->asesorUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesor Tinjauan',
            'email' => 'asesor.tinjauan@example.test',
            'status' => 'aktif',
        ]);
        $this->asesorUser->assignRole('asesor');
        $this->asesorUser->institutions()->attach($this->institution->id, ['role' => 'asesor']);

        $this->asesor = Asesor::create([
            'user_id' => $this->asesorUser->id,
            'no_register' => 'MET.000.000066 2026',
            'status' => 'aktif',
            'kebangsaan' => 'Indonesia',
        ]);

        $skema = Skema::create([
            'kode_skema' => 'SKM-TIN',
            'nama_skema' => 'Skema Tinjauan',
            'jenis' => 'okupasi',
            'biaya' => 0,
            'is_active' => true,
        ]);

        $event = EventAsesmen::create(['nama' => 'Event Tinjauan', 'tahun' => 2026]);
        $tuk = Tuk::create([
            'kode_tuk' => 'TUK-TIN',
            'nama_tuk' => 'TUK Tinjauan',
            'jenis' => 'mandiri',
            'penanggung_jawab' => 'PJ',
        ]);

        $this->jadwal = JadwalAsesmen::create([
            'event_id' => $event->id,
            'skema_id' => $skema->id,
            'tuk_id' => $tuk->id,
            'tanggal' => '2026-12-20',
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
            'no_pendaftaran' => 'PEND-TIN-0001',
            'asesi_id' => $asesi->id,
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

    public function test_asesor_can_open_tinjauan_page(): void
    {
        $this->actingAs($this->asesorUser)
            ->get(route('asesor.tinjauan-proses.show', $this->jadwal))
            ->assertOk()
            ->assertSee('Prosedur Asesmen terhadap Prinsip Asesmen')
            ->assertSee('Konsistensi Keputusan Asesmen')
            ->assertSee('Finalkan Tinjauan');
    }

    public function test_other_asesor_cannot_open_tinjauan(): void
    {
        $otherUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'aktif',
        ]);
        $otherUser->assignRole('asesor');
        $otherUser->institutions()->attach($this->institution->id, ['role' => 'asesor']);

        Asesor::create([
            'user_id' => $otherUser->id,
            'no_register' => 'MET.000.000067 2026',
            'status' => 'aktif',
            'kebangsaan' => 'Indonesia',
        ]);

        $this->actingAs($otherUser)
            ->get(route('asesor.tinjauan-proses.show', $this->jadwal))
            ->assertForbidden();
    }

    public function test_asesor_can_save_matrix_values(): void
    {
        $this->actingAs($this->asesorUser)
            ->post(route('asesor.tinjauan-proses.store', $this->jadwal), [
                'proses' => [
                    'rencana' => ['validitas' => 'FR.MAPA.01', 'reliabel' => 'FR.MAPA.02'],
                    'persiapan' => ['adil' => 'FR.AK.01'],
                ],
                'rekomendasi_proses' => 'Perkuat dokumentasi rencana.',
                'dimensi' => [
                    'task_skills' => 'FR.IA.01',
                    'transfer' => 'FR.IA.02',
                ],
                'rekomendasi_dimensi' => 'Tambah variasi tugas.',
                'komentar' => 'Proses berjalan konsisten.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $tinjauan = TinjauanProsesAsesmen::where('jadwal_id', $this->jadwal->id)->firstOrFail();

        $this->assertSame('FR.MAPA.01', $tinjauan->nilaiProses('rencana', 'validitas'));
        $this->assertSame('FR.AK.01', $tinjauan->nilaiProses('persiapan', 'adil'));
        $this->assertNull($tinjauan->nilaiProses('keputusan', 'validitas'));
        $this->assertSame('FR.IA.02', $tinjauan->nilaiDimensi('transfer'));
        $this->assertSame('Perkuat dokumentasi rencana.', $tinjauan->rekomendasi_proses);
        $this->assertSame($this->asesor->id, $tinjauan->asesor_id);
    }

    public function test_final_is_blocked_when_asesmen_not_finished(): void
    {
        $this->pendaftaran->update(['status' => Pendaftaran::STATUS_ASESMEN_BERJALAN]);

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.tinjauan-proses.final', $this->jadwal))
            ->assertRedirect()
            ->assertSessionHasErrors('tinjauan');

        $this->assertNull(TinjauanProsesAsesmen::where('jadwal_id', $this->jadwal->id)->first());
    }

    public function test_final_locks_tinjauan_and_blocks_edits(): void
    {
        $this->actingAs($this->asesorUser)
            ->post(route('asesor.tinjauan-proses.final', $this->jadwal))
            ->assertRedirect()
            ->assertSessionHas('success');

        $tinjauan = TinjauanProsesAsesmen::where('jadwal_id', $this->jadwal->id)->firstOrFail();

        $this->assertTrue($tinjauan->isFinal());
        $this->assertSame($this->asesorUser->id, $tinjauan->finalized_by);

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.tinjauan-proses.store', $this->jadwal), [
                'komentar' => 'Perubahan setelah final.',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('tinjauan');

        $this->assertNull($tinjauan->fresh()->komentar);
    }

    public function test_pdf_returns_404_before_tinjauan_is_created(): void
    {
        $this->actingAs($this->asesorUser)
            ->get(route('asesor.tinjauan-proses.pdf', $this->jadwal))
            ->assertNotFound();
    }

    public function test_asesor_can_download_pdf(): void
    {
        $this->actingAs($this->asesorUser)
            ->post(route('asesor.tinjauan-proses.store', $this->jadwal), [
                'komentar' => 'Baik.',
            ]);

        $response = $this->actingAs($this->asesorUser)
            ->get(route('asesor.tinjauan-proses.pdf', $this->jadwal));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_can_download_pdf(): void
    {
        $this->actingAs($this->asesorUser)
            ->post(route('asesor.tinjauan-proses.store', $this->jadwal), [
                'komentar' => 'Baik.',
            ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.jadwal.tinjauan-proses.pdf', $this->jadwal));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_asesor_peserta_page_links_to_tinjauan(): void
    {
        $this->actingAs($this->asesorUser)
            ->get(route('asesor.jadwal.peserta', $this->jadwal))
            ->assertOk()
            ->assertSee(route('asesor.tinjauan-proses.show', $this->jadwal), false);
    }
}
