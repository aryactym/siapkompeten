<?php

namespace Tests\Browser;

use App\Models\Asesor;
use App\Models\EventAsesmen;
use App\Models\Institution;
use App\Models\JadwalAsesmen;
use App\Models\Skema;
use App\Models\SkemaPersyaratan;
use App\Models\Tuk;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Province;
use Tests\DuskTestCase;

class AlurPenjadwalanDanPendaftaranTest extends DuskTestCase
{
    use DatabaseMigrations;

    private Institution $institution;

    private User $admin;

    private User $asesorUser;

    private Asesor $asesor;

    private EventAsesmen $event;

    private Skema $skema;

    private Tuk $tuk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->institution = Institution::create([
            'name' => 'LSP Dusk Fase 2 dan 3',
            'slug' => 'lsp-dusk-fase-2-3',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);

        TenantContext::set($this->institution);

        Province::firstOrCreate(['code' => '31'], ['name' => 'DKI JAKARTA']);
        City::firstOrCreate(['code' => '3171'], ['province_code' => '31', 'name' => 'KOTA JAKARTA SELATAN']);

        $this->admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Admin Dusk',
            'username' => 'admin.dusk',
            'email' => 'admin.dusk@example.test',
            'password' => 'password',
            'status' => 'aktif',
        ]);
        $this->admin->assignRole('admin');
        $this->admin->institutions()->attach($this->institution->id, ['role' => 'admin']);

        $this->asesorUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesor Dusk',
            'email' => 'asesor.dusk@example.test',
            'password' => 'password',
            'status' => 'aktif',
        ]);
        $this->asesorUser->assignRole('asesor');
        $this->asesorUser->institutions()->attach($this->institution->id, ['role' => 'asesor']);

        $this->asesor = Asesor::create([
            'user_id' => $this->asesorUser->id,
            'no_register' => 'MET.000.000077 2026',
            'status' => 'aktif',
            'kebangsaan' => 'Indonesia',
        ]);

        $this->event = EventAsesmen::create([
            'nama' => 'Event Dusk',
            'tahun' => 2026,
            'periode' => 11,
        ]);

        $this->skema = Skema::create([
            'kode_skema' => 'SKM-DUSK',
            'nama_skema' => 'Skema Dusk Penjadwalan',
            'jenis' => 'okupasi',
            'biaya' => 0,
            'is_active' => true,
        ]);

        SkemaPersyaratan::create([
            'skema_id' => $this->skema->id,
            'nama_dokumen' => 'KTP',
            'is_required' => true,
            'urutan' => 1,
        ]);

        $this->tuk = Tuk::create([
            'kode_tuk' => 'TUK-DUSK',
            'nama_tuk' => 'TUK Dusk',
            'jenis' => 'mandiri',
            'penanggung_jawab' => 'PJ Dusk',
            'is_active' => true,
        ]);
    }

    public function test_admin_creates_schedule_and_assigns_asesor(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/login')
                ->type('login', 'admin.dusk@example.test')
                ->type('password', 'password')
                ->press('Masuk Sekarang')
                ->waitForLocation('/dashboard')
                ->assertSee($this->institution->name)
                ->visit('/admin/jadwal/create')
                ->select('event_id', $this->event->id)
                ->select('skema_id', $this->skema->id)
                ->select('tuk_id', $this->tuk->id)
                ->type('tanggal', '2026-12-01')
                ->type('jam_mulai', '08:00')
                ->type('jam_selesai', '12:00')
                ->clear('kuota')
                ->type('kuota', '10')
                ->select('lingkup_asesor', 'wilayah')
                ->select('status', 'terbuka')
                ->press('Simpan')
                ->waitForText('Jadwal uji berhasil dibuat')
                ->assertSee($this->skema->nama_skema)
                ->select('asesor_id', $this->asesor->id)
                ->select('peran', 'utama')
                ->press('Tugaskan')
                ->waitForText('Asesor berhasil ditugaskan')
                ->assertSee($this->asesorUser->name);

            $jadwal = JadwalAsesmen::withoutTenantScope()->firstOrFail();
            $this->assertSame(1, $jadwal->penugasan()->count());
        });
    }

    public function test_asesi_registers_and_creates_pendaftaran_through_portal(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/daftar')
                ->type('name', 'Asesi Dusk')
                ->type('nik', '3201123456780099')
                ->type('phone', '081200000099')
                ->type('email', 'asesi.dusk@example.test')
                ->type('password', 'Password123')
                ->type('password_confirmation', 'Password123')
                ->select('jenis_kelamin', 'L')
                ->type('tempat_lahir', 'Jakarta')
                ->type('tanggal_lahir', '2000-01-01')
                ->type('alamat_rumah', 'Jl. Dusk No. 1')
                ->select('provinsi_code', '31')
                ->select('kota_code', '3171')
                ->type('pendidikan_terakhir', 'D3 Informatika')
                ->check('consent')
                ->press('Daftar Sebagai Asesi')
                ->waitForLocation('/dashboard')
                ->assertSee('Selamat Datang, Asesi Dusk')
                ->visit('/asesi/pendaftaran/create')
                ->assertSee($this->skema->nama_skema)
                ->radio('skema_id', $this->skema->id)
                ->press('Ajukan Pendaftaran')
                ->waitForDialog()
                ->acceptDialog()
                ->waitForText('Pendaftaran skema berhasil dibuat')
                ->assertSee('Unggah Dokumen');
        });
    }
}
