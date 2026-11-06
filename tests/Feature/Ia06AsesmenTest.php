<?php

namespace Tests\Feature;

use App\Models\Asesi;
use App\Models\Asesor;
use App\Models\EventAsesmen;
use App\Models\Ia06Asesmen;
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

class Ia06AsesmenTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    private User $asesorUser;

    private Pendaftaran $pendaftaran;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->institution = Institution::create(['name' => 'LSP IA06', 'slug' => 'lsp-ia06', 'is_active' => true, 'is_setup_completed' => true]);
        TenantContext::set($this->institution);
        $asesiUser = User::factory()->create(['institution_id' => $this->institution->id, 'name' => 'Asesi IA06', 'email' => 'asesi.ia06@example.test', 'nik' => '3201123456780611', 'phone' => '081234567916', 'status' => 'aktif']);
        $asesiUser->assignRole('asesi');
        $asesiUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);
        $asesi = Asesi::create(['user_id' => $asesiUser->id, 'no_registrasi' => '202611050116', 'jenis_kelamin' => 'L', 'tempat_lahir' => 'Bandung', 'tanggal_lahir' => '2000-01-01', 'kebangsaan' => 'Indonesia', 'alamat_rumah' => 'Jl. IA06', 'pendidikan_terakhir' => 'D3', 'status_kompetensi' => 'belum_asesmen']);
        $this->asesorUser = User::factory()->create(['institution_id' => $this->institution->id, 'name' => 'Asesor IA06', 'email' => 'asesor.ia06@example.test', 'status' => 'aktif']);
        $this->asesorUser->assignRole('asesor');
        $this->asesorUser->institutions()->attach($this->institution->id, ['role' => 'asesor']);
        $asesor = Asesor::create(['user_id' => $this->asesorUser->id, 'no_register' => 'MET.000.000108 2026', 'status' => 'aktif', 'kebangsaan' => 'Indonesia']);
        $skema = Skema::create(['kode_skema' => 'SKM-IA06', 'nama_skema' => 'Skema IA06', 'jenis' => 'okupasi', 'biaya' => 0, 'is_active' => true]);
        $event = EventAsesmen::create(['nama' => 'Event IA06', 'tahun' => 2026]);
        $tuk = Tuk::create(['kode_tuk' => 'TUK-IA06', 'nama_tuk' => 'TUK IA06', 'jenis' => 'mandiri', 'penanggung_jawab' => 'PJ']);
        $jadwal = JadwalAsesmen::create(['event_id' => $event->id, 'skema_id' => $skema->id, 'tuk_id' => $tuk->id, 'tanggal' => '2026-12-30', 'jam_mulai' => '08:00', 'jam_selesai' => '12:00', 'kuota' => 10, 'lingkup_asesor' => 'wilayah', 'status' => 'terbuka']);
        JadwalAsesor::create(['jadwal_id' => $jadwal->id, 'asesor_id' => $asesor->id, 'peran' => 'utama']);
        $this->pendaftaran = Pendaftaran::create(['no_pendaftaran' => 'PEND-IA06-0001', 'asesi_id' => $asesi->id, 'skema_id' => $skema->id, 'jadwal_id' => $jadwal->id, 'status' => Pendaftaran::STATUS_ASESMEN_BERJALAN]);
    }

    public function test_show_page(): void
    {
        $this->actingAs($this->asesorUser)->get(route('asesor.ia06.show', $this->pendaftaran))->assertOk()->assertSee('FR.IA.06');
    }

    public function test_add_question_answer_and_finalize(): void
    {
        $this->actingAs($this->asesorUser)->post(route('asesor.ia06.tambah', $this->pendaftaran), [
            'pertanyaan' => 'Jelaskan strategi pemasaran digital.',
            'kunci' => 'Menyebutkan kanal, target, dan metrik.',
        ])->assertRedirect()->assertSessionHas('success');

        $ia06 = Ia06Asesmen::where('pendaftaran_id', $this->pendaftaran->id)->firstOrFail();
        $soal = $ia06->soals()->firstOrFail();

        $this->actingAs($this->asesorUser)->post(route('asesor.ia06.store', $this->pendaftaran), [
            'judul' => 'Soal Esai Pemasaran',
            'umpan_balik' => 'Baik.',
            'soals' => [[
                'id' => $soal->id,
                'pertanyaan' => $soal->pertanyaan,
                'kunci' => $soal->kunci,
                'jawaban_asesi' => 'Kanal, target, metrik.',
                'pencapaian' => '1',
            ]],
        ])->assertRedirect()->assertSessionHas('success');

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.ia06.final', $this->pendaftaran))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue($ia06->fresh()->isFinal());
    }

    public function test_final_blocked_when_not_rated(): void
    {
        $this->actingAs($this->asesorUser)->post(route('asesor.ia06.tambah', $this->pendaftaran), ['pertanyaan' => 'Q1']);

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.ia06.final', $this->pendaftaran))
            ->assertRedirect()
            ->assertSessionHasErrors();
    }

    public function test_pdf_renders(): void
    {
        $this->actingAs($this->asesorUser)->post(route('asesor.ia06.tambah', $this->pendaftaran), ['pertanyaan' => 'Q1', 'kunci' => 'K']);

        $this->actingAs($this->asesorUser)
            ->get(route('asesor.ia06.pdf', $this->pendaftaran))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_other_asesor_cannot_access(): void
    {
        $other = User::factory()->create(['institution_id' => $this->institution->id, 'status' => 'aktif']);
        $other->assignRole('asesor');
        $other->institutions()->attach($this->institution->id, ['role' => 'asesor']);
        Asesor::create(['user_id' => $other->id, 'no_register' => 'MET.000.000109 2026', 'status' => 'aktif', 'kebangsaan' => 'Indonesia']);

        $this->actingAs($other)->get(route('asesor.ia06.show', $this->pendaftaran))->assertForbidden();
    }
}
