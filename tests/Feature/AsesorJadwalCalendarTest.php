<?php

namespace Tests\Feature;

use App\Models\Asesor;
use App\Models\EventAsesmen;
use App\Models\Institution;
use App\Models\JadwalAsesmen;
use App\Models\JadwalAsesor;
use App\Models\Skema;
use App\Models\Tuk;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsesorJadwalCalendarTest extends TestCase
{
    use RefreshDatabase;

    private User $asesorUser;

    private Asesor $asesor;

    private JadwalAsesmen $jadwal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $institution = Institution::create([
            'name' => 'LSP Kalender',
            'slug' => 'lsp-kalender',
            'is_active' => true,
        ]);

        TenantContext::set($institution);

        $this->asesorUser = User::factory()->create([
            'institution_id' => $institution->id,
            'status' => 'aktif',
        ]);
        $this->asesorUser->assignRole('asesor');
        $this->asesorUser->institutions()->attach($institution->id, ['role' => 'asesor']);

        $this->asesor = Asesor::create([
            'user_id' => $this->asesorUser->id,
            'no_register' => 'MET.000.000002 2026',
            'status' => 'aktif',
            'kebangsaan' => 'Indonesia',
        ]);

        $event = EventAsesmen::create([
            'nama' => 'Event Kalender',
            'tahun' => 2026,
            'periode' => 11,
        ]);

        $skema = Skema::create([
            'kode_skema' => 'SKM-KAL',
            'nama_skema' => 'Skema Kalender',
            'jenis' => 'okupasi',
            'biaya' => 0,
            'is_active' => true,
        ]);

        $tuk = Tuk::create([
            'kode_tuk' => 'TUK-KAL',
            'nama_tuk' => 'TUK Kalender',
            'jenis' => 'mandiri',
            'penanggung_jawab' => 'Penanggung Jawab',
            'is_active' => true,
        ]);

        $this->jadwal = JadwalAsesmen::create([
            'event_id' => $event->id,
            'skema_id' => $skema->id,
            'tuk_id' => $tuk->id,
            'tanggal' => '2026-11-15',
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
            'kuota' => 10,
            'status' => 'terbuka',
        ]);
        JadwalAsesor::create([
            'jadwal_id' => $this->jadwal->id,
            'asesor_id' => $this->asesor->id,
            'peran' => 'utama',
        ]);
    }

    public function test_list_view_renders(): void
    {
        $response = $this->actingAs($this->asesorUser)->get(route('asesor.jadwal.index'));

        $response->assertOk();
        $response->assertViewIs('asesor.jadwal.index');
        $response->assertSee('Skema Kalender');
        $response->assertSee('Kalender');
    }

    public function test_calendar_view_renders_assigned_jadwal(): void
    {
        $response = $this->actingAs($this->asesorUser)->get(route('asesor.jadwal.calendar', ['month' => '2026-11']));

        $response->assertOk();
        $response->assertViewIs('asesor.jadwal.calendar');
        $response->assertSee('Skema Kalender');
        $response->assertSee('09:00');
    }

    public function test_calendar_view_via_index_query_param(): void
    {
        $response = $this->actingAs($this->asesorUser)->get(route('asesor.jadwal.index', ['view' => 'calendar', 'month' => '2026-11']));

        $response->assertOk();
        $response->assertViewIs('asesor.jadwal.calendar');
    }
}
