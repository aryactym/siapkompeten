<?php

namespace Tests\Feature;

use App\Models\Asesi;
use App\Models\Asesor;
use App\Models\ElemenKompetensi;
use App\Models\EventAsesmen;
use App\Models\Institution;
use App\Models\InstrumenAsesmen;
use App\Models\InstrumenAsesmenItem;
use App\Models\JadwalAsesmen;
use App\Models\JadwalAsesor;
use App\Models\KriteriaUnjukKerja;
use App\Models\Pendaftaran;
use App\Models\PersetujuanAsesmen;
use App\Models\Skema;
use App\Models\SkemaUnit;
use App\Models\Tuk;
use App\Models\UnitKompetensi;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstrumenAsesmenTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    private User $asesiUser;

    private Asesi $asesi;

    private User $asesorUser;

    private Asesor $asesor;

    private Skema $skema;

    private KriteriaUnjukKerja $kuk1;

    private KriteriaUnjukKerja $kuk2;

    private Pendaftaran $pendaftaran;

    private JadwalAsesmen $jadwal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->institution = Institution::create([
            'name' => 'LSP Instrumen',
            'slug' => 'lsp-instrumen',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);

        TenantContext::set($this->institution);

        $this->asesiUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesi Instrumen',
            'email' => 'asesi.instrumen@example.test',
            'nik' => '3201123456780044',
            'phone' => '081234567844',
            'status' => 'aktif',
        ]);
        $this->asesiUser->assignRole('asesi');
        $this->asesiUser->institutions()->attach($this->institution->id, ['role' => 'asesi']);

        $this->asesi = Asesi::create([
            'user_id' => $this->asesiUser->id,
            'no_registrasi' => '202611050044',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '2000-01-01',
            'kebangsaan' => 'Indonesia',
            'alamat_rumah' => 'Jl. Instrumen No. 1',
            'pendidikan_terakhir' => 'D3',
            'status_kompetensi' => 'belum_asesmen',
        ]);

        $this->asesorUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesor Instrumen',
            'email' => 'asesor.instrumen@example.test',
            'status' => 'aktif',
        ]);
        $this->asesorUser->assignRole('asesor');
        $this->asesorUser->institutions()->attach($this->institution->id, ['role' => 'asesor']);

        $this->asesor = Asesor::create([
            'user_id' => $this->asesorUser->id,
            'no_register' => 'MET.000.000033 2026',
            'status' => 'aktif',
            'kebangsaan' => 'Indonesia',
        ]);

        $this->skema = Skema::create([
            'kode_skema' => 'SKM-INS',
            'nama_skema' => 'Skema Instrumen',
            'jenis' => 'okupasi',
            'biaya' => 0,
            'is_active' => true,
        ]);

        $unit = UnitKompetensi::create([
            'kode_unit' => 'UK-INS-01',
            'judul_unit' => 'Melaksanakan Observasi Kerja',
            'deskripsi' => 'Unit untuk instrumen',
            'is_active' => true,
        ]);

        SkemaUnit::create([
            'skema_id' => $this->skema->id,
            'unit_kompetensi_id' => $unit->id,
            'jenis' => 'inti',
            'urutan' => 1,
        ]);

        $elemen = ElemenKompetensi::create([
            'unit_kompetensi_id' => $unit->id,
            'urutan' => 1,
            'nama_elemen' => 'Elemen Instrumen',
        ]);

        $this->kuk1 = KriteriaUnjukKerja::create([
            'elemen_kompetensi_id' => $elemen->id,
            'urutan' => 1,
            'pernyataan_kuk' => 'Menyiapkan alat observasi',
        ]);

        $this->kuk2 = KriteriaUnjukKerja::create([
            'elemen_kompetensi_id' => $elemen->id,
            'urutan' => 2,
            'pernyataan_kuk' => 'Melaksanakan observasi sesuai prosedur',
        ]);

        $event = EventAsesmen::create(['nama' => 'Event Instrumen', 'tahun' => 2026]);
        $tuk = Tuk::create([
            'kode_tuk' => 'TUK-INS',
            'nama_tuk' => 'TUK Instrumen',
            'jenis' => 'mandiri',
            'penanggung_jawab' => 'PJ',
        ]);

        $this->jadwal = JadwalAsesmen::create([
            'event_id' => $event->id,
            'skema_id' => $this->skema->id,
            'tuk_id' => $tuk->id,
            'tanggal' => '2026-12-12',
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
            'no_pendaftaran' => 'PEND-INS-0001',
            'asesi_id' => $this->asesi->id,
            'skema_id' => $this->skema->id,
            'jadwal_id' => $this->jadwal->id,
            'status' => Pendaftaran::STATUS_ASESMEN_BERJALAN,
        ]);

        foreach (PersetujuanAsesmen::requiredJenis() as $jenis) {
            PersetujuanAsesmen::create([
                'institution_id' => $this->institution->id,
                'pendaftaran_id' => $this->pendaftaran->id,
                'jenis' => $jenis,
                'status' => PersetujuanAsesmen::STATUS_DISETUJUI,
                'disetujui_pada' => now(),
                'disetujui_oleh' => $this->asesiUser->id,
            ]);
        }
    }

    public function test_asesor_can_open_instrument_and_items_are_generated_per_kuk(): void
    {
        $this->actingAs($this->asesorUser)
            ->get(route('asesor.instrumen.show', [$this->pendaftaran, InstrumenAsesmen::JENIS_IA01]))
            ->assertOk()
            ->assertSee('Menyiapkan alat observasi')
            ->assertSee('Melaksanakan observasi sesuai prosedur');

        $instrumen = InstrumenAsesmen::where('pendaftaran_id', $this->pendaftaran->id)
            ->where('jenis', InstrumenAsesmen::JENIS_IA01)
            ->first();

        $this->assertNotNull($instrumen);
        $this->assertSame(2, $instrumen->items()->count());
        $this->assertSame(
            [$this->kuk1->id, $this->kuk2->id],
            $instrumen->items()->orderBy('urutan')->pluck('kuk_id')->all(),
        );
    }

    public function test_asesor_can_save_item_results(): void
    {
        $this->actingAs($this->asesorUser)
            ->get(route('asesor.instrumen.show', [$this->pendaftaran, InstrumenAsesmen::JENIS_IA01]))
            ->assertOk();

        $instrumen = InstrumenAsesmen::where('pendaftaran_id', $this->pendaftaran->id)->firstOrFail();
        $items = $instrumen->items()->orderBy('urutan')->get();

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.instrumen.store', [$this->pendaftaran, InstrumenAsesmen::JENIS_IA01]), [
                'catatan' => 'Observasi berjalan lancar.',
                'items' => [
                    $items[0]->id => ['hasil' => InstrumenAsesmenItem::HASIL_TERCAPAI, 'catatan' => 'Sesuai prosedur'],
                    $items[1]->id => ['hasil' => InstrumenAsesmenItem::HASIL_BELUM_TERCAPAI, 'catatan' => 'Perlu pendampingan'],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $instrumen->refresh();
        $this->assertSame('Observasi berjalan lancar.', $instrumen->catatan);
        $this->assertSame(InstrumenAsesmenItem::HASIL_TERCAPAI, $items[0]->fresh()->hasil);
        $this->assertSame(InstrumenAsesmenItem::HASIL_BELUM_TERCAPAI, $items[1]->fresh()->hasil);
    }

    public function test_final_is_blocked_when_items_are_not_fully_rated(): void
    {
        $this->actingAs($this->asesorUser)
            ->get(route('asesor.instrumen.show', [$this->pendaftaran, InstrumenAsesmen::JENIS_IA02]))
            ->assertOk();

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.instrumen.final', [$this->pendaftaran, InstrumenAsesmen::JENIS_IA02]))
            ->assertRedirect()
            ->assertSessionHas('error');

        $instrumen = InstrumenAsesmen::where('pendaftaran_id', $this->pendaftaran->id)
            ->where('jenis', InstrumenAsesmen::JENIS_IA02)
            ->firstOrFail();

        $this->assertFalse($instrumen->isFinal());
    }

    public function test_final_locks_instrument_and_blocks_further_edits(): void
    {
        $this->actingAs($this->asesorUser)
            ->get(route('asesor.instrumen.show', [$this->pendaftaran, InstrumenAsesmen::JENIS_IA01]))
            ->assertOk();

        $instrumen = InstrumenAsesmen::where('pendaftaran_id', $this->pendaftaran->id)->firstOrFail();
        $items = $instrumen->items()->orderBy('urutan')->get();

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.instrumen.store', [$this->pendaftaran, InstrumenAsesmen::JENIS_IA01]), [
                'items' => [
                    $items[0]->id => ['hasil' => InstrumenAsesmenItem::HASIL_TERCAPAI],
                    $items[1]->id => ['hasil' => InstrumenAsesmenItem::HASIL_TERCAPAI],
                ],
            ])
            ->assertSessionHas('success');

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.instrumen.final', [$this->pendaftaran, InstrumenAsesmen::JENIS_IA01]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $instrumen->refresh();
        $this->assertTrue($instrumen->isFinal());
        $this->assertSame($this->asesorUser->id, $instrumen->finalized_by);

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.instrumen.store', [$this->pendaftaran, InstrumenAsesmen::JENIS_IA01]), [
                'items' => [
                    $items[0]->id => ['hasil' => InstrumenAsesmenItem::HASIL_BELUM_TERCAPAI],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(InstrumenAsesmenItem::HASIL_TERCAPAI, $items[0]->fresh()->hasil);
    }

    public function test_store_is_rejected_when_asesmen_not_running(): void
    {
        $this->pendaftaran->update(['status' => Pendaftaran::STATUS_TERJADWAL]);

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.instrumen.store', [$this->pendaftaran, InstrumenAsesmen::JENIS_IA01]), [
                'items' => [],
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_other_asesor_cannot_access_instrument(): void
    {
        $otherUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'aktif',
        ]);
        $otherUser->assignRole('asesor');
        $otherUser->institutions()->attach($this->institution->id, ['role' => 'asesor']);

        Asesor::create([
            'user_id' => $otherUser->id,
            'no_register' => 'MET.000.000034 2026',
            'status' => 'aktif',
            'kebangsaan' => 'Indonesia',
        ]);

        $this->actingAs($otherUser)
            ->get(route('asesor.instrumen.show', [$this->pendaftaran, InstrumenAsesmen::JENIS_IA01]))
            ->assertForbidden();
    }

    public function test_unknown_instrument_type_returns_404(): void
    {
        $this->actingAs($this->asesorUser)
            ->get(route('asesor.instrumen.show', [$this->pendaftaran, 'ia99']))
            ->assertNotFound();
    }

    public function test_mak02_shows_instrument_recap_as_evidence_source(): void
    {
        $this->actingAs($this->asesorUser)
            ->get(route('asesor.instrumen.show', [$this->pendaftaran, InstrumenAsesmen::JENIS_IA01]))
            ->assertOk();

        $instrumen = InstrumenAsesmen::where('pendaftaran_id', $this->pendaftaran->id)->firstOrFail();
        $items = $instrumen->items()->orderBy('urutan')->get();

        $this->actingAs($this->asesorUser)
            ->post(route('asesor.instrumen.store', [$this->pendaftaran, InstrumenAsesmen::JENIS_IA01]), [
                'items' => [
                    $items[0]->id => ['hasil' => InstrumenAsesmenItem::HASIL_TERCAPAI],
                    $items[1]->id => ['hasil' => InstrumenAsesmenItem::HASIL_BELUM_TERCAPAI],
                ],
            ])
            ->assertSessionHas('success');

        $this->actingAs($this->asesorUser)
            ->get(route('asesor.mak02.show', $this->pendaftaran))
            ->assertOk()
            ->assertSee('Sumber Bukti Instrumen')
            ->assertSee('FR.IA.01')
            ->assertSee(route('asesor.instrumen.show', [$this->pendaftaran, InstrumenAsesmen::JENIS_IA01]), false);
    }

    public function test_ia08_instrument_is_available(): void
    {
        $this->actingAs($this->asesorUser)
            ->get(route('asesor.instrumen.show', [$this->pendaftaran, InstrumenAsesmen::JENIS_IA08]))
            ->assertOk()
            ->assertSee('FR.IA.08');

        $this->actingAs($this->asesorUser)
            ->get(route('asesor.instrumen.pdf', [$this->pendaftaran, InstrumenAsesmen::JENIS_IA08]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_pdf_renders_for_instrument(): void
    {
        $response = $this->actingAs($this->asesorUser)
            ->get(route('asesor.instrumen.pdf', [$this->pendaftaran, InstrumenAsesmen::JENIS_IA01]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
