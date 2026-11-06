<?php

namespace Tests\Feature;

use App\Models\Asesi;
use App\Models\Asesor;
use App\Models\EventAsesmen;
use App\Models\Institution;
use App\Models\JadwalAsesmen;
use App\Models\JadwalAsesor;
use App\Models\Pendaftaran;
use App\Models\Pleno;
use App\Models\PlenoPeserta;
use App\Models\Skema;
use App\Models\Tuk;
use App\Models\User;
use App\Models\ValidasiAsesmen;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidasiAsesmenTest extends TestCase
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
            'name' => 'LSP Validasi',
            'slug' => 'lsp-validasi',
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
            'name' => 'Asesi Validasi',
            'email' => 'asesi.validasi@example.test',
            'nik' => '3201123456780099',
            'phone' => '081234567899',
            'status' => 'aktif',
        ]);
        $this->asesiUser->assignRole('asesi');
        $this->asesiUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        $this->asesi = Asesi::create([
            'user_id' => $this->asesiUser->id,
            'no_registrasi' => '202611050099',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
            'kebangsaan' => 'Indonesia',
            'alamat_rumah' => 'Jl. Validasi No. 1',
            'pendidikan_terakhir' => 'D3',
            'status_kompetensi' => 'kompeten',
        ]);

        $this->asesorUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesor Validasi',
            'email' => 'asesor.validasi@example.test',
            'status' => 'aktif',
        ]);
        $this->asesorUser->assignRole('asesor');
        $this->asesorUser->institutions()->attach($this->institution->id, ['role' => 'asesor']);

        $this->asesor = Asesor::create([
            'user_id' => $this->asesorUser->id,
            'no_register' => 'MET.000.000088 2026',
            'status' => 'aktif',
            'kebangsaan' => 'Indonesia',
        ]);

        $skema = Skema::create([
            'kode_skema' => 'SKM-VAL',
            'nama_skema' => 'Skema Validasi',
            'jenis' => 'okupasi',
            'biaya' => 0,
            'is_active' => true,
        ]);

        $event = EventAsesmen::create(['nama' => 'Event Validasi', 'tahun' => 2026]);
        $tuk = Tuk::create([
            'kode_tuk' => 'TUK-VAL',
            'nama_tuk' => 'TUK Validasi',
            'jenis' => 'mandiri',
            'penanggung_jawab' => 'PJ',
        ]);

        $this->jadwal = JadwalAsesmen::create([
            'event_id' => $event->id,
            'skema_id' => $skema->id,
            'tuk_id' => $tuk->id,
            'tanggal' => '2026-12-25',
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
            'no_pendaftaran' => 'PEND-VAL-0001',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $skema->id,
            'jadwal_id' => $this->jadwal->id,
            'status' => Pendaftaran::STATUS_SELESAI,
        ]);
    }

    public function test_admin_can_open_validasi_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('validasi.show', $this->pendaftaran))
            ->assertOk()
            ->assertSee('FR.VA')
            ->assertSee('Simpan Validasi');
    }

    public function test_admin_can_save_and_finalize_validasi(): void
    {
        $this->actingAs($this->admin)
            ->post(route('validasi.store', $this->pendaftaran), [
                'aspek_validitas' => 'Bukti valid.',
                'keputusan' => ValidasiAsesmen::KEPUTUSAN_DISETUJUI,
                'catatan' => 'OK.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->actingAs($this->admin)
            ->post(route('validasi.final', $this->pendaftaran))
            ->assertRedirect()
            ->assertSessionHas('success');

        $validasi = ValidasiAsesmen::where('pendaftaran_id', $this->pendaftaran->id)->firstOrFail();

        $this->assertTrue($validasi->isFinal());
        $this->assertTrue($validasi->isApproved());
    }

    public function test_final_requires_keputusan(): void
    {
        $this->actingAs($this->admin)
            ->post(route('validasi.store', $this->pendaftaran), [
                'aspek_validitas' => 'Bukti valid.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->actingAs($this->admin)
            ->post(route('validasi.final', $this->pendaftaran))
            ->assertRedirect()
            ->assertSessionHasErrors('keputusan');
    }

    public function test_pdf_returns_404_before_validasi_is_created(): void
    {
        $this->actingAs($this->admin)
            ->get(route('validasi.pdf', $this->pendaftaran))
            ->assertNotFound();
    }

    public function test_admin_can_download_pdf(): void
    {
        $this->actingAs($this->admin)
            ->post(route('validasi.store', $this->pendaftaran), [
                'keputusan' => ValidasiAsesmen::KEPUTUSAN_DISETUJUI,
            ]);

        $response = $this->actingAs($this->admin)
            ->get(route('validasi.pdf', $this->pendaftaran));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_sertifikat_issuance_is_blocked_without_approved_validasi(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.sertifikat.store'), [
                'pendaftaran_id' => $this->pendaftaran->id,
                'no_seri_blangko' => 'BNSP-LSP.SKM.00001.2026',
            ]);

        $response->assertSessionHasErrors('pendaftaran_id');
    }

    public function test_sertifikat_issuance_is_allowed_with_approved_validasi(): void
    {
        ValidasiAsesmen::create([
            'institution_id' => $this->institution->id,
            'pendaftaran_id' => $this->pendaftaran->id,
            'validator_id' => $this->admin->id,
            'keputusan' => ValidasiAsesmen::KEPUTUSAN_DISETUJUI,
            'status' => ValidasiAsesmen::STATUS_FINAL,
            'finalized_at' => now(),
            'finalized_by' => $this->admin->id,
        ]);

        Pleno::firstOrCreate(
            ['jadwal_id' => $this->jadwal->id],
            [
                'institution_id' => $this->institution->id,
                'keputusan' => Pleno::KEPUTUSAN_DISETUJUI,
                'status' => Pleno::STATUS_FINAL,
                'finalized_at' => now(),
                'finalized_by' => $this->admin->id,
            ],
        );
        PlenoPeserta::firstOrCreate(
            ['pleno_id' => Pleno::where('jadwal_id', $this->jadwal->id)->first()->id, 'pendaftaran_id' => $this->pendaftaran->id],
            [
                'institution_id' => $this->institution->id,
                'keputusan' => PlenoPeserta::KEPUTUSAN_KOMPETEN,
            ],
        );

        $response = $this->actingAs($this->admin)
            ->post(route('admin.sertifikat.store'), [
                'pendaftaran_id' => $this->pendaftaran->id,
                'no_seri_blangko' => 'BNSP-LSP.SKM.00002.2026',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sertifikats', [
            'pendaftaran_id' => $this->pendaftaran->id,
        ]);
    }
}
