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
use App\Models\User;
use App\Support\SignatureImage;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SignatureDatabaseTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    private User $admin;

    private User $asesiUser;

    private Pendaftaran $pendaftaran;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->institution = Institution::create([
            'name' => 'LSP Tanda Tangan',
            'slug' => 'lsp-tanda-tangan',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);
        TenantContext::set($this->institution);

        $this->admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Admin Tanda Tangan',
            'status' => 'aktif',
        ]);
        $this->admin->assignRole('admin');
        $this->admin->institutions()->attach($this->institution->id, ['role' => 'admin']);

        $this->asesiUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Peserta Tanda Tangan',
            'status' => 'aktif',
        ]);
        $this->asesiUser->assignRole('asesi');
        $this->asesiUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        $asesi = Asesi::create([
            'user_id' => $this->asesiUser->id,
            'no_registrasi' => 'REG-SIGN',
            'jenis_kelamin' => 'L',
            'pendidikan_terakhir' => 'SMA',
        ]);

        $asesorUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesor Tanda Tangan',
            'status' => 'aktif',
        ]);
        $asesorUser->assignRole('asesor');
        $asesorUser->institutions()->attach($this->institution->id, ['role' => 'asesor']);
        $asesor = Asesor::create([
            'institution_id' => $this->institution->id,
            'user_id' => $asesorUser->id,
            'no_register' => 'ASR-SIGN',
        ]);

        $skema = Skema::create([
            'kode_skema' => 'SKM-SIGN',
            'nama_skema' => 'Skema Tanda Tangan',
            'jenis' => 'okupasi',
        ]);
        $tuk = Tuk::create([
            'kode_tuk' => 'TUK-SIGN',
            'nama_tuk' => 'TUK Tanda Tangan',
            'jenis' => 'mandiri',
            'penanggung_jawab' => 'PJ',
        ]);
        $event = EventAsesmen::create(['nama' => 'Event Sign', 'tahun' => 2026, 'periode' => 11]);
        $jadwal = JadwalAsesmen::create([
            'event_id' => $event->id,
            'skema_id' => $skema->id,
            'tuk_id' => $tuk->id,
            'tanggal' => '2026-12-06',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
            'kuota' => 10,
            'status' => 'terbuka',
        ]);
        JadwalAsesor::create([
            'institution_id' => $this->institution->id,
            'jadwal_id' => $jadwal->id,
            'asesor_id' => $asesor->id,
            'peran' => 'utama',
        ]);

        $this->pendaftaran = Pendaftaran::create([
            'no_pendaftaran' => 'REG-SIGN',
            'asesi_id' => $asesi->id,
            'skema_id' => $skema->id,
            'jadwal_id' => $jadwal->id,
            'status' => 'terjadwal',
        ]);

        $this->setSignature($this->asesiUser);
        $this->setSignature($asesorUser);
    }

    public function test_admin_can_store_signature_in_database(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.signature.store'), [
                'signature_source' => 'upload',
                'signature_file' => UploadedFile::fake()->image('signature.png', 100, 50),
            ]);

        $response->assertRedirect(route('admin.signature.show'));
        $this->admin->refresh();

        $this->assertNotNull($this->admin->signature_data);
        $this->assertSame('image/png', $this->admin->signature_mime);
        $this->assertNull($this->admin->signature_path);
        $this->assertStringStartsWith('iVBORw0KGgo', $this->admin->signature_data);
    }

    public function test_institution_signature_can_be_stored_in_database(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession([TenantContext::SESSION_ID => $this->institution->id])
            ->post(route('admin.institution.upload-signature', $this->institution), [
                'tanda_tangan_file' => UploadedFile::fake()->image('ketua.png', 100, 50),
            ]);

        $response->assertRedirect();
        $this->institution->refresh();

        $this->assertNotNull($this->institution->tanda_tangan_data);
        $this->assertSame('image/png', $this->institution->tanda_tangan_mime);
        $this->assertNull($this->institution->tanda_tangan_path);
    }

    public function test_pdf_renders_signature_from_database(): void
    {
        $this->setSignature($this->institution);
        $pendaftaran = $this->pendaftaran->load([
            'asesi.user',
            'skema',
            'jadwal.tuk',
            'jadwal.asesors.user',
        ]);

        $this->view('pdf.mak03', [
            'pendaftaran' => $pendaftaran,
            'hasil' => null,
            'institution' => $this->institution,
        ])
            ->assertSee('data:image/png;base64,', false)
            ->assertDontSee('/storage/signatures', false);
    }

    public function test_pdf_marks_missing_signature_explicitly(): void
    {
        $pendaftaran = $this->pendaftaran->load([
            'asesi.user',
            'skema',
            'jadwal.tuk',
            'jadwal.asesors.user',
        ]);

        $pendaftaran->asesi->user->forceFill([
            'signature_data' => null,
            'signature_mime' => null,
            'signature_hash' => null,
            'signature_uploaded_at' => null,
            'signature_path' => null,
        ])->save();

        $this->view('pdf.mak03', [
            'pendaftaran' => $pendaftaran,
            'hasil' => null,
            'institution' => $this->institution,
        ])
            ->assertSee('Tanda tangan belum tersimpan.');
    }

    private function setSignature(User|Institution $model): void
    {
        $image = imagecreatetruecolor(10, 10);
        ob_start();
        imagepng($image);
        $binary = ob_get_clean();
        imagedestroy($image);
        $payload = SignatureImage::encodePng($binary);

        if ($model instanceof User) {
            $model->forceFill([
                'signature_data' => $payload['data'],
                'signature_mime' => 'image/png',
                'signature_hash' => $payload['hash'],
                'signature_uploaded_at' => now(),
                'signature_path' => null,
            ])->save();
        } else {
            $model->forceFill([
                'tanda_tangan_data' => $payload['data'],
                'tanda_tangan_mime' => 'image/png',
                'tanda_tangan_hash' => $payload['hash'],
                'tanda_tangan_uploaded_at' => now(),
                'tanda_tangan_path' => null,
            ])->save();
        }
    }
}
