<?php

namespace Tests\Feature;

use App\Models\Asesi;
use App\Models\Asesor;
use App\Models\EventAsesmen;
use App\Models\Institution;
use App\Models\JadwalAsesmen;
use App\Models\JadwalAsesor;
use App\Models\Pendaftaran;
use App\Models\PendaftaranDokumen;
use App\Models\Skema;
use App\Models\SkemaPersyaratan;
use App\Models\Tuk;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Province;
use Tests\TestCase;

class PendaftaranTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    private User $admin;

    private User $asesiUser;

    private Asesi $asesi;

    private Skema $skema;

    private SkemaPersyaratan $syaratWajib;

    private SkemaPersyaratan $syaratOpsional;

    private EventAsesmen $event;

    private Tuk $tuk;

    private Asesor $asesor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->institution = Institution::create([
            'name' => 'LSP Pendaftaran',
            'slug' => 'lsp-pendaftaran',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);

        TenantContext::set($this->institution);

        Province::firstOrCreate(['code' => '31'], ['name' => 'DKI JAKARTA']);
        City::firstOrCreate(['code' => '3171'], ['province_code' => '31', 'name' => 'KOTA JAKARTA SELATAN']);

        $this->admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'aktif',
        ]);
        $this->admin->assignRole('admin');
        $this->admin->institutions()->attach($this->institution->id, ['role' => 'admin']);

        $this->asesiUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesi Pendaftaran',
            'email' => 'asesi.pendaftaran@example.test',
            'nik' => '3201123456780009',
            'phone' => '081234567890',
            'status' => 'aktif',
        ]);
        $this->asesiUser->assignRole('asesi');
        $this->asesiUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        $this->asesi = Asesi::create([
            'user_id' => $this->asesiUser->id,
            'no_registrasi' => '202610310001',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
            'kebangsaan' => 'Indonesia',
            'alamat_rumah' => 'Jl. Pendidikan No. 1',
            'provinsi_code' => '31',
            'kota_code' => '3171',
            'pendidikan_terakhir' => 'D3 Teknik Informatika',
        ]);

        $this->skema = Skema::create([
            'kode_skema' => 'SKM-DAFTAR',
            'nama_skema' => 'Skema Pendaftaran',
            'jenis' => 'okupasi',
            'biaya' => 0,
            'is_active' => true,
        ]);

        $this->syaratWajib = SkemaPersyaratan::create([
            'skema_id' => $this->skema->id,
            'nama_dokumen' => 'KTP',
            'is_required' => true,
            'urutan' => 1,
        ]);

        $this->syaratOpsional = SkemaPersyaratan::create([
            'skema_id' => $this->skema->id,
            'nama_dokumen' => 'Portofolio',
            'is_required' => false,
            'urutan' => 2,
        ]);

        $this->event = EventAsesmen::create([
            'nama' => 'Event Pendaftaran',
            'tahun' => 2026,
        ]);

        $this->tuk = Tuk::create([
            'kode_tuk' => 'TUK-DAFTAR',
            'nama_tuk' => 'TUK Pendaftaran',
            'jenis' => 'mandiri',
            'penanggung_jawab' => 'PJ',
        ]);

        $asesorUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'aktif',
        ]);
        $asesorUser->assignRole('asesor');
        $asesorUser->institutions()->attach($this->institution->id, ['role' => 'asesor']);

        $this->asesor = Asesor::create([
            'user_id' => $asesorUser->id,
            'no_register' => 'MET.000.000009 2026',
            'status' => 'aktif',
            'kebangsaan' => 'Indonesia',
        ]);
    }

    public function test_public_registration_creates_asesi_account_with_hashed_nik(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Calon Asesi Baru',
            'nik' => '3201123456780011',
            'email' => 'calon.asesi@example.test',
            'phone' => '081298765432',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '1999-05-05',
            'alamat_rumah' => 'Jl. Merdeka No. 2',
            'provinsi_code' => '31',
            'kota_code' => '3171',
            'pendidikan_terakhir' => 'S1 Informatika',
            'consent' => '1',
        ]);

        $response->assertRedirect(route('dashboard'));

        $user = User::where('email', 'calon.asesi@example.test')->firstOrFail();

        $this->assertTrue($user->hasRole('asesi'));
        $this->assertSame('3201123456780011', $user->nik);
        $this->assertSame(User::hashNik('3201123456780011'), $user->nik_hash);
        $this->assertDatabaseHas('asesis', [
            'user_id' => $user->id,
            'status_verifikasi' => 'belum_terverifikasi',
            'sumber' => 'mandiri',
        ]);
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_rejects_nik_that_is_not_16_digits(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'NIK Salah',
            'nik' => '12345',
            'email' => 'niksalah@example.test',
            'phone' => '081200000000',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1999-05-05',
            'alamat_rumah' => 'Jl. Merdeka',
            'provinsi_code' => '31',
            'kota_code' => '3171',
            'pendidikan_terakhir' => 'SMA',
            'consent' => '1',
        ]);

        $response->assertSessionHasErrors('nik');
        $this->assertGuest();
    }

    public function test_pendaftaran_is_blocked_until_profile_is_complete(): void
    {
        $incompleteUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'aktif',
        ]);
        $incompleteUser->assignRole('asesi');
        $incompleteUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        Asesi::create([
            'user_id' => $incompleteUser->id,
            'no_registrasi' => '202610310099',
        ]);

        $this->actingAs($incompleteUser)
            ->withSession([TenantContext::SESSION_ID => $this->institution->id])
            ->get(route('asesi.pendaftaran.create'))
            ->assertRedirect(route('asesi.profil.edit'))
            ->assertSessionHas('error');
    }

    public function test_missing_required_document_prevents_submission(): void
    {
        $pendaftaran = $this->createPendaftaran();

        $response = $this->asAsesi()->post(route('asesi.pendaftaran.submit', $pendaftaran));

        $response->assertSessionHasErrors('dokumen');
        $this->assertSame(Pendaftaran::STATUS_DRAF, $pendaftaran->fresh()->status);
    }

    public function test_document_upload_submission_verification_and_plotting_flow(): void
    {
        Storage::fake('local');

        $pendaftaran = $this->createPendaftaran();

        $upload = $this->asAsesi()->post(route('asesi.pendaftaran.dokumen.store', $pendaftaran), [
            'skema_persyaratan_id' => $this->syaratWajib->id,
            'file' => UploadedFile::fake()->create('ktp.pdf', 100, 'application/pdf'),
        ]);

        $upload->assertRedirect();
        $upload->assertSessionHas('success');

        $dokumen = PendaftaranDokumen::where('pendaftaran_id', $pendaftaran->id)->firstOrFail();
        $this->assertSame('menunggu', $dokumen->status);
        $this->assertStringStartsWith(
            'tenants/'.$this->institution->id.'/pendaftaran/'.$pendaftaran->id.'/',
            $dokumen->file_path,
        );
        Storage::disk('local')->assertExists($dokumen->file_path);

        $this->asAsesi()
            ->post(route('asesi.pendaftaran.submit', $pendaftaran))
            ->assertSessionHas('success');

        $this->assertSame(Pendaftaran::STATUS_DOKUMEN_DIAJUKAN, $pendaftaran->fresh()->status);

        $this->asAdmin()
            ->post(route('admin.pendaftaran.dokumen.verify', [$pendaftaran, $dokumen]), [
                'status' => 'disetujui',
            ])
            ->assertSessionHas('success');

        $this->assertSame(Pendaftaran::STATUS_MENUNGGU_JADWAL, $pendaftaran->fresh()->status);

        $jadwal = $this->createJadwalWithAsesor();

        $this->asAdmin()
            ->post(route('admin.pendaftaran.approve', $pendaftaran), [
                'jadwal_id' => $jadwal->id,
            ])
            ->assertSessionHas('success');

        $pendaftaran->refresh();
        $this->assertSame(Pendaftaran::STATUS_TERJADWAL, $pendaftaran->status);
        $this->assertSame($jadwal->id, $pendaftaran->jadwal_id);
        $this->assertSame($this->admin->id, $pendaftaran->disetujui_oleh);

        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $this->asesiUser->id,
            'tipe' => 'pendaftaran_disetujui',
        ]);
    }

    public function test_rejected_document_allows_asesi_to_reupload(): void
    {
        Storage::fake('local');

        $pendaftaran = $this->createPendaftaran();

        $this->asAsesi()->post(route('asesi.pendaftaran.dokumen.store', $pendaftaran), [
            'skema_persyaratan_id' => $this->syaratWajib->id,
            'file' => UploadedFile::fake()->create('ktp.pdf', 100, 'application/pdf'),
        ]);

        $this->asAsesi()->post(route('asesi.pendaftaran.submit', $pendaftaran));
        $dokumen = PendaftaranDokumen::where('pendaftaran_id', $pendaftaran->id)->firstOrFail();

        $this->asAdmin()
            ->post(route('admin.pendaftaran.dokumen.verify', [$pendaftaran, $dokumen]), [
                'status' => 'ditolak',
                'catatan' => 'Hasil pindai buram.',
            ])
            ->assertSessionHas('success');

        $this->assertSame(Pendaftaran::STATUS_DOKUMEN_DITOLAK, $pendaftaran->fresh()->status);

        $this->asAsesi()
            ->post(route('asesi.pendaftaran.dokumen.store', $pendaftaran), [
                'skema_persyaratan_id' => $this->syaratWajib->id,
                'file' => UploadedFile::fake()->create('ktp-baru.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHas('success');

        $this->assertSame('menunggu', $dokumen->fresh()->status);
    }

    public function test_plotting_requires_a_schedule_with_asesor(): void
    {
        $pendaftaran = $this->createPendaftaran(Pendaftaran::STATUS_MENUNGGU_JADWAL);

        $jadwal = JadwalAsesmen::create([
            'event_id' => $this->event->id,
            'skema_id' => $this->skema->id,
            'tuk_id' => $this->tuk->id,
            'tanggal' => '2026-11-10',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
            'kuota' => 5,
            'lingkup_asesor' => 'wilayah',
            'status' => 'terbuka',
        ]);

        $this->asAdmin()
            ->post(route('admin.pendaftaran.approve', $pendaftaran), ['jadwal_id' => $jadwal->id])
            ->assertSessionHas('error');

        $this->assertSame(Pendaftaran::STATUS_MENUNGGU_JADWAL, $pendaftaran->fresh()->status);
        $this->assertNull($pendaftaran->fresh()->jadwal_id);
    }

    public function test_asesi_cannot_access_another_tenant_pendaftaran(): void
    {
        $otherInstitution = Institution::create([
            'name' => 'LSP Pendaftaran Lain',
            'slug' => 'lsp-pendaftaran-lain',
            'is_active' => true,
        ]);

        TenantContext::set($otherInstitution);

        $otherUser = User::factory()->create([
            'institution_id' => $otherInstitution->id,
            'status' => 'aktif',
        ]);
        $otherAsesi = Asesi::create([
            'user_id' => $otherUser->id,
            'no_registrasi' => '202610319999',
        ]);
        $otherSkema = Skema::create([
            'kode_skema' => 'SKM-LAIN',
            'nama_skema' => 'Skema Lain',
            'jenis' => 'okupasi',
        ]);
        $otherPendaftaran = Pendaftaran::create([
            'no_pendaftaran' => '202610319999',
            'asesi_id' => $otherAsesi->id,
            'skema_id' => $otherSkema->id,
            'status' => Pendaftaran::STATUS_DRAF,
        ]);

        TenantContext::set($this->institution);

        $this->asAsesi()
            ->get(route('asesi.pendaftaran.show', $otherPendaftaran))
            ->assertNotFound();
    }

    public function test_asesi_can_render_pendaftaran_detail_page(): void
    {
        $pendaftaran = $this->createPendaftaran(Pendaftaran::STATUS_SELESAI);
        $pendaftaran->update(['jadwal_id' => $this->createJadwalWithAsesor()->id]);

        $this->asAsesi()
            ->get(route('asesi.pendaftaran.show', $pendaftaran))
            ->assertOk()
            ->assertSee('Status Pendaftaran')
            ->assertSee('Hasil Asesmen');
    }

    private function asAsesi(): static
    {
        return $this->actingAs($this->asesiUser)
            ->withSession([TenantContext::SESSION_ID => $this->institution->id]);
    }

    private function asAdmin(): static
    {
        return $this->actingAs($this->admin)
            ->withSession([TenantContext::SESSION_ID => $this->institution->id]);
    }

    private function createPendaftaran(string $status = Pendaftaran::STATUS_DRAF): Pendaftaran
    {
        return Pendaftaran::create([
            'no_pendaftaran' => '202610310001',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'status' => $status,
        ]);
    }

    private function createJadwalWithAsesor(): JadwalAsesmen
    {
        $jadwal = JadwalAsesmen::create([
            'event_id' => $this->event->id,
            'skema_id' => $this->skema->id,
            'tuk_id' => $this->tuk->id,
            'tanggal' => '2026-11-10',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
            'kuota' => 5,
            'lingkup_asesor' => 'wilayah',
            'status' => 'terbuka',
        ]);

        JadwalAsesor::create([
            'jadwal_id' => $jadwal->id,
            'asesor_id' => $this->asesor->id,
            'peran' => 'utama',
        ]);

        return $jadwal;
    }
}
