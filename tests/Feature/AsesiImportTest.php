<?php

namespace Tests\Feature;

use App\Models\Asesi;
use App\Models\ImportBatch;
use App\Models\ImportRow;
use App\Models\Institution;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Province;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class AsesiImportTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->institution = Institution::create([
            'name' => 'LSP Impor',
            'slug' => 'lsp-impor',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);

        TenantContext::set($this->institution);

        Province::firstOrCreate(['code' => '31'], ['name' => 'DKI JAKARTA']);
        City::firstOrCreate(['code' => '3171', 'province_code' => '31', 'name' => 'KOTA JAKARTA SELATAN']);

        $this->admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'aktif',
        ]);
        $this->admin->assignRole('admin');
        $this->admin->institutions()->attach($this->institution->id, ['role' => 'admin']);
    }

    public function test_admin_can_download_import_template(): void
    {
        $response = $this->asAdmin()->get(route('admin.asesi.import.template'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_preview_flags_invalid_rows_and_commit_creates_accounts(): void
    {
        Queue::fake();
        Storage::fake('local');

        $file = $this->makeSpreadsheet([
            $this->validRow('Asesi Satu', '3201123456780101', 'asesi.satu@example.test'),
            $this->validRow('Duplikat NIK A', '3201123456780102', 'asesi.dua@example.test'),
            $this->validRow('Duplikat NIK B', '3201123456780102', 'asesi.tiga@example.test'),
            $this->validRow('Email Salah', '3201123456780103', 'bukan-email'),
            $this->validRow('', '3201123456780104', 'asesi.empat@example.test'),
        ]);

        $this->asAdmin()
            ->post(route('admin.asesi.import.preview'), ['file' => $file])
            ->assertRedirect();

        $batch = ImportBatch::withoutTenantScope()->firstOrFail();

        $this->assertSame(ImportBatch::STATUS_PREVIEW, $batch->status);
        $this->assertSame(5, $batch->total_rows);
        $this->assertSame(1, $batch->valid_rows);
        $this->assertSame(4, $batch->invalid_rows);

        $this->assertSame(
            'NIK duplikat di dalam berkas.',
            ImportRow::withoutTenantScope()
                ->where('import_batch_id', $batch->id)
                ->where('row_number', 3)
                ->firstOrFail()
                ->errors['nik'],
        );

        $this->asAdmin()
            ->post(route('admin.asesi.import.commit', $batch))
            ->assertRedirect();

        $batch->refresh();
        $this->assertSame(ImportBatch::STATUS_SELESAI, $batch->status);
        $this->assertSame(1, $batch->imported_rows);
        $this->assertSame(4, $batch->invalid_rows);

        $user = User::where('email', 'asesi.satu@example.test')->firstOrFail();
        $this->assertTrue($user->hasRole('asesi'));
        $this->assertSame(User::hashNik('3201123456780101'), $user->nik_hash);

        $asesi = Asesi::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('impor', $asesi->sumber);
        $this->assertSame($batch->id, $asesi->import_batch_id);
        $this->assertSame('belum_terverifikasi', $asesi->status_verifikasi);

        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $user->id,
            'tipe' => 'aktivasi_akun',
        ]);

        $this->assertSame(
            ImportRow::STATUS_IMPORTED,
            ImportRow::withoutTenantScope()
                ->where('import_batch_id', $batch->id)
                ->where('row_number', 2)
                ->firstOrFail()
                ->status,
        );
    }

    public function test_nik_already_registered_is_rejected_in_preview(): void
    {
        Queue::fake();
        Storage::fake('local');

        $existing = User::factory()->create([
            'institution_id' => $this->institution->id,
            'nik' => '3201123456780201',
            'email' => 'sudah.ada@example.test',
            'status' => 'aktif',
        ]);
        $existing->assignRole('asesi');

        $file = $this->makeSpreadsheet([
            $this->validRow('NIK Terdaftar', '3201123456780201', 'baru@example.test'),
        ]);

        $this->asAdmin()->post(route('admin.asesi.import.preview'), ['file' => $file]);

        $batch = ImportBatch::withoutTenantScope()->latest('created_at')->firstOrFail();
        $row = ImportRow::withoutTenantScope()->where('import_batch_id', $batch->id)->firstOrFail();

        $this->assertSame(ImportRow::STATUS_INVALID, $row->status);
        $this->assertSame('NIK sudah terdaftar pada lembaga ini.', $row->errors['nik']);
    }

    public function test_import_batch_from_another_tenant_is_not_accessible(): void
    {
        $other = Institution::create([
            'name' => 'LSP Impor Lain',
            'slug' => 'lsp-impor-lain',
            'is_active' => true,
        ]);

        $otherAdmin = User::factory()->create([
            'institution_id' => $other->id,
            'status' => 'aktif',
        ]);
        $otherAdmin->assignRole('admin');

        TenantContext::set($other);

        $batch = ImportBatch::create([
            'user_id' => $otherAdmin->id,
            'original_name' => 'lain.xlsx',
            'file_path' => 'tenants/'.$other->id.'/imports/lain.xlsx',
            'status' => ImportBatch::STATUS_PREVIEW,
        ]);

        TenantContext::set($this->institution);

        $this->asAdmin()
            ->get(route('admin.asesi.import.show', $batch))
            ->assertNotFound();
    }

    public function test_imported_asesi_can_activate_account_through_signed_link(): void
    {
        $user = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'Asesi Aktivasi',
            'email' => 'aktivasi@example.test',
            'status' => 'aktif',
            'last_login_at' => null,
        ]);
        $user->assignRole('asesi');

        $url = URL::temporarySignedRoute('asesi.activate.store', now()->addHour(), ['user' => $user->id]);

        $this->post($url, [
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])->assertRedirect(route('login'));

        $this->assertNotNull($user->fresh()->last_login_at);

        $showUrl = URL::temporarySignedRoute('asesi.activate', now()->addHour(), ['user' => $user->id]);

        $this->get($showUrl)->assertRedirect(route('login'));
    }

    /**
     * @return array<string, string>
     */
    private function validRow(string $nama, string $nik, string $email): array
    {
        return [
            'nama' => $nama,
            'nik' => $nik,
            'email' => $email,
            'telepon' => '081234567890',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
            'alamat' => 'Jl. Impor No. 1',
            'kode_provinsi' => '31',
            'kode_kota' => '3171',
            'kode_kecamatan' => '',
            'pendidikan_terakhir' => 'D3 Informatika',
            'pekerjaan' => 'Staf',
            'jabatan' => 'Staff',
        ];
    }

    /**
     * @param  array<int, array<string, string>>  $rows
     */
    private function makeSpreadsheet(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $headers = array_keys($this->validRow('Nama', '3201123456780001', 'a@example.test'));
        $sheet->fromArray($headers, null, 'A1');

        $line = 2;
        foreach ($rows as $row) {
            $sheet->fromArray(array_values($row), null, 'A'.$line);
            $line++;
        }

        $path = tempnam(sys_get_temp_dir(), 'asesi-import-').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile(
            $path,
            'asesi.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true,
        );
    }

    private function asAdmin(): static
    {
        return $this->actingAs($this->admin)
            ->withSession([TenantContext::SESSION_ID => $this->institution->id]);
    }
}
