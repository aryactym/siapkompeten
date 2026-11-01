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

    private ElemenKompetensi $elemen;

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
        $this->elemen = $elemen;

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

    private function elemenPayload(): array
    {
        return [
            'elemen_approval' => [$this->elemen->id => '1'],
            'bukti' => [$this->elemen->id => 'Sertifikat pelatihan'],
        ];
    }

    /**
     * Extract tag <input> yang mengandung atribut id tertentu, dari '<input' sampai '>' pertama.
     * Mencegah false-positive cek 'checked' dari elemen lain.
     */
    private function extractInputTag(string $html, string $id): string
    {
        // Cari atribut id="..." milik <input> (bukan for="..." pada <label>).
        $needle = 'id="'.$id.'"';
        $pos = strpos($html, $needle);
        $this->assertNotFalse($pos, "Input dengan atribut {$needle} tidak ditemukan di HTML");

        // Cari '<input' sebelum atribut id, lalu '>' sesudahnya.
        $start = strrpos(substr($html, 0, $pos), '<input');
        $end = strpos($html, '>', $pos);

        return substr($html, $start, $end - $start + 1);
    }

    public function test_asesi_fills_and_submits_apl02(): void
    {
        $this->actingAs($this->asesiUser)
            ->get(route('asesi.apl02.show', $this->pendaftaran))
            ->assertOk()
            ->assertSee('Mampu menyusun rencana uji');

        // Penyimpanan draf lalu penyerahan (per elemen: approval elemen menyatakan seluruh KUK).
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.apl02.store', $this->pendaftaran), $this->elemenPayload())
            ->assertRedirect(route('asesi.apl02.show', $this->pendaftaran))
            ->assertSessionHas('success');

        $penilaian = Apl02Penilaian::where('pendaftaran_id', $this->pendaftaran->id)->firstOrFail();
        $this->assertSame(Apl02Penilaian::STATUS_DRAF_ASESI, $penilaian->status);
        $this->assertSame(2, $penilaian->jawabans()->count());
        $this->assertTrue($penilaian->jawabans()->first()->jawaban_asesi);

        // Regression: setelah simpan draf, GET segar harus restore radio "Ya, mampu semua" dari DB.
        $html = $this->actingAs($this->asesiUser)
            ->get(route('asesi.apl02.show', $this->pendaftaran))
            ->assertOk()
            ->getContent();
        $pos = strpos($html, 'name="elemen_approval['.$this->elemen->id.']" value="1"');
        $this->assertNotFalse($pos, 'Radio elemen_approval harus dirender');
        $this->assertStringContainsString('checked', substr($html, $pos, 400), 'Radio "Ya, mampu semua" harus ter-check dari DB');

        $this->actingAs($this->asesiUser)
            ->post(route('asesi.apl02.serah', $this->pendaftaran), $this->elemenPayload())
            ->assertRedirect(route('asesi.apl02.show', $this->pendaftaran))
            ->assertSessionHas('success');

        $this->assertSame(Apl02Penilaian::STATUS_DISERAHKAN, $penilaian->fresh()->status);
    }

    public function test_asesor_verifies_apl02_and_role_is_restricted(): void
    {
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.apl02.serah', $this->pendaftaran), $this->elemenPayload())
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
            ->assertSee('Mampu menyusun rencana uji')
            // Regression: kunci form verifikasi wajib kuk_id, bukan elemen_id (FK apl02_jawabans).
            ->assertSee('name="verifikasi['.$this->kuk1->id.'][valid]"', false)
            ->assertDontSee('name="verifikasi['.$this->elemen->id.'][valid]"', false);

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
            'metode_default' => ['observasi'],
            'elemenTercapai' => [$this->elemen->id => '1'],
            'elemenCatatan' => [$this->elemen->id => null],
            'elemen4C' => [
                $this->elemen->id => ['valid' => '1', 'autentik' => '1', 'terkini' => '1', 'memadai' => '1'],
            ],            'units' => [
                $this->unit->id => [
                    'keputusan' => 'kompeten',
                    'catatan' => null,
                    'metode' => [
                        'observasi' => [
                            'digunakan' => '1',
                            'bukti_langsung' => '1',
                            'bukti_tidak_langsung' => '0',
                            'bukti_tambahan' => '0',
                            'keputusan' => 'kompeten',
                            'catatan' => null,
                        ],
                    ],
                ],
            ],
        ];

        $this->actingAs($this->asesorUser)
            ->get(route('asesor.mak02.show', $this->pendaftaran))
            ->assertOk()
            ->assertSee('Melakukan Pengujian')
            ->assertSee('Semua Tercapai')
            ->assertSee('semuaTercapai()');

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

        // Regression: GET segar harus restore radio "Semua tercapai" (value=1) dari DB.
        // Bug keyBy+only() Eloquent Collection menyebabkan semua elemen tampil "belum dinilai".
        // Extract tag input spesifik (id=elemen-ya-...) sampai '>' pertama — jangan pakai
        // substring lebar karena radio "belum dinilai" (value="") memang default checked.
        $html = $this->actingAs($this->asesorUser)
            ->get(route('asesor.mak02.show', $this->pendaftaran))
            ->assertOk()
            ->getContent();
        $tagYa = $this->extractInputTag($html, 'elemen-ya-'.$this->elemen->id);
        $this->assertStringContainsString('checked', $tagYa, 'Radio "Semua tercapai" (value=1) harus ter-check dari DB');

        // APL-02 diverifikasi lebih dulu sebelum MAK-02 dapat difinalkan.
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.apl02.serah', $this->pendaftaran), $this->elemenPayload())
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

    public function test_asesor_simpan_mak02_metode_tidak_digunakan(): void
    {
        $payload = [
            'metode_default' => ['observasi'],
            'elemenTercapai' => [$this->elemen->id => '1'],
            'elemenCatatan' => [$this->elemen->id => null],
            'elemen4C' => [
                $this->elemen->id => ['valid' => '1', 'autentik' => '1', 'terkini' => '1', 'memadai' => '1'],
            ],
            'units' => [
                $this->unit->id => [
                    'keputusan' => 'kompeten',
                    'catatan' => null,
                    'metode' => [
                        'observasi' => [
                            'digunakan' => '0',
                            'bukti_langsung' => '0',
                            'bukti_tidak_langsung' => '0',
                            'bukti_tambahan' => '0',
                            'keputusan' => null,
                            'catatan' => null,
                        ],
                    ],
                ],
            ],
        ];

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.mak02.store', $this->pendaftaran), $payload)
            ->assertRedirect(route('asesor.mak02.show', $this->pendaftaran))
            ->assertSessionHas('success');

        $hasil = HasilAsesmen::where('pendaftaran_id', $this->pendaftaran->id)->firstOrFail();
        $hasilUnit = $hasil->units()->first();
        $this->assertSame('kompeten', $hasilUnit->keputusan);
        $this->assertCount(1, $hasilUnit->metodes);
        $this->assertFalse($hasilUnit->metodes->first()->digunakan);
    }

    public function test_asesor_simpan_draf_mak02_dan_tanpa_unit_keputusan(): void
    {
        // Penilaian per elemen (mis. "Semua tercapai") harus tersimpan
        // walau unit keputusan belum diisi — final tetap mewajibkan nanti.
        $payload = [
            'metode_default' => ['observasi'],
            'elemenTercapai' => [$this->elemen->id => '1'],
            'elemenCatatan' => [$this->elemen->id => null],
            'elemen4C' => [
                $this->elemen->id => ['valid' => '1', 'autentik' => '1', 'terkini' => '1', 'memadai' => '1'],
            ],
        ];

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.mak02.store', $this->pendaftaran), $payload)
            ->assertRedirect(route('asesor.mak02.show', $this->pendaftaran))
            ->assertSessionHas('success');

        $hasil = HasilAsesmen::where('pendaftaran_id', $this->pendaftaran->id)->firstOrFail();
        $this->assertSame(2, $hasil->kuks()->count());
        $this->assertSame(true, $hasil->kuks()->first()->tercapai);
    }

    public function test_asesor_mak02_refresh_shows_tercapai_terpilih(): void
    {
        $payload = [
            'metode_default' => ['observasi'],
            'elemenTercapai' => [$this->elemen->id => '1'],
            'elemenCatatan' => [$this->elemen->id => null],
            'elemen4C' => [
                $this->elemen->id => ['valid' => '1', 'autentik' => '1', 'terkini' => '1', 'memadai' => '1'],
            ],
            'units' => [
                $this->unit->id => [
                    'keputusan' => 'kompeten',
                    'catatan' => null,
                    'metode' => [
                        'observasi' => [
                            'digunakan' => '1',
                            'bukti_langsung' => '1',
                            'bukti_tidak_langsung' => '0',
                            'bukti_tambahan' => '0',
                            'keputusan' => 'kompeten',
                            'catatan' => null,
                        ],
                    ],
                ],
            ],
        ];

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.mak02.store', $this->pendaftaran), $payload)
            ->assertRedirect(route('asesor.mak02.show', $this->pendaftaran))
            ->assertSessionHas('success');

        // Setelah refresh, radio "Semua tercapai" harus masih terpilih.
        $response = $this->actingAs($this->asesorUser)
            ->get(route('asesor.mak02.show', $this->pendaftaran));
        $response->assertOk();
        $this->assertSame(
            1,
            preg_match('/name="elemenTercapai\[[^\]]+\]"\s+value="1"\s+checked/', $response->getContent())
        );
    }

    public function test_asesor_finalisasi_mak02_ditolak_metode_tidak_digunakan(): void
    {
        // Metode tidak digunakan (digunakan=0) tapi keputusan terisi → final ditolak.
        $payload = [
            'metode_default' => ['observasi'],
            'elemenTercapai' => [$this->elemen->id => '1'],
            'elemenCatatan' => [$this->elemen->id => null],
            'elemen4C' => [
                $this->elemen->id => ['valid' => '1', 'autentik' => '1', 'terkini' => '1', 'memadai' => '1'],
            ],
            'units' => [
                $this->unit->id => [
                    'keputusan' => 'kompeten',
                    'catatan' => null,
                    'metode' => [
                        'observasi' => [
                            'digunakan' => '0',
                            'bukti_langsung' => '0',
                            'bukti_tidak_langsung' => '0',
                            'bukti_tambahan' => '0',
                            'keputusan' => 'kompeten',
                            'catatan' => null,
                        ],
                    ],
                ],
            ],
        ];

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.mak02.store', $this->pendaftaran), $payload)
            ->assertRedirect(route('asesor.mak02.show', $this->pendaftaran))
            ->assertSessionHas('success');

        $this->actingAs($this->asesiUser)
            ->post(route('asesi.apl02.serah', $this->pendaftaran), $this->elemenPayload())
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

        // Paksa data: metode tidak digunakan tapi keputusan terisi.
        $hasil = HasilAsesmen::where('pendaftaran_id', $this->pendaftaran->id)->firstOrFail();
        $hasil->units()->first()->metodes()->first()->update(['keputusan' => 'kompeten']);

        // Final diblokir: metode tidak digunakan tidak boleh punya keputusan/catatan.
        $this->actingAs($this->asesorUser)
            ->from(route('asesor.mak02.show', $this->pendaftaran))
            ->post(route('asesor.mak02.final', $this->pendaftaran), [
                'rekomendasi' => HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN,
                'umpan_balik' => '1',
                'konfirmasi' => '1',
            ])
            ->assertRedirect(route('asesor.mak02.show', $this->pendaftaran))
            ->assertSessionHasErrors('units');
    }

    public function test_asesor_finalisasi_mak02_gagal_validasi_field_wajib(): void
    {
        $payload = [
            'metode_default' => ['observasi'],
            'elemenTercapai' => [$this->elemen->id => '1'],
            'elemenCatatan' => [$this->elemen->id => null],
            'elemen4C' => [
                $this->elemen->id => ['valid' => '1', 'autentik' => '1', 'terkini' => '1', 'memadai' => '1'],
            ],
            'units' => [
                $this->unit->id => [
                    'keputusan' => 'kompeten',
                    'catatan' => null,
                    'metode' => [
                        'observasi' => [
                            'digunakan' => '1',
                            'bukti_langsung' => '1',
                            'bukti_tidak_langsung' => '0',
                            'bukti_tambahan' => '0',
                            'keputusan' => 'kompeten',
                            'catatan' => null,
                        ],
                    ],
                ],
            ],
        ];

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.mak02.store', $this->pendaftaran), $payload)
            ->assertRedirect(route('asesor.mak02.show', $this->pendaftaran))
            ->assertSessionHas('success');

        // APL-02 diverifikasi lebih dulu.
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.apl02.serah', $this->pendaftaran), $this->elemenPayload())
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

        // Finalisasi tanpa field wajib → validasi gagal, error di-flash.
        $this->actingAs($this->asesorUser)
            ->from(route('asesor.mak02.show', $this->pendaftaran))
            ->post(route('asesor.mak02.final', $this->pendaftaran), [])
            ->assertRedirect(route('asesor.mak02.show', $this->pendaftaran))
            ->assertSessionHasErrors(['rekomendasi', 'umpan_balik', 'konfirmasi']);

        // Hasil belum jadi final.
        $hasil = HasilAsesmen::where('pendaftaran_id', $this->pendaftaran->id)->firstOrFail();
        $this->assertNotSame(HasilAsesmen::STATUS_FINAL, $hasil->status);
    }

    public function test_apl02_and_mak02_can_be_printed_as_pdf(): void
    {
        $this->actingAs($this->asesiUser)
            ->post(route('asesi.apl02.serah', $this->pendaftaran), $this->elemenPayload())
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
