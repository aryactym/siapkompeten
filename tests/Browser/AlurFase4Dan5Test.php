<?php

namespace Tests\Browser;

use App\Models\Asesi;
use App\Models\Asesor;
use App\Models\Biaya;
use App\Models\ElemenKompetensi;
use App\Models\EventAsesmen;
use App\Models\Institution;
use App\Models\JadwalAsesmen;
use App\Models\JadwalAsesor;
use App\Models\KriteriaUnjukKerja;
use App\Models\Pendaftaran;
use App\Models\RekeningBank;
use App\Models\Skema;
use App\Models\SkemaUnit;
use App\Models\Tuk;
use App\Models\UnitKompetensi;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AlurFase4Dan5Test extends DuskTestCase
{
    use DatabaseMigrations;

    private Institution $institution;

    private User $admin;

    private User $asesiUser;

    private User $asesorUser;

    private Pendaftaran $pendaftaran;

    private JadwalAsesmen $jadwal;

    private RekeningBank $rekening;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->institution = Institution::create([
            'name' => 'LSP Dusk Fase 4 dan 5',
            'slug' => 'lsp-dusk-fase-4-5',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);

        TenantContext::set($this->institution);

        $this->admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Admin Fase 45',
            'username' => 'admin.fase45',
            'email' => 'admin.fase45@example.test',
            'password' => 'password',
            'status' => 'aktif',
        ]);
        $this->admin->assignRole('admin');
        $this->admin->institutions()->attach($this->institution->id, ['role' => 'admin']);

        $this->asesiUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesi Fase 45',
            'username' => 'asesi.fase45',
            'email' => 'asesi.fase45@example.test',
            'password' => 'password',
            'nik' => '3201123456780033',
            'phone' => '081234567833',
            'status' => 'aktif',
        ]);
        $this->asesiUser->assignRole('asesi');
        $this->asesiUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        $asesi = Asesi::create([
            'user_id' => $this->asesiUser->id,
            'no_registrasi' => '202610310033',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
            'kebangsaan' => 'Indonesia',
            'alamat_rumah' => 'Jl. Fase 45 No. 1',
            'pendidikan_terakhir' => 'D3 Teknik Informatika',
        ]);

        $this->asesorUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesor Fase 45',
            'username' => 'asesor.fase45',
            'email' => 'asesor.fase45@example.test',
            'password' => 'password',
            'status' => 'aktif',
        ]);
        $this->asesorUser->assignRole('asesor');
        $this->asesorUser->institutions()->attach($this->institution->id, ['role' => 'asesor']);

        $asesor = Asesor::create([
            'user_id' => $this->asesorUser->id,
            'no_register' => 'MET.000.000021 2026',
            'status' => 'aktif',
            'kebangsaan' => 'Indonesia',
        ]);

        $skema = Skema::create([
            'kode_skema' => 'SKM-DUSK45',
            'nama_skema' => 'Skema Dusk Fase 45',
            'jenis' => 'okupasi',
            'biaya' => 0,
            'is_active' => true,
        ]);

        Biaya::create([
            'skema_id' => $skema->id,
            'jenis_biaya' => 'Biaya Asesmen Dusk',
            'nominal' => 1000000,
            'tanggung_jawab' => Biaya::TANGGUNG_PESERTA,
        ]);

        $unit = UnitKompetensi::create([
            'kode_unit' => 'UK-DUSK45',
            'judul_unit' => 'Unit Dusk Fase 45',
            'deskripsi' => 'Unit kompetensi uji',
            'is_active' => true,
        ]);

        SkemaUnit::create([
            'skema_id' => $skema->id,
            'unit_kompetensi_id' => $unit->id,
            'jenis' => 'inti',
            'urutan' => 1,
        ]);

        $elemen = ElemenKompetensi::create([
            'unit_kompetensi_id' => $unit->id,
            'urutan' => 1,
            'nama_elemen' => 'Elemen Dusk Fase 45',
        ]);

        KriteriaUnjukKerja::create([
            'elemen_kompetensi_id' => $elemen->id,
            'urutan' => 1,
            'pernyataan_kuk' => 'KUK Dusk Fase 45',
        ]);

        $this->rekening = RekeningBank::create([
            'nama_bank' => 'Bank Mandiri',
            'no_rekening' => '5556667778',
            'atas_nama' => 'LSP SiapKompeten',
            'is_aktif' => true,
        ]);

        $event = EventAsesmen::create(['nama' => 'Event Dusk Fase 45', 'tahun' => 2026]);
        $tuk = Tuk::create([
            'kode_tuk' => 'TUK-DUSK45',
            'nama_tuk' => 'TUK Dusk Fase 45',
            'jenis' => 'mandiri',
            'penanggung_jawab' => 'PJ Dusk',
        ]);

        $this->jadwal = JadwalAsesmen::create([
            'event_id' => $event->id,
            'skema_id' => $skema->id,
            'tuk_id' => $tuk->id,
            'tanggal' => '2026-12-10',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
            'kuota' => 10,
            'lingkup_asesor' => 'wilayah',
            'status' => 'terbuka',
        ]);

        JadwalAsesor::create([
            'jadwal_id' => $this->jadwal->id,
            'asesor_id' => $asesor->id,
            'peran' => 'utama',
        ]);

        $this->pendaftaran = Pendaftaran::create([
            'no_pendaftaran' => 'PEND-DUSK45',
            'asesi_id' => $asesi->id,
            'skema_id' => $skema->id,
            'jadwal_id' => $this->jadwal->id,
            'status' => Pendaftaran::STATUS_MENUNGGU_PEMBAYARAN,
        ]);
    }

    public function test_admin_menu_kelembagaan_dan_validasi_pembayaran(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/login')
                ->type('login', 'admin.fase45@example.test')
                ->type('password', 'password')
                ->press('Masuk Sekarang')
                ->waitForLocation('/dashboard')
                ->assertVisible('#sidebar-menu');

            // Menu Kelembagaan -> Rekening Bank LSP
            $browser->click('a.dropdown-toggle[href="#sidebar-lsp"]')
                ->waitForText('Rekening Bank LSP')
                ->clickLink('Rekening Bank LSP')
                ->waitForLocation('/admin/rekening-bank')
                ->assertSee('Rekening Bank Penerima')
                ->assertSee('Bank Mandiri');

            // Menu Kelembagaan -> Biaya Skema
            $browser->click('a.dropdown-toggle[href="#sidebar-lsp"]')
                ->waitForText('Biaya Skema')
                ->clickLink('Biaya Skema')
                ->waitForLocation('/admin/biaya')
                ->assertSee('Biaya per Skema Sertifikasi')
                ->assertSee('Biaya Asesmen Dusk');

            // Menu Asesi & Pendaftaran -> Validasi Pembayaran
            $browser->click('a.dropdown-toggle[href="#sidebar-asesi"]')
                ->waitForText('Validasi Pembayaran')
                ->clickLink('Validasi Pembayaran')
                ->waitForLocation('/admin/pembayaran')
                ->assertSee('Validasi Pembayaran');
        });
    }

    public function test_asesi_uploads_payment_proof_from_portal(): void
    {
        $bukti = storage_path('framework/testing/bukti-dusk.pdf');
        if (! is_dir(dirname($bukti))) {
            mkdir(dirname($bukti), 0755, true);
        }
        file_put_contents($bukti, '%PDF-1.4 fake bukti pembayaran');

        $this->browse(function (Browser $browser) use ($bukti): void {
            $browser->visit('/login')
                ->type('login', 'asesi.fase45@example.test')
                ->type('password', 'password')
                ->press('Masuk Sekarang')
                ->waitForLocation('/dashboard')
                ->visit('/asesi/pendaftaran/'.$this->pendaftaran->id.'/pembayaran')
                ->assertSee('Rincian Tagihan')
                ->assertSee('Biaya Asesmen Dusk')
                ->select('rekening_bank_id', $this->rekening->id)
                ->type('tanggal_bayar', now()->format('Y-m-d'))
                ->attach('bukti', $bukti)
                ->press('Unggah Bukti Pembayaran')
                ->waitForText('Bukti pembayaran berhasil diunggah')
                ->assertSee('Menunggu Validasi Admin');
        });
    }

    public function test_asesor_opens_apl02_and_mak02_from_peserta(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/login')
                ->type('login', 'asesor.fase45@example.test')
                ->type('password', 'password')
                ->press('Masuk Sekarang')
                ->waitForLocation('/dashboard')
                ->visit('/asesor/jadwal/'.$this->jadwal->id.'/peserta')
                ->assertSee('Asesi Fase 45')
                ->click("a[title='Verifikasi APL-02']")
                ->waitForText('Verifikasi APL-02')
                ->assertSee('KUK Dusk Fase 45');

            $browser->visit('/asesor/jadwal/'.$this->jadwal->id.'/peserta')
                ->click("a[title='Input MAK-02']")
                ->waitForText('MAK-02')
                ->assertSee('Unit Dusk Fase 45');
        });
    }
}
