<?php

namespace Tests\Feature;

use App\Models\Asesi;
use App\Models\EventAsesmen;
use App\Models\Institution;
use App\Models\JadwalAsesmen;
use App\Models\Pendaftaran;
use App\Models\Sertifikat;
use App\Models\Skema;
use App\Models\Tuk;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SertifikatBundleTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    private User $admin;

    private Skema $skema;

    private JadwalAsesmen $jadwal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->institution = Institution::create([
            'name' => 'LSP Sertifikat',
            'slug' => 'lsp-sertifikat',
            'is_active' => true,
            'is_setup_completed' => true,
            'nomor_lisensi' => 'BNSP-LSP-0001-ID',
        ]);

        TenantContext::set($this->institution);

        $this->admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'aktif',
        ]);
        $this->admin->assignRole('admin');
        $this->admin->institutions()->attach($this->institution->id, ['role' => 'admin']);

        $this->skema = Skema::create([
            'kode_skema' => 'SKM-CERT-01',
            'nama_skema' => 'Skema Sertifikasi Test',
            'jenis' => 'okupasi',
            'biaya' => 500000,
            'is_active' => true,
        ]);

        $event = EventAsesmen::create(['nama' => 'Event Sertifikat', 'tahun' => 2026]);
        $tuk = Tuk::create([
            'kode_tuk' => 'TUK-CERT',
            'nama_tuk' => 'TUK Test',
            'jenis' => 'mandiri',
            'penanggung_jawab' => 'PJ Test',
        ]);

        $this->jadwal = JadwalAsesmen::create([
            'event_id' => $event->id,
            'skema_id' => $this->skema->id,
            'tuk_id' => $tuk->id,
            'tanggal' => now()->subDays(7)->toDateString(),
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
            'kuota' => 10,
            'lingkup_asesor' => 'wilayah',
            'status' => 'selesai',
        ]);
    }

    /** Buat pendaftaran selesai + asesi kompeten */
    private function buatPesertaKompeten(string $namaSuffix = ''): Pendaftaran
    {
        $asesiUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesi '.$namaSuffix,
            'email' => 'asesi'.strtolower($namaSuffix).'@example.test',
            'nik' => '32010000000'.rand(10000, 99999),
            'status' => 'aktif',
        ]);
        $asesiUser->assignRole('asesi');
        $asesiUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        $asesi = Asesi::create([
            'user_id' => $asesiUser->id,
            'no_registrasi' => 'REG'.rand(100000, 999999),
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1995-01-01',
            'kebangsaan' => 'Indonesia',
            'alamat_rumah' => 'Jl. Test',
            'pendidikan_terakhir' => 'S1',
            'status_kompetensi' => 'kompeten',
        ]);

        return Pendaftaran::create([
            'no_pendaftaran' => 'PDN'.rand(100000, 999999),
            'asesi_id' => $asesi->id,
            'skema_id' => $this->skema->id,
            'jadwal_id' => $this->jadwal->id,
            'status' => Pendaftaran::STATUS_SELESAI,
        ]);
    }

    public function test_admin_can_view_bundle_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.sertifikat.bundle.index'));

        $response->assertOk();
    }

    public function test_bundle_index_shows_jadwal_with_eligible_peserta(): void
    {
        $this->buatPesertaKompeten('Satu');

        $response = $this->actingAs($this->admin)
            ->get(route('admin.sertifikat.bundle.index'));

        $response->assertOk();
        $response->assertSee('Skema Sertifikasi Test');
    }

    public function test_admin_can_view_bundle_create_form(): void
    {
        $this->buatPesertaKompeten('Dua');

        $response = $this->actingAs($this->admin)
            ->get(route('admin.sertifikat.bundle.create', $this->jadwal));

        $response->assertOk();
        $response->assertSee('Asesi Dua');
    }

    public function test_admin_can_publish_bundle_sertifikat(): void
    {
        $p1 = $this->buatPesertaKompeten('Tiga');
        $p2 = $this->buatPesertaKompeten('Empat');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.sertifikat.bundle.store', $this->jadwal), [
                'sertifikat' => [
                    ['pendaftaran_id' => $p1->id, 'no_seri_blangko' => 'BNSP-LSP.SKM.00001.2026'],
                    ['pendaftaran_id' => $p2->id, 'no_seri_blangko' => 'BNSP-LSP.SKM.00002.2026'],
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sertifikats', ['pendaftaran_id' => $p1->id, 'no_seri_blangko' => 'BNSP-LSP.SKM.00001.2026']);
        $this->assertDatabaseHas('sertifikats', ['pendaftaran_id' => $p2->id, 'no_seri_blangko' => 'BNSP-LSP.SKM.00002.2026']);

        $this->assertSame(2, Sertifikat::count());
    }

    public function test_bundle_rejects_duplicate_no_seri_blangko_in_same_request(): void
    {
        $p1 = $this->buatPesertaKompeten('Lima');
        $p2 = $this->buatPesertaKompeten('Enam');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.sertifikat.bundle.store', $this->jadwal), [
                'sertifikat' => [
                    ['pendaftaran_id' => $p1->id, 'no_seri_blangko' => 'BNSP-LSP.SKM.00003.2026'],
                    ['pendaftaran_id' => $p2->id, 'no_seri_blangko' => 'BNSP-LSP.SKM.00003.2026'],
                ],
            ]);

        $response->assertSessionHasErrors();
        $this->assertSame(0, Sertifikat::count());
    }

    public function test_bundle_rejects_invalid_bnsp_format(): void
    {
        $p1 = $this->buatPesertaKompeten('Tujuh');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.sertifikat.bundle.store', $this->jadwal), [
                'sertifikat' => [
                    ['pendaftaran_id' => $p1->id, 'no_seri_blangko' => 'FORMAT-SALAH'],
                ],
            ]);

        $response->assertSessionHasErrors();
        $this->assertSame(0, Sertifikat::count());
    }

    public function test_single_sertifikat_store_from_pendaftaran(): void
    {
        $pendaftaran = $this->buatPesertaKompeten('Delapan');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.sertifikat.store'), [
                'pendaftaran_id' => $pendaftaran->id,
                'no_seri_blangko' => 'BNSP-LSP.SKM.00010.2026',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sertifikats', [
            'pendaftaran_id' => $pendaftaran->id,
            'status' => Sertifikat::STATUS_AKTIF,
        ]);
    }

    public function test_cannot_issue_sertifikat_for_non_kompeten_asesi(): void
    {
        $asesiUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'email' => 'tidak.kompeten@example.test',
            'nik' => '3201111111111111',
            'status' => 'aktif',
        ]);
        $asesiUser->assignRole('asesi');
        $asesiUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        $asesi = Asesi::create([
            'user_id' => $asesiUser->id,
            'no_registrasi' => 'REG999999',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '1995-05-05',
            'kebangsaan' => 'Indonesia',
            'alamat_rumah' => 'Jl. Tidak Kompeten',
            'pendidikan_terakhir' => 'SMA',
            'status_kompetensi' => 'belum_kompeten',
        ]);

        $pendaftaran = Pendaftaran::create([
            'no_pendaftaran' => 'PDN999999',
            'asesi_id' => $asesi->id,
            'skema_id' => $this->skema->id,
            'jadwal_id' => $this->jadwal->id,
            'status' => Pendaftaran::STATUS_SELESAI,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.sertifikat.store'), [
                'pendaftaran_id' => $pendaftaran->id,
                'no_seri_blangko' => 'BNSP-LSP.SKM.00099.2026',
            ]);

        $response->assertSessionHasErrors('pendaftaran_id');
        $this->assertSame(0, Sertifikat::count());
    }

    public function test_asesi_can_view_their_own_sertifikat(): void
    {
        $pendaftaran = $this->buatPesertaKompeten('Sembilan');

        $sertifikat = Sertifikat::create([
            'institution_id' => $this->institution->id,
            'pendaftaran_id' => $pendaftaran->id,
            'asesi_id' => $pendaftaran->asesi_id,
            'skema_id' => $this->skema->id,
            'no_seri_blangko' => 'BNSP-LSP.SKM.00011.2026',
            'no_registrasi' => 'BNSP-LSP-0001-ID.SKM-CERT-01.00001.2026',
            'tanggal_terbit' => now(),
            'status' => Sertifikat::STATUS_AKTIF,
            'diterbitkan_oleh' => $this->admin->id,
        ]);

        $asesiUser = $pendaftaran->asesi->user;

        $response = $this->actingAs($asesiUser)
            ->get(route('asesi.sertifikat.index'));

        $response->assertOk();
        $response->assertSee('BNSP-LSP-0001-ID.SKM-CERT-01.00001.2026');
    }
}
