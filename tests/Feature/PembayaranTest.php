<?php

namespace Tests\Feature;

use App\Models\Asesi;
use App\Models\Biaya;
use App\Models\Institution;
use App\Models\Pembayaran;
use App\Models\Pendaftaran;
use App\Models\RekeningBank;
use App\Models\Skema;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Province;
use Tests\TestCase;

class PembayaranTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    private User $admin;

    private User $asesiUser;

    private Asesi $asesi;

    private Skema $skema;

    private Pendaftaran $pendaftaran;

    private RekeningBank $rekening;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->institution = Institution::create([
            'name' => 'LSP Pembayaran',
            'slug' => 'lsp-pembayaran',
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
            'name' => 'Asesi Pembayaran',
            'email' => 'asesi.pembayaran@example.test',
            'nik' => '3201123456780011',
            'phone' => '081234567811',
            'status' => 'aktif',
        ]);
        $this->asesiUser->assignRole('asesi');
        $this->asesiUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        $this->asesi = Asesi::create([
            'user_id' => $this->asesiUser->id,
            'no_registrasi' => '202610310011',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
            'kebangsaan' => 'Indonesia',
            'alamat_rumah' => 'Jl. Pembayaran No. 1',
            'provinsi_code' => '31',
            'kota_code' => '3171',
            'pendidikan_terakhir' => 'D3 Teknik Informatika',
        ]);

        $this->skema = Skema::create([
            'kode_skema' => 'SKM-BAYAR',
            'nama_skema' => 'Skema Pembayaran',
            'jenis' => 'okupasi',
            'biaya' => 0,
            'is_active' => true,
        ]);

        Biaya::create([
            'skema_id' => $this->skema->id,
            'jenis_biaya' => 'Biaya Asesmen',
            'nominal' => 750000,
            'tanggung_jawab' => Biaya::TANGGUNG_PESERTA,
        ]);
        Biaya::create([
            'skema_id' => $this->skema->id,
            'jenis_biaya' => 'Biaya Sertifikasi BNSP',
            'nominal' => 1250000,
            'tanggung_jawab' => Biaya::TANGGUNG_PESERTA,
        ]);
        Biaya::create([
            'skema_id' => $this->skema->id,
            'jenis_biaya' => 'Biaya Sponsor',
            'nominal' => 500000,
            'tanggung_jawab' => Biaya::TANGGUNG_FASILITATOR,
        ]);

        $this->rekening = RekeningBank::create([
            'nama_bank' => 'Bank Mandiri',
            'no_rekening' => '1234567890',
            'atas_nama' => 'LSP SiapKompeten',
            'is_aktif' => true,
        ]);

        $this->pendaftaran = Pendaftaran::create([
            'no_pendaftaran' => 'PEND-2026-0001',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'status' => Pendaftaran::STATUS_MENUNGGU_PEMBAYARAN,
        ]);
    }

    public function test_admin_can_manage_rekening_bank(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.rekening.store'), [
                'nama_bank' => 'Bank BRI',
                'no_rekening' => '9876543210',
                'atas_nama' => 'LSP SiapKompeten',
                'is_aktif' => '1',
            ])
            ->assertRedirect(route('admin.rekening.index'))
            ->assertSessionHas('success');

        $rekening = RekeningBank::where('no_rekening', '9876543210')->firstOrFail();
        $this->assertTrue($rekening->is_aktif);
        $this->assertSame($this->institution->id, $rekening->institution_id);

        $this->actingAs($this->admin)
            ->put(route('admin.rekening.update', $rekening), [
                'nama_bank' => 'Bank BRI',
                'no_rekening' => '9876543210',
                'atas_nama' => 'LSP SiapKompeten Cabang Utama',
                'is_aktif' => '0',
            ])
            ->assertRedirect(route('admin.rekening.index'));

        $this->assertFalse($rekening->fresh()->is_aktif);

        // Nomor rekening wajib unik per lembaga.
        $this->actingAs($this->admin)
            ->from(route('admin.rekening.index'))
            ->post(route('admin.rekening.store'), [
                'nama_bank' => 'Bank Mandiri',
                'no_rekening' => '1234567890',
                'atas_nama' => 'LSP SiapKompeten',
            ])
            ->assertRedirect(route('admin.rekening.index'))
            ->assertSessionHasErrors('no_rekening');

        // Rekening yang belum digunakan dapat dihapus.
        $this->actingAs($this->admin)
            ->delete(route('admin.rekening.destroy', $rekening))
            ->assertRedirect(route('admin.rekening.index'))
            ->assertSessionHas('success');

        $this->assertNull(RekeningBank::find($rekening->id));

        // Rekening yang masih dipakai pembayaran tidak dapat dihapus.
        Pembayaran::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'total' => 2000000,
            'rekening_bank_id' => $this->rekening->id,
            'status' => Pembayaran::STATUS_MENUNGGU,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.rekening.destroy', $this->rekening))
            ->assertSessionHas('error');

        $this->assertNotNull(RekeningBank::find($this->rekening->id));
    }

    public function test_admin_can_manage_biaya_per_skema_with_formatted_nominal(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.biaya.store'), [
                'skema_id' => $this->skema->id,
                'jenis_biaya' => 'Biaya Administrasi',
                'nominal' => '1.500.000',
                'tanggung_jawab' => Biaya::TANGGUNG_PESERTA,
            ])
            ->assertSessionHas('success');

        $biaya = Biaya::where('jenis_biaya', 'Biaya Administrasi')->firstOrFail();
        $this->assertSame(1500000, (int) $biaya->nominal);

        // Jenis biaya unik per skema.
        $this->actingAs($this->admin)
            ->from(route('admin.biaya.index'))
            ->post(route('admin.biaya.store'), [
                'skema_id' => $this->skema->id,
                'jenis_biaya' => 'Biaya Asesmen',
                'nominal' => '100000',
                'tanggung_jawab' => Biaya::TANGGUNG_PESERTA,
            ])
            ->assertSessionHasErrors('jenis_biaya');

        $response = $this->actingAs($this->admin)->get(route('admin.biaya.index'));
        $response->assertOk()->assertSee('Biaya Administrasi');

        $this->actingAs($this->admin)
            ->delete(route('admin.biaya.destroy', $biaya))
            ->assertSessionHas('success');

        $this->assertNull(Biaya::find($biaya->id));
    }

    public function test_asesi_sees_tagihan_and_uploads_payment_proof(): void
    {
        Storage::fake('local');

        $response = $this->actingAs($this->asesiUser)
            ->get(route('asesi.pembayaran.show', $this->pendaftaran));

        $response->assertOk()
            ->assertSee('Biaya Asesmen')
            ->assertSee('Biaya Sertifikasi BNSP')
            ->assertSee('Bank Mandiri')
            ->assertSee(number_format(2000000, 0, ',', '.'));

        // Biaya fasilitator tidak ditagihkan ke peserta.
        $response->assertDontSee('Biaya Sponsor');

        $this->actingAs($this->asesiUser)
            ->post(route('asesi.pembayaran.store', $this->pendaftaran), [
                'rekening_bank_id' => $this->rekening->id,
                'tanggal_bayar' => now()->format('Y-m-d'),
                'catatan' => 'Transfer mobile banking',
                'bukti' => UploadedFile::fake()->create('bukti.pdf', 128, 'application/pdf'),
            ])
            ->assertRedirect(route('asesi.pembayaran.show', $this->pendaftaran))
            ->assertSessionHas('success');

        $pembayaran = Pembayaran::where('pendaftaran_id', $this->pendaftaran->id)->firstOrFail();

        $this->assertSame(Pembayaran::STATUS_MENUNGGU, $pembayaran->status);
        $this->assertSame(2000000, (int) $pembayaran->total);
        $this->assertSame(Pendaftaran::STATUS_PEMBAYARAN_DIAJUKAN, $this->pendaftaran->fresh()->status);
        Storage::disk('local')->assertExists($pembayaran->bukti_path);
    }

    public function test_admin_validates_payment_and_pendaftaran_moves_forward(): void
    {
        Storage::fake('local');

        $this->actingAs($this->asesiUser)
            ->post(route('asesi.pembayaran.store', $this->pendaftaran), [
                'rekening_bank_id' => $this->rekening->id,
                'tanggal_bayar' => now()->format('Y-m-d'),
                'bukti' => UploadedFile::fake()->create('bukti.jpg', 64, 'image/jpeg'),
            ])
            ->assertSessionHas('success');

        $pembayaran = Pembayaran::where('pendaftaran_id', $this->pendaftaran->id)->firstOrFail();

        // Tidak valid wajib menyertakan catatan.
        $this->actingAs($this->admin)
            ->from(route('admin.pembayaran.show', $pembayaran))
            ->post(route('admin.pembayaran.putuskan', $pembayaran), ['status' => 'tidak_valid'])
            ->assertSessionHasErrors('catatan');

        $this->actingAs($this->admin)
            ->post(route('admin.pembayaran.putuskan', $pembayaran), [
                'status' => 'tidak_valid',
                'catatan' => 'Bukti buram, unggah ulang.',
            ])
            ->assertRedirect(route('admin.pembayaran.show', $pembayaran))
            ->assertSessionHas('success');

        $pembayaran->refresh();
        $this->assertSame(Pembayaran::STATUS_TIDAK_VALID, $pembayaran->status);
        $this->assertSame(Pendaftaran::STATUS_MENUNGGU_PEMBAYARAN, $this->pendaftaran->fresh()->status);

        // Ajukan ulang setelah revisi.
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.pembayaran.store', $this->pendaftaran), [
                'rekening_bank_id' => $this->rekening->id,
                'tanggal_bayar' => now()->format('Y-m-d'),
                'bukti' => UploadedFile::fake()->create('bukti-baru.jpg', 64, 'image/jpeg'),
            ])
            ->assertSessionHas('success');

        $pembayaran->refresh();
        $this->assertSame(Pembayaran::STATUS_MENUNGGU, $pembayaran->status);

        $this->actingAs($this->admin)
            ->post(route('admin.pembayaran.putuskan', $pembayaran), ['status' => 'valid'])
            ->assertRedirect(route('admin.pembayaran.show', $pembayaran));

        $pembayaran->refresh();
        $this->assertSame(Pembayaran::STATUS_VALID, $pembayaran->status);
        $this->assertSame(Pendaftaran::STATUS_MENUNGGU_JADWAL, $this->pendaftaran->fresh()->status);
        $this->assertNotNull($pembayaran->divalidasi_pada);

        // Keputusan tidak boleh diubah setelah valid.
        $this->actingAs($this->admin)
            ->from(route('admin.pembayaran.show', $pembayaran))
            ->post(route('admin.pembayaran.putuskan', $pembayaran), [
                'status' => 'tidak_valid',
                'catatan' => 'Berubah pikiran.',
            ])
            ->assertSessionHas('error');
    }

    public function test_payment_pages_are_restricted_by_role(): void
    {
        $this->actingAs($this->asesiUser)
            ->get(route('admin.pembayaran.index'))
            ->assertForbidden();

        $this->actingAs($this->asesiUser)
            ->get(route('admin.rekening.index'))
            ->assertForbidden();

        // Asesi lain tidak boleh melihat tagihan pendaftaran ini.
        $otherUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'aktif',
        ]);
        $otherUser->assignRole('asesi');
        $otherUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        $this->actingAs($otherUser)
            ->get(route('asesi.pembayaran.show', $this->pendaftaran))
            ->assertForbidden();
    }
}
