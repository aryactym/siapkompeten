<?php

namespace Tests\Feature;

use App\Models\Asesi;
use App\Models\Asesor;
use App\Models\EventAsesmen;
use App\Models\HasilAsesmen;
use App\Models\Institution;
use App\Models\JadwalAsesmen;
use App\Models\JadwalAsesor;
use App\Models\Pendaftaran;
use App\Models\Pleno;
use App\Models\Skema;
use App\Models\Tuk;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlenoAsesmenTest extends TestCase
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
            'name' => 'LSP Pleno',
            'slug' => 'lsp-pleno',
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
            'name' => 'Asesi Pleno',
            'email' => 'asesi.pleno@example.test',
            'nik' => '3201123456780100',
            'phone' => '081234567900',
            'status' => 'aktif',
        ]);
        $this->asesiUser->assignRole('asesi');
        $this->asesiUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        $this->asesi = Asesi::create([
            'user_id' => $this->asesiUser->id,
            'no_registrasi' => '202611050100',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '2000-01-01',
            'kebangsaan' => 'Indonesia',
            'alamat_rumah' => 'Jl. Pleno No. 1',
            'pendidikan_terakhir' => 'D3',
            'status_kompetensi' => 'kompeten',
        ]);

        $this->asesorUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesor Pleno',
            'email' => 'asesor.pleno@example.test',
            'status' => 'aktif',
        ]);
        $this->asesorUser->assignRole('asesor');
        $this->asesorUser->institutions()->attach($this->institution->id, ['role' => 'asesor']);

        $this->asesor = Asesor::create([
            'user_id' => $this->asesorUser->id,
            'no_register' => 'MET.000.000099 2026',
            'status' => 'aktif',
            'kebangsaan' => 'Indonesia',
        ]);

        $skema = Skema::create([
            'kode_skema' => 'SKM-PLN',
            'nama_skema' => 'Skema Pleno',
            'jenis' => 'okupasi',
            'biaya' => 0,
            'is_active' => true,
        ]);

        $event = EventAsesmen::create(['nama' => 'Event Pleno', 'tahun' => 2026]);
        $tuk = Tuk::create([
            'kode_tuk' => 'TUK-PLN',
            'nama_tuk' => 'TUK Pleno',
            'jenis' => 'mandiri',
            'penanggung_jawab' => 'PJ',
        ]);

        $this->jadwal = JadwalAsesmen::create([
            'event_id' => $event->id,
            'skema_id' => $skema->id,
            'tuk_id' => $tuk->id,
            'tanggal' => '2026-12-28',
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
            'no_pendaftaran' => 'PEND-PLN-0001',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $skema->id,
            'jadwal_id' => $this->jadwal->id,
            'status' => Pendaftaran::STATUS_SELESAI,
        ]);

        HasilAsesmen::create([
            'institution_id' => $this->institution->id,
            'pendaftaran_id' => $this->pendaftaran->id,
            'asesor_id' => $this->asesor->id,
            'rekomendasi' => HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN,
            'persen_kompeten' => 100,
            'umpan_balik_diberikan' => true,
            'status' => HasilAsesmen::STATUS_FINAL,
            'finalized_at' => now(),
            'finalized_by' => $this->asesorUser->id,
        ]);
    }

    public function test_admin_can_open_pleno_page(): void
    {
        $this->actingAs($this->admin)
            ->get(route('pleno.show', $this->jadwal))
            ->assertOk()
            ->assertSee('Berita Acara Pleno')
            ->assertSee('Simpan Pleno');
    }

    public function test_admin_can_save_and_finalize_pleno(): void
    {
        $this->actingAs($this->admin)
            ->post(route('pleno.store', $this->jadwal), [
                'nomor_ba' => 'BA-001',
                'keputusan' => 'disetujui',
                'peserta' => [
                    [
                        'pendaftaran_id' => $this->pendaftaran->id,
                        'keputusan' => 'kompeten',
                        'catatan' => 'OK',
                    ],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->actingAs($this->admin)
            ->post(route('pleno.final', $this->jadwal))
            ->assertRedirect()
            ->assertSessionHas('success');

        $pleno = Pleno::where('jadwal_id', $this->jadwal->id)->firstOrFail();

        $this->assertTrue($pleno->isFinal());
        $this->assertTrue($pleno->isApproved());
    }

    public function test_final_requires_keputusan(): void
    {
        $this->actingAs($this->admin)
            ->post(route('pleno.store', $this->jadwal), [
                'peserta' => [
                    [
                        'pendaftaran_id' => $this->pendaftaran->id,
                        'keputusan' => 'kompeten',
                    ],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->actingAs($this->admin)
            ->post(route('pleno.final', $this->jadwal))
            ->assertRedirect()
            ->assertSessionHasErrors('keputusan');
    }

    public function test_pdf_returns_404_before_pleno_is_created(): void
    {
        // ensure not created by show? show creates draf. Use direct query.
        Pleno::where('jadwal_id', $this->jadwal->id)->delete();

        $this->actingAs($this->admin)
            ->get(route('pleno.pdf', $this->jadwal))
            ->assertNotFound();
    }

    public function test_admin_can_download_pdf(): void
    {
        $this->actingAs($this->admin)
            ->post(route('pleno.store', $this->jadwal), [
                'keputusan' => 'disetujui',
                'peserta' => [
                    [
                        'pendaftaran_id' => $this->pendaftaran->id,
                        'keputusan' => 'kompeten',
                    ],
                ],
            ]);

        $response = $this->actingAs($this->admin)
            ->get(route('pleno.pdf', $this->jadwal));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
