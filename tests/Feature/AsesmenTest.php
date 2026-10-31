<?php

namespace Tests\Feature;

use App\Models\Apl02Penilaian;
use App\Models\Asesi;
use App\Models\Asesor;
use App\Models\ElemenKompetensi;
use App\Models\EventAsesmen;
use App\Models\HasilAsesmen;
use App\Models\Institution;
use App\Models\JadwalAsesmen;
use App\Models\JadwalAsesor;
use App\Models\KriteriaUnjukKerja;
use App\Models\Pendaftaran;
use App\Models\Skema;
use App\Models\SkemaUnit;
use App\Models\Tuk;
use App\Models\UnitKompetensi;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsesmenTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    private User $admin;

    private User $asesiUser;

    private Asesi $asesi;

    private User $asesorUser;

    private Asesor $asesor;

    private Skema $skema;

    private UnitKompetensi $unit;

    private KriteriaUnjukKerja $kuk1;

    private KriteriaUnjukKerja $kuk2;

    private Pendaftaran $pendaftaran;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->institution = Institution::create([
            'name' => 'LSP Asesmen',
            'slug' => 'lsp-asesmen',
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
            'name' => 'Asesi Asesmen',
            'email' => 'asesi.asesmen@example.test',
            'nik' => '3201123456780022',
            'phone' => '081234567822',
            'status' => 'aktif',
        ]);
        $this->asesiUser->assignRole('asesi');
        $this->asesiUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        $this->asesi = Asesi::create([
            'user_id' => $this->asesiUser->id,
            'no_registrasi' => '202610310022',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
            'kebangsaan' => 'Indonesia',
            'alamat_rumah' => 'Jl. Asesmen No. 1',
            'pendidikan_terakhir' => 'D3 Teknik Informatika',
            'status_kompetensi' => 'belum_asesmen',
        ]);

        $this->asesorUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesor Asesmen',
            'email' => 'asesor.asesmen@example.test',
            'status' => 'aktif',
        ]);
        $this->asesorUser->assignRole('asesor');
        $this->asesorUser->institutions()->attach($this->institution->id, ['role' => 'asesor']);

        $this->asesor = Asesor::create([
            'user_id' => $this->asesorUser->id,
            'no_register' => 'MET.000.000011 2026',
            'status' => 'aktif',
            'kebangsaan' => 'Indonesia',
        ]);

        $this->skema = Skema::create([
            'kode_skema' => 'SKM-ASM',
            'nama_skema' => 'Skema Asesmen',
            'jenis' => 'okupasi',
            'biaya' => 0,
            'is_active' => true,
        ]);

        $this->unit = UnitKompetensi::create([
            'kode_unit' => 'UK-001',
            'judul_unit' => 'Melakukan Pengujian',
            'deskripsi' => 'Unit kompetensi pengujian',
            'is_active' => true,
        ]);

        SkemaUnit::create([
            'skema_id' => $this->skema->id,
            'unit_kompetensi_id' => $this->unit->id,
            'jenis' => 'inti',
            'urutan' => 1,
        ]);

        $elemen = ElemenKompetensi::create([
            'unit_kompetensi_id' => $this->unit->id,
            'urutan' => 1,
            'nama_elemen' => 'Elemen Pengujian',
        ]);

        $this->kuk1 = KriteriaUnjukKerja::create([
            'elemen_kompetensi_id' => $elemen->id,
            'urutan' => 1,
            'pernyataan_kuk' => 'Mampu menyusun rencana uji',
        ]);

        $this->kuk2 = KriteriaUnjukKerja::create([
            'elemen_kompetensi_id' => $elemen->id,
            'urutan' => 2,
            'pernyataan_kuk' => 'Mampu melaksanakan uji kompetensi',
        ]);

        $event = EventAsesmen::create(['nama' => 'Event Asesmen', 'tahun' => 2026]);
        $tuk = Tuk::create([
            'kode_tuk' => 'TUK-ASM',
            'nama_tuk' => 'TUK Asesmen',
            'jenis' => 'mandiri',
            'penanggung_jawab' => 'PJ',
        ]);

        $jadwal = JadwalAsesmen::create([
            'event_id' => $event->id,
            'skema_id' => $this->skema->id,
            'tuk_id' => $tuk->id,
            'tanggal' => '2026-12-05',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
            'kuota' => 10,
            'lingkup_asesor' => 'wilayah',
            'status' => 'terbuka',
        ]);

        JadwalAsesor::create([
            'jadwal_id' => $jadwal->id,
            'asesor_id' => $this->asesor->id,
            'peran' => 'utama',
        ]);

        $this->pendaftaran = Pendaftaran::create([
            'no_pendaftaran' => 'PEND-ASM-0001',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'jadwal_id' => $jadwal->id,
            'status' => Pendaftaran::STATUS_TERJADWAL,
        ]);
    }

    private function jawabanPayload(): array
    {
        return [
            'jawaban' => [$this->kuk1->id => '1', $this->kuk2->id => '1'],
            'bukti' => [$this->kuk1->id => 'Sertifikat pelatihan', $this->kuk2->id => 'Portofolio proyek'],
        ];
    }

    public function test_asesi_fills_and_submits_apl02(): void
    {
        $this->actingAs($this->asesiUser)
            ->get(route('asesi.apl02.show', $this->pendaftaran))
            ->assertOk()
            ->assertSee('Mampu menyusun rencana uji');

        // Penyerahan gagal jika ada KUK yang belum dijawab.
        $this->actingAs($this->asesiUser)
            ->from(route('asesi.apl02.show', $this->pendaftaran))
            ->post(route('asesi.apl02.serah', $this->pendaftaran), [
                'jawaban' => [$this->kuk1->id => '1'],
                'bukti' => [],
            ])
            ->assertRedirect(route('asesi.apl02.show', $this->pendaftaran))
            ->assertSessionHasErrors('apl02');

        $this->actingAs($this->asesiUser)
            ->post(route('asesi.apl02.store', $this->pendaftaran), $this->jawabanPayload())
            ->assertRedirect(route('asesi.apl02.show', $this->pendaftaran))
            ->assertSessionHas('success');

        $penilaian = Apl02Penilaian::where('pendaftaran_id', $this->pendaftaran->id)->firstOrFail();
        $this->assertSame(Apl02Penilaian::STATUS_DRAF_ASESI, $penilaian->status);
        $this->assertSame(2, $penilaian->jawabans()->count());
        $this->assertTrue($penilaian->jawabans()->first()->jawaban_asesi);

        $this->actingAs($this->asesiUser)
            ->post(route('asesi.apl02.serah', $this->pendaftaran), $this->jawabanPayload())
            ->assertRedirect(route('asesi.apl02.show', $this->pendaftaran))
            ->assertSessionHas('success');

        $this->assertSame(Apl02Penilaian::STATUS_DISERAHKAN, $penilaian->fresh()->status);
    }

    public function test_asesor_verifies_apl02_and_role_is_restricted(): void
    {
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.apl02.serah', $this->pendaftaran), $this->jawabanPayload())
            ->assertSessionHas('success');

        // Asesor lain yang tidak ditugaskan ditolak.
        $asesorLainUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'aktif',
        ]);
        $asesorLainUser->assignRole('asesor');
        $asesorLainUser->institutions()->attach($this->institution->id, ['role' => 'asesor']);

        $this->actingAs($asesorLainUser)
            ->get(route('asesor.apl02.show', $this->pendaftaran))
            ->assertForbidden();

        $this->actingAs($this->asesorUser)
            ->get(route('asesor.apl02.show', $this->pendaftaran))
            ->assertOk()
            ->assertSee('Mampu menyusun rencana uji');

        $verifikasi = [
            'verifikasi' => [
                $this->kuk1->id => ['valid' => '1', 'autentik' => '1', 'terkini' => '1', 'memadai' => '1'],
                $this->kuk2->id => ['valid' => '1', 'autentik' => '1', 'terkini' => '1', 'memadai' => '1'],
            ],
            'bukti' => [$this->kuk1->id => 'Diperiksa', $this->kuk2->id => 'Diperiksa'],
        ];

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.apl02.verifikasi', $this->pendaftaran), $verifikasi)
            ->assertRedirect(route('asesor.apl02.show', $this->pendaftaran))
            ->assertSessionHas('success');

        $penilaian = Apl02Penilaian::where('pendaftaran_id', $this->pendaftaran->id)->firstOrFail();
        $this->assertSame(Apl02Penilaian::STATUS_DIVERIFIKASI, $penilaian->status);
        $this->assertTrue($penilaian->jawabans()->first()->semuaBuktiAsesor());

        // Asesi tidak boleh membuka halaman verifikasi asesor.
        $this->actingAs($this->asesiUser)
            ->get(route('asesor.apl02.show', $this->pendaftaran))
            ->assertForbidden();
    }

    public function test_asesor_inputs_and_finalizes_mak02(): void
    {
        $payload = [
            'units' => [
                $this->unit->id => [
                    'keputusan' => 'kompeten',
                    'pencapaian' => '1',
                    'metode' => [
                        'observasi' => [
                            'digunakan' => '1',
                            'bukti_langsung' => '1',
                            'bukti_tidak_langsung' => '0',
                            'bukti_tambahan' => '0',
                            'pencapaian' => '1',
                            'keputusan' => 'kompeten',
                        ],
                    ],
                ],
            ],
            'kuks' => [
                $this->kuk1->id => ['pencapaian' => '1', 'bukti_no' => 'B-001', 'valid' => '1', 'autentik' => '1', 'terkini' => '1', 'memadai' => '1'],
                $this->kuk2->id => ['pencapaian' => '1', 'bukti_no' => 'B-002', 'valid' => '1', 'autentik' => '1', 'terkini' => '1', 'memadai' => '1'],
            ],
        ];

        $this->actingAs($this->asesorUser)
            ->get(route('asesor.mak02.show', $this->pendaftaran))
            ->assertOk()
            ->assertSee('Melakukan Pengujian');

        // Finalisasi diblokir sampai APL-02 diverifikasi.
        $this->actingAs($this->asesorUser)
            ->from(route('asesor.mak02.show', $this->pendaftaran))
            ->post(route('asesor.mak02.final', $this->pendaftaran), [
                'rekomendasi' => HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN,
                'konfirmasi' => '1',
            ])
            ->assertForbidden();

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.mak02.store', $this->pendaftaran), $payload)
            ->assertRedirect(route('asesor.mak02.show', $this->pendaftaran))
            ->assertSessionHas('success');

        $hasil = HasilAsesmen::where('pendaftaran_id', $this->pendaftaran->id)->firstOrFail();
        $this->assertSame(2, $hasil->kuks()->count());
        $this->assertSame(1, $hasil->units()->count());
        $this->assertSame(1, $hasil->units()->first()->metodes()->count());

        // APL-02 diverifikasi lebih dulu sebelum MAK-02 dapat difinalkan.
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.apl02.serah', $this->pendaftaran), $this->jawabanPayload())
            ->assertSessionHas('success');

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.apl02.verifikasi', $this->pendaftaran), [
                'verifikasi' => [
                    $this->kuk1->id => ['valid' => '1', 'autentik' => '1', 'terkini' => '1', 'memadai' => '1'],
                    $this->kuk2->id => ['valid' => '1', 'autentik' => '1', 'terkini' => '1', 'memadai' => '1'],
                ],
                'bukti' => [],
            ])
            ->assertSessionHas('success');

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.mak02.final', $this->pendaftaran), [
                'rekomendasi' => HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN,
                'umpan_balik' => '1',
                'konfirmasi' => '1',
            ])
            ->assertRedirect(route('asesor.mak02.show', $this->pendaftaran))
            ->assertSessionHas('success');

        $hasil->refresh();
        $this->assertSame(HasilAsesmen::STATUS_FINAL, $hasil->status);
        $this->assertSame(100.0, (float) $hasil->persen_kompeten);
        $this->assertSame(HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN, $hasil->rekomendasi);
        $this->assertSame(Pendaftaran::STATUS_SELESAI, $this->pendaftaran->fresh()->status);
        $this->assertSame('kompeten', $this->asesi->fresh()->status_kompetensi);

        // Data terkunci: penyimpanan ulang draf ditolak.
        $this->actingAs($this->asesorUser)
            ->from(route('asesor.mak02.show', $this->pendaftaran))
            ->post(route('asesor.mak02.store', $this->pendaftaran), $payload)
            ->assertSessionHasErrors('hasil');
    }

    public function test_apl02_and_mak02_can_be_printed_as_pdf(): void
    {
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.apl02.serah', $this->pendaftaran), $this->jawabanPayload())
            ->assertSessionHas('success');

        $responseApl02 = $this->actingAs($this->asesiUser)->get(route('asesi.apl02.pdf', $this->pendaftaran));
        $responseApl02->assertOk();
        $this->assertStringStartsWith('application/pdf', (string) $responseApl02->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', (string) $responseApl02->getContent());

        $responseMak02 = $this->actingAs($this->asesorUser)->get(route('asesor.mak02.pdf', $this->pendaftaran));
        $responseMak02->assertOk();
        $this->assertStringStartsWith('application/pdf', (string) $responseMak02->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', (string) $responseMak02->getContent());
    }
}
