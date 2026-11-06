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
use App\Models\UmpanBalikAsesmen;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UmpanBalikAsesmenTest extends TestCase
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
            'name' => 'LSP Umpan Balik',
            'slug' => 'lsp-umpan-balik',
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
            'name' => 'Asesi Umpan Balik',
            'email' => 'asesi.umpanbalik@example.test',
            'nik' => '3201123456780055',
            'phone' => '081234567855',
            'status' => 'aktif',
        ]);
        $this->asesiUser->assignRole('asesi');
        $this->asesiUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        $this->asesi = Asesi::create([
            'user_id' => $this->asesiUser->id,
            'no_registrasi' => '202611050055',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Medan',
            'tanggal_lahir' => '2000-01-01',
            'kebangsaan' => 'Indonesia',
            'alamat_rumah' => 'Jl. Umpan Balik No. 1',
            'pendidikan_terakhir' => 'D3',
            'status_kompetensi' => 'belum_asesmen',
        ]);

        $this->asesorUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesor Umpan Balik',
            'email' => 'asesor.umpanbalik@example.test',
            'status' => 'aktif',
        ]);
        $this->asesorUser->assignRole('asesor');
        $this->asesorUser->institutions()->attach($this->institution->id, ['role' => 'asesor']);

        $this->asesor = Asesor::create([
            'user_id' => $this->asesorUser->id,
            'no_register' => 'MET.000.000044 2026',
            'status' => 'aktif',
            'kebangsaan' => 'Indonesia',
        ]);

        $skema = Skema::create([
            'kode_skema' => 'SKM-UB',
            'nama_skema' => 'Skema Umpan Balik',
            'jenis' => 'okupasi',
            'biaya' => 0,
            'is_active' => true,
        ]);

        $event = EventAsesmen::create(['nama' => 'Event Umpan Balik', 'tahun' => 2026]);
        $tuk = Tuk::create([
            'kode_tuk' => 'TUK-UB',
            'nama_tuk' => 'TUK Umpan Balik',
            'jenis' => 'mandiri',
            'penanggung_jawab' => 'PJ',
        ]);

        $this->jadwal = JadwalAsesmen::create([
            'event_id' => $event->id,
            'skema_id' => $skema->id,
            'tuk_id' => $tuk->id,
            'tanggal' => '2026-12-15',
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
            'no_pendaftaran' => 'PEND-UB-0001',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $skema->id,
            'jadwal_id' => $this->jadwal->id,
            'status' => Pendaftaran::STATUS_SELESAI,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function jawabanLengkap(string $jawaban = UmpanBalikAsesmen::JAWABAN_YA): array
    {
        $komponen = [];

        foreach (array_keys(UmpanBalikAsesmen::KOMPONEN) as $key) {
            $komponen[$key] = $jawaban;
        }

        return $komponen;
    }

    public function test_asesi_can_submit_feedback_after_decision(): void
    {
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.umpan-balik.store', $this->pendaftaran), [
                'komponen' => $this->jawabanLengkap(),
                'catatan' => 'Proses asesmen berjalan baik.',
            ])
            ->assertRedirect(route('asesi.umpan-balik.show', $this->pendaftaran))
            ->assertSessionHas('success');

        $umpanBalik = $this->pendaftaran->fresh()->umpanBalik;

        $this->assertNotNull($umpanBalik);
        $this->assertTrue($umpanBalik->isSubmitted());
        $this->assertSame($this->asesiUser->id, $umpanBalik->disubmit_oleh);
        $this->assertSame($this->institution->id, $umpanBalik->institution_id);
        $this->assertSame('Proses asesmen berjalan baik.', $umpanBalik->catatan);
        $this->assertCount(count(UmpanBalikAsesmen::KOMPONEN), $umpanBalik->komponen());
    }

    public function test_feedback_is_blocked_before_decision(): void
    {
        $this->pendaftaran->update(['status' => Pendaftaran::STATUS_ASESMEN_BERJALAN]);

        $this->actingAs($this->asesiUser)
            ->post(route('asesi.umpan-balik.store', $this->pendaftaran), [
                'komponen' => $this->jawabanLengkap(),
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('umpan_balik');

        $this->assertNull($this->pendaftaran->fresh()->umpanBalik);
    }

    public function test_all_components_are_required(): void
    {
        $komponen = $this->jawabanLengkap();
        unset($komponen['p5']);

        $this->actingAs($this->asesiUser)
            ->post(route('asesi.umpan-balik.store', $this->pendaftaran), [
                'komponen' => $komponen,
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('komponen.p5');

        $this->assertNull($this->pendaftaran->fresh()->umpanBalik);
    }

    public function test_feedback_cannot_be_submitted_twice(): void
    {
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.umpan-balik.store', $this->pendaftaran), [
                'komponen' => $this->jawabanLengkap(),
            ])
            ->assertSessionHas('success');

        $this->actingAs($this->asesiUser)
            ->post(route('asesi.umpan-balik.store', $this->pendaftaran), [
                'komponen' => $this->jawabanLengkap(UmpanBalikAsesmen::JAWABAN_TIDAK),
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('umpan_balik');

        $this->assertSame(
            UmpanBalikAsesmen::JAWABAN_YA,
            $this->pendaftaran->fresh()->umpanBalik->jawaban('p1'),
        );
    }

    public function test_other_asesi_cannot_submit_feedback(): void
    {
        $otherUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'aktif',
        ]);
        $otherUser->assignRole('asesi');
        $otherUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        Asesi::create([
            'user_id' => $otherUser->id,
            'no_registrasi' => '202611050056',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Medan',
            'tanggal_lahir' => '2000-01-01',
            'kebangsaan' => 'Indonesia',
            'alamat_rumah' => 'Jl. Lain No. 2',
            'pendidikan_terakhir' => 'D3',
            'status_kompetensi' => 'belum_asesmen',
        ]);

        $this->actingAs($otherUser)
            ->post(route('asesi.umpan-balik.store', $this->pendaftaran), [
                'komponen' => $this->jawabanLengkap(),
            ])
            ->assertForbidden();

        $this->assertNull($this->pendaftaran->fresh()->umpanBalik);
    }

    public function test_show_page_renders_form_before_submit_and_locks_after(): void
    {
        $this->actingAs($this->asesiUser)
            ->get(route('asesi.umpan-balik.show', $this->pendaftaran))
            ->assertOk()
            ->assertSee('Kirim Umpan Balik')
            ->assertSee('FR.AK.03');

        $this->actingAs($this->asesiUser)
            ->post(route('asesi.umpan-balik.store', $this->pendaftaran), [
                'komponen' => $this->jawabanLengkap(),
            ]);

        $this->actingAs($this->asesiUser)
            ->get(route('asesi.umpan-balik.show', $this->pendaftaran))
            ->assertOk()
            ->assertSee('tidak dapat diubah')
            ->assertDontSee('Kirim Umpan Balik');
    }

    public function test_asesi_pendaftaran_page_links_to_feedback_form(): void
    {
        $this->actingAs($this->asesiUser)
            ->get(route('asesi.pendaftaran.show', $this->pendaftaran))
            ->assertOk()
            ->assertSee(route('asesi.umpan-balik.show', $this->pendaftaran), false);
    }

    public function test_asesor_can_download_feedback_pdf(): void
    {
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.umpan-balik.store', $this->pendaftaran), [
                'komponen' => $this->jawabanLengkap(),
            ]);

        $response = $this->actingAs($this->asesorUser)
            ->get(route('asesor.umpan-balik.pdf', $this->pendaftaran));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_feedback_pdf_returns_404_before_submission(): void
    {
        $this->actingAs($this->asesorUser)
            ->get(route('asesor.umpan-balik.pdf', $this->pendaftaran))
            ->assertNotFound();
    }

    public function test_admin_can_download_feedback_pdf(): void
    {
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.umpan-balik.store', $this->pendaftaran), [
                'komponen' => $this->jawabanLengkap(),
            ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.pendaftaran.umpan-balik.pdf', $this->pendaftaran));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_asesor_mak02_shows_feedback_status(): void
    {
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.umpan-balik.store', $this->pendaftaran), [
                'komponen' => $this->jawabanLengkap(),
            ]);

        $this->actingAs($this->asesorUser)
            ->get(route('asesor.mak02.show', $this->pendaftaran))
            ->assertOk()
            ->assertSee('Umpan Balik Asesi (FR.AK.03)')
            ->assertSee(route('asesor.umpan-balik.pdf', $this->pendaftaran), false);
    }

    public function test_admin_pendaftaran_page_shows_feedback_card(): void
    {
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.umpan-balik.store', $this->pendaftaran), [
                'komponen' => $this->jawabanLengkap(),
            ]);

        $this->actingAs($this->admin)
            ->get(route('admin.pendaftaran.show', $this->pendaftaran))
            ->assertOk()
            ->assertSee('Umpan Balik Asesi (FR.AK.03)')
            ->assertSee(route('admin.pendaftaran.umpan-balik.pdf', $this->pendaftaran), false);
    }
}
