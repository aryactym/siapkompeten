<?php

namespace Tests\Feature;

use App\Models\Asesi;
use App\Models\Asesor;
use App\Models\EventAsesmen;
use App\Models\Ia04Asesmen;
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

class Ia04AsesmenTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    private User $asesorUser;

    private Asesor $asesor;

    private Pendaftaran $pendaftaran;

    private JadwalAsesmen $jadwal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->institution = Institution::create(['name' => 'LSP IA04', 'slug' => 'lsp-ia04', 'is_active' => true, 'is_setup_completed' => true]);
        TenantContext::set($this->institution);
        $asesiUser = User::factory()->create(['institution_id' => $this->institution->id, 'name' => 'Asesi IA04', 'email' => 'asesi.ia04@example.test', 'nik' => '3201123456780411', 'phone' => '081234567914', 'status' => 'aktif']);
        $asesiUser->assignRole('asesi');
        $asesiUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);
        $asesi = Asesi::create(['user_id' => $asesiUser->id, 'no_registrasi' => '202611050114', 'jenis_kelamin' => 'L', 'tempat_lahir' => 'Bandung', 'tanggal_lahir' => '2000-01-01', 'kebangsaan' => 'Indonesia', 'alamat_rumah' => 'Jl. IA04', 'pendidikan_terakhir' => 'D3', 'status_kompetensi' => 'belum_asesmen']);
        $this->asesorUser = User::factory()->create(['institution_id' => $this->institution->id, 'name' => 'Asesor IA04', 'email' => 'asesor.ia04@example.test', 'status' => 'aktif']);
        $this->asesorUser->assignRole('asesor');
        $this->asesorUser->institutions()->attach($this->institution->id, ['role' => 'asesor']);
        $this->asesor = Asesor::create(['user_id' => $this->asesorUser->id, 'no_register' => 'MET.000.000104 2026', 'status' => 'aktif', 'kebangsaan' => 'Indonesia']);
        $skema = Skema::create(['kode_skema' => 'SKM-IA04', 'nama_skema' => 'Skema IA04', 'jenis' => 'okupasi', 'biaya' => 0, 'is_active' => true]);
        $event = EventAsesmen::create(['nama' => 'Event IA04', 'tahun' => 2026]);
        $tuk = Tuk::create(['kode_tuk' => 'TUK-IA04', 'nama_tuk' => 'TUK IA04', 'jenis' => 'mandiri', 'penanggung_jawab' => 'PJ']);
        $this->jadwal = JadwalAsesmen::create(['event_id' => $event->id, 'skema_id' => $skema->id, 'tuk_id' => $tuk->id, 'tanggal' => '2026-12-30', 'jam_mulai' => '08:00', 'jam_selesai' => '12:00', 'kuota' => 10, 'lingkup_asesor' => 'wilayah', 'status' => 'terbuka']);
        JadwalAsesor::create(['jadwal_id' => $this->jadwal->id, 'asesor_id' => $this->asesor->id, 'peran' => 'utama']);
        $this->pendaftaran = Pendaftaran::create(['no_pendaftaran' => 'PEND-IA04-0001', 'asesi_id' => $asesi->id, 'skema_id' => $skema->id, 'jadwal_id' => $this->jadwal->id, 'status' => Pendaftaran::STATUS_ASESMEN_BERJALAN]);
    }

    public function test_show_page(): void
    {
        $this->actingAs($this->asesorUser)->get(route('asesor.ia04.show', $this->pendaftaran))->assertOk()->assertSee('FR.IA.04');
    }

    public function test_add_item_save_and_finalize(): void
    {
        $this->actingAs($this->asesorUser)
            ->post(route('asesor.ia04.tambah', $this->pendaftaran), ['pertanyaan' => 'Jelaskan strategi proyek.', 'lingkup' => 'Perencanaan'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $ia04 = Ia04Asesmen::where('pendaftaran_id', $this->pendaftaran->id)->firstOrFail();
        $item = $ia04->items()->first();

        $this->actingAs($this->asesorUser)->post(route('asesor.ia04.store', $this->pendaftaran), [
            'judul_kegiatan' => 'Kampanye Digital',
            'skenario' => 'Siapkan kampanye pemasaran digital.',
            'hal_dipersiapkan' => 'Rencana konten.',
            'waktu_persiapan' => '60 menit',
            'hasil_dipresentasikan' => 'Presentasi rencana.',
            'waktu_presentasi' => '30 menit',
            'umpan_balik' => 'Baik.',
            'rekomendasi' => Ia04Asesmen::REKOMENDASI_KOMPETEN,
            'items' => [['id' => $item->id, 'lingkup' => 'Perencanaan', 'tanggapan' => 'Jawaban', 'pencapaian' => '1']],
        ])->assertRedirect()->assertSessionHas('success');

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.ia04.final', $this->pendaftaran))
            ->assertRedirect()
            ->assertSessionHas('success');

        $fresh = $ia04->fresh();
        $this->assertTrue($fresh->isFinal());
        $this->assertSame(Ia04Asesmen::REKOMENDASI_KOMPETEN, $fresh->rekomendasi);
    }

    public function test_final_blocked_when_item_not_rated_or_recommendation_missing(): void
    {
        $this->actingAs($this->asesorUser)->post(route('asesor.ia04.tambah', $this->pendaftaran), ['pertanyaan' => 'Q1']);

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.ia04.final', $this->pendaftaran))
            ->assertRedirect()
            ->assertSessionHasErrors();
    }

    public function test_edit_blocked_after_final(): void
    {
        $ia04 = Ia04Asesmen::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'institution_id' => $this->institution->id,
            'status' => Ia04Asesmen::STATUS_FINAL,
            'rekomendasi' => Ia04Asesmen::REKOMENDASI_KOMPETEN,
            'finalized_at' => now(),
        ]);

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.ia04.store', $this->pendaftaran), ['judul_kegiatan' => 'Ubah'])
            ->assertRedirect()
            ->assertSessionHasErrors();

        $this->assertNull(Ia04Asesmen::find($ia04->id)->judul_kegiatan);
    }

    public function test_pdf_renders(): void
    {
        $this->actingAs($this->asesorUser)->post(route('asesor.ia04.tambah', $this->pendaftaran), ['pertanyaan' => 'Q1']);
        $ia04 = Ia04Asesmen::where('pendaftaran_id', $this->pendaftaran->id)->firstOrFail();
        $item = $ia04->items()->first();
        $this->actingAs($this->asesorUser)->post(route('asesor.ia04.store', $this->pendaftaran), [
            'rekomendasi' => Ia04Asesmen::REKOMENDASI_KOMPETEN,
            'items' => [['id' => $item->id, 'pencapaian' => '1']],
        ]);

        $this->actingAs($this->asesorUser)
            ->get(route('asesor.ia04.pdf', $this->pendaftaran))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_other_asesor_cannot_access(): void
    {
        $other = User::factory()->create(['institution_id' => $this->institution->id, 'status' => 'aktif']);
        $other->assignRole('asesor');
        $other->institutions()->attach($this->institution->id, ['role' => 'asesor']);
        Asesor::create(['user_id' => $other->id, 'no_register' => 'MET.000.000105 2026', 'status' => 'aktif', 'kebangsaan' => 'Indonesia']);

        $this->actingAs($other)->get(route('asesor.ia04.show', $this->pendaftaran))->assertForbidden();
    }
}
