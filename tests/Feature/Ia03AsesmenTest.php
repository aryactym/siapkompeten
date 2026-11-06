<?php

namespace Tests\Feature;

use App\Models\Asesi;
use App\Models\Asesor;
use App\Models\EventAsesmen;
use App\Models\Ia03Asesmen;
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

class Ia03AsesmenTest extends TestCase
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
        $this->institution = Institution::create(['name' => 'LSP IA03', 'slug' => 'lsp-ia03', 'is_active' => true, 'is_setup_completed' => true]);
        TenantContext::set($this->institution);
        $asesiUser = User::factory()->create(['institution_id' => $this->institution->id, 'name' => 'Asesi IA03', 'email' => 'asesi.ia03@example.test', 'nik' => '3201123456780111', 'phone' => '081234567911', 'status' => 'aktif']);
        $asesiUser->assignRole('asesi');
        $asesiUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);
        $asesi = Asesi::create(['user_id' => $asesiUser->id, 'no_registrasi' => '202611050111', 'jenis_kelamin' => 'L', 'tempat_lahir' => 'Bandung', 'tanggal_lahir' => '2000-01-01', 'kebangsaan' => 'Indonesia', 'alamat_rumah' => 'Jl. IA03', 'pendidikan_terakhir' => 'D3', 'status_kompetensi' => 'belum_asesmen']);
        $this->asesorUser = User::factory()->create(['institution_id' => $this->institution->id, 'name' => 'Asesor IA03', 'email' => 'asesor.ia03@example.test', 'status' => 'aktif']);
        $this->asesorUser->assignRole('asesor');
        $this->asesorUser->institutions()->attach($this->institution->id, ['role' => 'asesor']);
        $this->asesor = Asesor::create(['user_id' => $this->asesorUser->id, 'no_register' => 'MET.000.000100 2026', 'status' => 'aktif', 'kebangsaan' => 'Indonesia']);
        $skema = Skema::create(['kode_skema' => 'SKM-IA03', 'nama_skema' => 'Skema IA03', 'jenis' => 'okupasi', 'biaya' => 0, 'is_active' => true]);
        $event = EventAsesmen::create(['nama' => 'Event IA03', 'tahun' => 2026]);
        $tuk = Tuk::create(['kode_tuk' => 'TUK-IA03', 'nama_tuk' => 'TUK IA03', 'jenis' => 'mandiri', 'penanggung_jawab' => 'PJ']);
        $this->jadwal = JadwalAsesmen::create(['event_id' => $event->id, 'skema_id' => $skema->id, 'tuk_id' => $tuk->id, 'tanggal' => '2026-12-30', 'jam_mulai' => '08:00', 'jam_selesai' => '12:00', 'kuota' => 10, 'lingkup_asesor' => 'wilayah', 'status' => 'terbuka']);
        JadwalAsesor::create(['jadwal_id' => $this->jadwal->id, 'asesor_id' => $this->asesor->id, 'peran' => 'utama']);
        $this->pendaftaran = Pendaftaran::create(['no_pendaftaran' => 'PEND-IA03-0001', 'asesi_id' => $asesi->id, 'skema_id' => $skema->id, 'jadwal_id' => $this->jadwal->id, 'status' => Pendaftaran::STATUS_ASESMEN_BERJALAN]);
    }

    public function test_show_page(): void
    {
        $this->actingAs($this->asesorUser)->get(route('asesor.ia03.show', $this->pendaftaran))->assertOk()->assertSee('FR.IA.03');
    }

    public function test_add_question_and_finalize(): void
    {
        $this->actingAs($this->asesorUser)->post(route('asesor.ia03.tambah', $this->pendaftaran), ['pertanyaan' => 'Bagaimana K3L?'])->assertRedirect()->assertSessionHas('success');
        $ia03 = Ia03Asesmen::where('pendaftaran_id', $this->pendaftaran->id)->firstOrFail();
        $item = $ia03->items()->first();
        $this->actingAs($this->asesorUser)->post(route('asesor.ia03.store', $this->pendaftaran), [
            'items' => [['id' => $item->id, 'tanggapan' => 'Jawaban', 'pencapaian' => '1', 'catatan' => 'OK']],
            'umpan_balik' => 'Baik.',
        ])->assertRedirect()->assertSessionHas('success');
        $this->actingAs($this->asesorUser)->post(route('asesor.ia03.final', $this->pendaftaran))->assertRedirect()->assertSessionHas('success');
        $this->assertTrue($ia03->fresh()->isFinal());
    }

    public function test_final_blocked_when_not_rated(): void
    {
        $this->actingAs($this->asesorUser)->post(route('asesor.ia03.tambah', $this->pendaftaran), ['pertanyaan' => 'Q1']);
        $this->actingAs($this->asesorUser)->post(route('asesor.ia03.final', $this->pendaftaran))->assertRedirect()->assertSessionHasErrors();
    }

    public function test_pdf_renders(): void
    {
        $this->actingAs($this->asesorUser)->post(route('asesor.ia03.tambah', $this->pendaftaran), ['pertanyaan' => 'Q1']);
        $ia03 = Ia03Asesmen::where('pendaftaran_id', $this->pendaftaran->id)->firstOrFail();
        $item = $ia03->items()->first();
        $this->actingAs($this->asesorUser)->post(route('asesor.ia03.store', $this->pendaftaran), ['items' => [['id' => $item->id, 'pencapaian' => '1']]]);
        $this->actingAs($this->asesorUser)->get(route('asesor.ia03.pdf', $this->pendaftaran))->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_other_asesor_cannot_access(): void
    {
        $other = User::factory()->create(['institution_id' => $this->institution->id, 'status' => 'aktif']);
        $other->assignRole('asesor');
        $other->institutions()->attach($this->institution->id, ['role' => 'asesor']);
        Asesor::create(['user_id' => $other->id, 'no_register' => 'MET.000.000101 2026', 'status' => 'aktif', 'kebangsaan' => 'Indonesia']);
        $this->actingAs($other)->get(route('asesor.ia03.show', $this->pendaftaran))->assertForbidden();
    }
}
