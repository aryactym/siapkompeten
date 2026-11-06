<?php

namespace Tests\Feature;

use App\Models\Asesi;
use App\Models\Asesor;
use App\Models\ElemenKompetensi;
use App\Models\EventAsesmen;
use App\Models\HasilAsesmen;
use App\Models\Institution;
use App\Models\JadwalAsesmen;
use App\Models\KriteriaUnjukKerja;
use App\Models\Pendaftaran;
use App\Models\PersetujuanAsesmen;
use App\Models\RencanaAsesmen;
use App\Models\RencanaAsesmenUnit;
use App\Models\Skema;
use App\Models\Tuk;
use App\Models\UnitKompetensi;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RencanaAsesmenTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    private User $asesorUser;

    private Asesor $asesor;

    private User $asesiUser;

    private Asesi $asesi;

    private Skema $skema;

    private UnitKompetensi $unit;

    private KriteriaUnjukKerja $kuk1;

    private ElemenKompetensi $elemen;

    private Pendaftaran $pendaftaran;

    private JadwalAsesmen $jadwal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->institution = Institution::create([
            'name' => 'LSP Rencana',
            'slug' => 'lsp-rencana',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);

        TenantContext::set($this->institution);

        $this->asesorUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesor Rencana',
            'email' => 'asesor.rencana@example.test',
            'status' => 'aktif',
        ]);
        $this->asesorUser->assignRole('asesor');
        $this->asesorUser->institutions()->attach($this->institution->id, ['role' => 'asesor']);

        $this->asesor = Asesor::create([
            'user_id' => $this->asesorUser->id,
            'no_register' => 'MET.000.000021 2026',
            'status' => 'aktif',
            'kebangsaan' => 'Indonesia',
        ]);

        $this->asesiUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesi Rencana',
            'email' => 'asesi.rencana@example.test',
            'nik' => '3201123456780033',
            'status' => 'aktif',
        ]);
        $this->asesiUser->assignRole('asesi');
        $this->asesiUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        $this->asesi = Asesi::create([
            'user_id' => $this->asesiUser->id,
            'no_registrasi' => '202610310033',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
            'kebangsaan' => 'Indonesia',
            'alamat_rumah' => 'Jl. Rencana No. 1',
            'pendidikan_terakhir' => 'D3 Teknik Informatika',
            'status_kompetensi' => 'belum_asesmen',
        ]);

        $this->skema = Skema::create([
            'kode_skema' => 'SKM-RNC',
            'nama_skema' => 'Skema Rencana',
            'jenis' => 'okupasi',
            'biaya' => 0,
            'is_active' => true,
            'institution_id' => $this->institution->id,
        ]);

        $this->unit = UnitKompetensi::create([
            'institution_id' => $this->institution->id,
            'kode_unit' => 'U-RNC-01',
            'judul_unit' => 'Unit Rencana Contoh',
            'deskripsi' => 'Deskripsi unit contoh.',
        ]);

        $this->elemen = ElemenKompetensi::create([
            'institution_id' => $this->institution->id,
            'unit_kompetensi_id' => $this->unit->id,
            'urutan' => 1,
            'nama_elemen' => 'Elemen Rencana Contoh',
        ]);

        $this->kuk1 = KriteriaUnjukKerja::create([
            'institution_id' => $this->institution->id,
            'elemen_kompetensi_id' => $this->elemen->id,
            'urutan' => 1,
            'pernyataan_kuk' => 'KUK Rencana 1',
        ]);

        // Jadwal dan pendaftaran
        $event = EventAsesmen::create([
            'institution_id' => $this->institution->id,
            'nama' => 'Event Rencana',
            'tahun' => now()->year,
            'tanggal_mulai' => now()->addDays(7),
            'tanggal_selesai' => now()->addDays(7),
            'status' => 'aktif',
        ]);

        $tuk = Tuk::create([
            'institution_id' => $this->institution->id,
            'kode_tuk' => 'TUK-RNC',
            'nama_tuk' => 'TUK Rencana',
            'alamat' => 'Alamat TUK',
            'penanggung_jawab' => 'PJ TUK',
            'is_active' => true,
        ]);

        $this->jadwal = JadwalAsesmen::create([
            'institution_id' => $this->institution->id,
            'event_id' => $event->id,
            'skema_id' => $this->skema->id,
            'tuk_id' => $tuk->id,
            'tanggal' => now()->addDays(7),
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '16:00:00',
            'kuota' => 10,
            'status' => 'terbuka',
        ]);

        $this->jadwal->asesors()->attach($this->asesor->id, [
            'id' => (string) Str::uuid(),
            'institution_id' => $this->institution->id,
            'peran' => 'utama',
        ]);

        $this->pendaftaran = Pendaftaran::create([
            'no_pendaftaran' => 'PEND-RNC-0001',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'jadwal_id' => $this->jadwal->id,
            'status' => Pendaftaran::STATUS_TERJADWAL,
        ]);
    }

    public function test_asesor_can_create_and_finalize_rencana(): void
    {
        $this->actingAs($this->asesorUser)
            ->get(route('asesor.rencana-asesmen.create', $this->pendaftaran))
            ->assertOk()
            ->assertSee('Buat Rencana Asesmen');

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.rencana-asesmen.store', $this->pendaftaran), [
                'pendekatan' => 'observasi',
                'tujuan' => 'Mengevaluasi keterampilan asesi.',
                'ruang_lingkup' => 'Semua unit kompetensi dalam skema.',
                'tanggal_asesmen' => now()->addDays(7)->format('Y-m-d'),
                'catatan' => 'Catatan rencana.',
            ])
            ->assertSessionHas('success');

        $rencana = RencanaAsesmen::where('pendaftaran_id', $this->pendaftaran->id)->first();
        $this->assertNotNull($rencana);
        $this->assertSame('draf', $rencana->status);

        $this->actingAs($this->asesorUser)
            ->get(route('asesor.rencana-asesmen.edit', $rencana))
            ->assertOk();

        // Tambah unit dan peta instrumen, lalu final
        $this->actingAs($this->asesorUser)
            ->put(route('asesor.rencana-asesmen.update', $rencana), [
                'pendekatan' => 'observasi',
                'tujuan' => 'Mengevaluasi keterampilan asesi.',
                'ruang_lingkup' => 'Semua unit kompetensi dalam skema.',
                'tanggal_asesmen' => now()->addDays(7)->format('Y-m-d'),
                'unit_ids' => [$this->unit->id],
                'units' => [
                    $this->unit->id => [
                        'kuks' => [$this->kuk1->id],
                        'jenis_instrumen' => 'observasi',
                        'metode' => 'observasi',
                        'bukti' => 'Bukti contoh',
                    ],
                ],
            ])
            ->assertRedirect(route('asesor.rencana-asesmen.edit', $rencana))
            ->assertSessionHas('success');

        $rencana->refresh();
        $this->assertSame('draf', $rencana->status);
        $this->assertSame(1, RencanaAsesmenUnit::where('rencana_asesmen_id', $rencana->id)->count());
        $this->assertSame(1, $rencana->units()->count());

        // Finalisasi
        $this->actingAs($this->asesorUser)
            ->post(route('asesor.rencana-asesmen.final', $rencana))
            ->assertRedirect(route('asesor.rencana-asesmen.edit', $rencana))
            ->assertSessionHas('success');

        $this->assertTrue($rencana->fresh()->isFinal());
    }

    public function test_asesi_cannot_access_rencana(): void
    {
        $rencana = RencanaAsesmen::create([
            'institution_id' => $this->institution->id,
            'pendaftaran_id' => $this->pendaftaran->id,
            'asesor_id' => $this->asesor->id,
            'pendekatan' => 'observasi',
            'tujuan' => 'Tujuan.',
            'ruang_lingkup' => 'Lingkup.',
            'tanggal_asesmen' => now()->addDays(7),
            'status' => 'draf',
        ]);

        $this->actingAs($this->asesiUser)
            ->get(route('asesor.rencana-asesmen.edit', $rencana))
            ->assertForbidden();
    }

    public function test_mak02_final_blocked_if_rencana_not_final(): void
    {
        // Buat pendaftaran baru tanpa rencana
        $pendaftaran = Pendaftaran::create([
            'no_pendaftaran' => 'PEND-RNC-0002',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'jadwal_id' => $this->jadwal->id,
            'status' => Pendaftaran::STATUS_TERJADWAL,
        ]);

        // Setujui APL-02 dan pre-asesmen agar hanya rencana yang menjadi hambatan
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.apl02.serah', $pendaftaran), [
                'elemen_approval' => [$this->elemen->id => true],
            ])
            ->assertSessionHas('success');

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.apl02.verifikasi', $pendaftaran), [
                'verifikasi' => [
                    $this->kuk1->id => ['valid' => '1', 'autentik' => '1', 'terkini' => '1', 'memadai' => '1'],
                ],
                'bukti' => [],
            ])
            ->assertSessionHas('success');

        foreach (PersetujuanAsesmen::requiredJenis() as $jenis) {
            PersetujuanAsesmen::create([
                'institution_id' => $this->institution->id,
                'pendaftaran_id' => $pendaftaran->id,
                'jenis' => $jenis,
                'status' => PersetujuanAsesmen::STATUS_DISETUJUI,
                'disetujui_pada' => now(),
                'disetujui_oleh' => $this->asesiUser->id,
            ]);
        }

        // Finalisasi MAK-02 harus ditolak karena rencana belum ada
        $this->actingAs($this->asesorUser)
            ->post(route('asesor.mak02.final', $pendaftaran), [
                'rekomendasi' => HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN,
                'umpan_balik' => '1',
                'konfirmasi' => '1',
            ])
            ->assertSessionHasErrors('hasil');
    }

    public function test_mak02_final_allowed_if_rencana_final(): void
    {
        $pendaftaran = Pendaftaran::create([
            'no_pendaftaran' => 'PEND-RNC-0003',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'jadwal_id' => $this->jadwal->id,
            'status' => Pendaftaran::STATUS_TERJADWAL,
        ]);

        $rencana = RencanaAsesmen::create([
            'institution_id' => $this->institution->id,
            'pendaftaran_id' => $pendaftaran->id,
            'asesor_id' => $this->asesor->id,
            'pendekatan' => 'observasi',
            'tujuan' => 'Tujuan.',
            'ruang_lingkup' => 'Lingkup.',
            'tanggal_asesmen' => now()->addDays(7),
            'status' => RencanaAsesmen::STATUS_FINAL,
        ]);
        RencanaAsesmenUnit::create([
            'institution_id' => $this->institution->id,
            'rencana_asesmen_id' => $rencana->id,
            'unit_kompetensi_id' => $this->unit->id,
            'urutan' => 1,
        ]);

        $this->actingAs($this->asesiUser)
            ->post(route('asesi.apl02.serah', $pendaftaran), [
                'elemen_approval' => [$this->elemen->id => true],
            ])
            ->assertSessionHas('success');

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.apl02.verifikasi', $pendaftaran), [
                'verifikasi' => [
                    $this->kuk1->id => ['valid' => '1', 'autentik' => '1', 'terkini' => '1', 'memadai' => '1'],
                ],
                'bukti' => [],
            ])
            ->assertSessionHas('success');

        foreach (PersetujuanAsesmen::requiredJenis() as $jenis) {
            PersetujuanAsesmen::create([
                'institution_id' => $this->institution->id,
                'pendaftaran_id' => $pendaftaran->id,
                'jenis' => $jenis,
                'status' => PersetujuanAsesmen::STATUS_DISETUJUI,
                'disetujui_pada' => now(),
                'disetujui_oleh' => $this->asesiUser->id,
            ]);
        }

        HasilAsesmen::create([
            'institution_id' => $this->institution->id,
            'pendaftaran_id' => $pendaftaran->id,
            'asesor_id' => $this->asesor->id,
            'metode_default' => ['observasi'],
            'status' => 'draf',
        ]);

        // Finalisasi MAK-02 sekarang diizinkan (rencana sudah final)
        $this->actingAs($this->asesorUser)
            ->post(route('asesor.mak02.final', $pendaftaran), [
                'rekomendasi' => HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN,
                'umpan_balik' => '1',
                'konfirmasi' => '1',
            ])
            ->assertSessionHas('success');

        $this->assertTrue($pendaftaran->fresh()->hasilAsesmen?->isFinal());
    }

    public function test_asesor_can_see_asesi_persetujuan_data(): void
    {
        $pendaftaran = Pendaftaran::create([
            'no_pendaftaran' => 'PEND-RNC-0004',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'jadwal_id' => $this->jadwal->id,
            'status' => Pendaftaran::STATUS_TERJADWAL,
        ]);

        foreach (PersetujuanAsesmen::requiredJenis() as $jenis) {
            PersetujuanAsesmen::create([
                'institution_id' => $this->institution->id,
                'pendaftaran_id' => $pendaftaran->id,
                'jenis' => $jenis,
                'status' => PersetujuanAsesmen::STATUS_MENUNGGU,
            ]);
        }

        // Asesi menyetujui AK.07 dengan data isian lengkap
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.pre-asesmen.approve', $pendaftaran), [
                'setuju_ak07' => '1',
                'tanggal' => now()->format('Y-m-d'),
                'acuan' => 'SKKNI 124 Tahun 2022',
                'metode' => 'Observasi',
                'instrumen' => 'FR.IA.02',
                'potensi' => [1],
                'p1_perlu' => 'ya',
                'p1_lainnya' => 'Keterangan contoh',
            ])
            ->assertRedirect(route('asesi.pre-asesmen.show', $pendaftaran))
            ->assertSessionHas('success');

        $persetujuan = $pendaftaran->fresh()->persetujuanAsesmens->where('jenis', 'ak07')->first();
        $this->assertTrue($persetujuan->isApproved());
        $this->assertSame('SKKNI 124 Tahun 2022', $persetujuan->data_json['acuan'] ?? null);
        $this->assertSame('Observasi', $persetujuan->data_json['metode'] ?? null);
        $this->assertContains(1, $persetujuan->data_json['potensi'] ?? []);

        // Asesor membuka halaman pre-asesmen — data harus muncul (tidak error)
        $this->actingAs($this->asesorUser)
            ->get(route('asesor.pre-asesmen.show', $pendaftaran))
            ->assertOk()
            ->assertSee('SKKNI 124 Tahun 2022')
            ->assertSee('Observasi')
            ->assertSee('FR.IA.02')
            ->assertSee('Keterangan contoh');

        // PDF gabungan hanya boleh mencentang satu pilihan Ya/Tidak per baris.
        $pendaftaran->load(['skema', 'jadwal.tuk', 'asesi.user', 'persetujuanAsesmens']);

        $html = view('pdf.pre-asesmen-gabungan', [
            'pendaftaran' => $pendaftaran,
            'data' => $persetujuan->data_json,
            'persetujuan' => $persetujuan,
            'skema' => $pendaftaran->skema,
            'jadwal' => $pendaftaran->jadwal,
            'asesiUser' => $pendaftaran->asesi->user,
        ])->render();

        foreach (range(1, 8) as $baris) {
            $ya = preg_match('/name="p'.$baris.'_perlu" value="ya" disabled\s+checked/', $html) === 1;
            $tidak = preg_match('/name="p'.$baris.'_perlu" value="tidak" disabled\s+checked/', $html) === 1;

            $this->assertFalse($ya && $tidak, "Baris {$baris} menampilkan Ya dan Tidak tercentang sekaligus.");
        }

        $this->assertMatchesRegularExpression('/name="p1_perlu" value="ya" disabled\s+checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/name="p1_perlu" value="tidak" disabled\s+checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/name="p2_perlu" value="(ya|tidak)" disabled\s+checked/', $html);
    }
}
