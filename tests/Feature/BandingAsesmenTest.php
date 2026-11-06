<?php

namespace Tests\Feature;

use App\Models\Asesi;
use App\Models\Asesor;
use App\Models\Banding;
use App\Models\EventAsesmen;
use App\Models\HasilAsesmen;
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

class BandingAsesmenTest extends TestCase
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
            'name' => 'LSP Banding',
            'slug' => 'lsp-banding',
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
            'name' => 'Asesi Banding',
            'email' => 'asesi.banding@example.test',
            'nik' => '3201123456780088',
            'phone' => '081234567888',
            'status' => 'aktif',
        ]);
        $this->asesiUser->assignRole('asesi');
        $this->asesiUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        $this->asesi = Asesi::create([
            'user_id' => $this->asesiUser->id,
            'no_registrasi' => '202611050088',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2000-01-01',
            'kebangsaan' => 'Indonesia',
            'alamat_rumah' => 'Jl. Banding No. 1',
            'pendidikan_terakhir' => 'D3',
            'status_kompetensi' => 'belum_kompeten',
        ]);

        $this->asesorUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesor Banding',
            'email' => 'asesor.banding@example.test',
            'status' => 'aktif',
        ]);
        $this->asesorUser->assignRole('asesor');
        $this->asesorUser->institutions()->attach($this->institution->id, ['role' => 'asesor']);

        $this->asesor = Asesor::create([
            'user_id' => $this->asesorUser->id,
            'no_register' => 'MET.000.000077 2026',
            'status' => 'aktif',
            'kebangsaan' => 'Indonesia',
        ]);

        $skema = Skema::create([
            'kode_skema' => 'SKM-BND',
            'nama_skema' => 'Skema Banding',
            'jenis' => 'okupasi',
            'biaya' => 0,
            'is_active' => true,
        ]);

        $event = EventAsesmen::create(['nama' => 'Event Banding', 'tahun' => 2026]);
        $tuk = Tuk::create([
            'kode_tuk' => 'TUK-BND',
            'nama_tuk' => 'TUK Banding',
            'jenis' => 'mandiri',
            'penanggung_jawab' => 'PJ',
        ]);

        $this->jadwal = JadwalAsesmen::create([
            'event_id' => $event->id,
            'skema_id' => $skema->id,
            'tuk_id' => $tuk->id,
            'tanggal' => '2026-12-22',
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
            'no_pendaftaran' => 'PEND-BND-0001',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $skema->id,
            'jadwal_id' => $this->jadwal->id,
            'status' => Pendaftaran::STATUS_SELESAI,
        ]);
    }

    public function test_asesi_can_submit_banding_after_decision(): void
    {
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.banding.store', $this->pendaftaran), [
                'alasan' => 'Saya tidak setuju dengan penilaian KUK nomor 2.',
            ])
            ->assertRedirect(route('asesi.banding.show', $this->pendaftaran))
            ->assertSessionHas('success');

        $banding = $this->pendaftaran->fresh()->banding;

        $this->assertNotNull($banding);
        $this->assertSame(Banding::STATUS_DIAJUKAN, $banding->status);
        $this->assertSame($this->asesiUser->id, $banding->pengaju_id);
        $this->assertSame($this->institution->id, $banding->institution_id);
        $this->assertCount(1, $banding->histories);
    }

    public function test_banding_is_blocked_before_decision(): void
    {
        $this->pendaftaran->update(['status' => Pendaftaran::STATUS_ASESMEN_BERJALAN]);

        $this->actingAs($this->asesiUser)
            ->post(route('asesi.banding.store', $this->pendaftaran), [
                'alasan' => 'Alasan banding.',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('banding');

        $this->assertNull($this->pendaftaran->fresh()->banding);
    }

    public function test_alasan_is_required(): void
    {
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.banding.store', $this->pendaftaran), [
                'alasan' => '',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('alasan');

        $this->assertNull($this->pendaftaran->fresh()->banding);
    }

    public function test_banding_cannot_be_submitted_twice_while_open(): void
    {
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.banding.store', $this->pendaftaran), [
                'alasan' => 'Alasan pertama.',
            ])
            ->assertSessionHas('success');

        $this->actingAs($this->asesiUser)
            ->post(route('asesi.banding.store', $this->pendaftaran), [
                'alasan' => 'Alasan kedua.',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('banding');
    }

    public function test_other_asesi_cannot_submit_banding(): void
    {
        $otherUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'aktif',
        ]);
        $otherUser->assignRole('asesi');
        $otherUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        Asesi::create([
            'user_id' => $otherUser->id,
            'no_registrasi' => '202611050089',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2000-01-01',
            'kebangsaan' => 'Indonesia',
            'alamat_rumah' => 'Jl. Lain No. 2',
            'pendidikan_terakhir' => 'D3',
            'status_kompetensi' => 'belum_asesmen',
        ]);

        $this->actingAs($otherUser)
            ->post(route('asesi.banding.store', $this->pendaftaran), [
                'alasan' => 'Alasan banding.',
            ])
            ->assertForbidden();

        $this->assertNull($this->pendaftaran->fresh()->banding);
    }

    public function test_asesi_pendaftaran_page_links_to_banding_form(): void
    {
        $this->actingAs($this->asesiUser)
            ->get(route('asesi.pendaftaran.show', $this->pendaftaran))
            ->assertOk()
            ->assertSee(route('asesi.banding.show', $this->pendaftaran), false);
    }

    public function test_asesor_can_review_banding(): void
    {
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.banding.store', $this->pendaftaran), [
                'alasan' => 'Alasan banding.',
            ]);

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.banding.review', $this->pendaftaran), [
                'status' => Banding::STATUS_DITERIMA,
                'keputusan' => 'Banding diterima; akan diuji ulang pada unit terkait.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $banding = $this->pendaftaran->fresh()->banding;

        $this->assertSame(Banding::STATUS_DITERIMA, $banding->status);
        $this->assertSame('Banding diterima; akan diuji ulang pada unit terkait.', $banding->keputusan);
        $this->assertSame($this->asesorUser->id, $banding->ditinjau_oleh);
        $this->assertCount(2, $banding->histories);
    }

    public function test_review_requires_decision_for_accepted_or_rejected(): void
    {
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.banding.store', $this->pendaftaran), [
                'alasan' => 'Alasan banding.',
            ]);

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.banding.review', $this->pendaftaran), [
                'status' => Banding::STATUS_DITOLAK,
                'keputusan' => '',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('keputusan');

        $this->assertSame(
            Banding::STATUS_DIAJUKAN,
            $this->pendaftaran->fresh()->banding->status,
        );
    }

    public function test_accepted_banding_reopens_asesmen(): void
    {
        HasilAsesmen::create([
            'institution_id' => $this->institution->id,
            'pendaftaran_id' => $this->pendaftaran->id,
            'asesor_id' => $this->asesor->id,
            'rekomendasi' => HasilAsesmen::REKOMENDASI_TIDAK,
            'persen_kompeten' => 60,
            'umpan_balik_diberikan' => true,
            'status' => HasilAsesmen::STATUS_FINAL,
            'finalized_at' => now(),
            'finalized_by' => $this->asesorUser->id,
        ]);

        $this->actingAs($this->asesiUser)
            ->post(route('asesi.banding.store', $this->pendaftaran), [
                'alasan' => 'Alasan banding.',
            ]);

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.banding.review', $this->pendaftaran), [
                'status' => Banding::STATUS_DITERIMA,
                'keputusan' => 'Diterima; uji ulang.',
            ]);

        $pendaftaran = $this->pendaftaran->fresh();

        $this->assertSame(Pendaftaran::STATUS_ASESMEN_BERJALAN, $pendaftaran->status);
        $this->assertSame(
            HasilAsesmen::STATUS_DRAF,
            HasilAsesmen::where('pendaftaran_id', $pendaftaran->id)->first()->status,
        );
    }

    public function test_show_page_renders_banding_details_for_asesor(): void
    {
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.banding.store', $this->pendaftaran), [
                'alasan' => 'Alasan banding.',
            ]);

        $this->actingAs($this->asesorUser)
            ->get(route('asesor.banding.show', $this->pendaftaran))
            ->assertOk()
            ->assertSee('Alasan banding.')
            ->assertSee('Tinjau Banding');
    }

    public function test_asesi_can_download_pdf(): void
    {
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.banding.store', $this->pendaftaran), [
                'alasan' => 'Alasan banding.',
            ]);

        $response = $this->actingAs($this->asesiUser)
            ->get(route('asesi.banding.pdf', $this->pendaftaran));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_asesor_can_download_pdf(): void
    {
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.banding.store', $this->pendaftaran), [
                'alasan' => 'Alasan banding.',
            ]);

        $response = $this->actingAs($this->asesorUser)
            ->get(route('asesor.banding.pdf', $this->pendaftaran));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_can_download_pdf(): void
    {
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.banding.store', $this->pendaftaran), [
                'alasan' => 'Alasan banding.',
            ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.pendaftaran.banding.pdf', $this->pendaftaran));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_pdf_returns_404_before_banding_is_submitted(): void
    {
        $this->actingAs($this->asesorUser)
            ->get(route('asesor.banding.pdf', $this->pendaftaran))
            ->assertNotFound();
    }

    public function test_admin_pendaftaran_page_shows_banding_card(): void
    {
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.banding.store', $this->pendaftaran), [
                'alasan' => 'Alasan banding.',
            ]);

        $this->actingAs($this->admin)
            ->get(route('admin.pendaftaran.show', $this->pendaftaran))
            ->assertOk()
            ->assertSee('Banding (FR.AK.04)')
            ->assertSee(route('admin.pendaftaran.banding.pdf', $this->pendaftaran), false);
    }

    public function test_asesor_peserta_page_links_to_banding(): void
    {
        $this->actingAs($this->asesorUser)
            ->get(route('asesor.jadwal.peserta', $this->jadwal))
            ->assertOk()
            ->assertSee(route('asesor.banding.show', $this->pendaftaran), false);
    }
}
