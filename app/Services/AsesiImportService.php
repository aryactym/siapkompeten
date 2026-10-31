<?php

namespace App\Services;

use App\Imports\AsesiRowsImport;
use App\Models\Asesi;
use App\Models\ImportBatch;
use App\Models\ImportRow;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class AsesiImportService
{
    public const DISK = 'local';

    public const MAX_ROWS = 2000;

    /** @var array<int, string> */
    public const ALLOWED_EXTENSIONS = ['xlsx', 'csv'];

    public function __construct(private readonly NotificationOutbox $outbox) {}

    /**
     * Simpan berkas dan urai seluruh baris menjadi status preview (FR-ASI-08).
     */
    public function createBatch(UploadedFile $file, User $admin): ImportBatch
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw ValidationException::withMessages([
                'file' => 'Berkas harus berformat .xlsx atau .csv.',
            ]);
        }

        $batchId = (string) Str::uuid();
        $path = sprintf('tenants/%s/imports/%s.%s', $admin->institution_id, $batchId, $extension);

        Storage::disk(self::DISK)->putFileAs(dirname($path), $file, basename($path));

        $batch = ImportBatch::create([
            'user_id' => $admin->id,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'status' => ImportBatch::STATUS_PREVIEW,
        ]);

        try {
            $this->parseIntoRows($batch);
        } catch (Throwable $exception) {
            Storage::disk(self::DISK)->delete($path);
            $batch->rows()->delete();
            $batch->delete();

            throw $exception;
        }

        activity('asesi')
            ->causedBy($admin)
            ->performedOn($batch)
            ->withProperties(['total_rows' => $batch->total_rows])
            ->log('Berkas impor asesi diunggah untuk pratinjau.');

        return $batch->refresh();
    }

    /**
     * Impor baris valid menjadi akun + profil asesi (FR-ASI-08/10).
     */
    public function commit(ImportBatch $batch, User $admin): ImportBatch
    {
        DB::transaction(function () use ($batch): void {
            $locked = ImportBatch::withoutTenantScope()
                ->whereKey($batch->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== ImportBatch::STATUS_PREVIEW) {
                throw ValidationException::withMessages([
                    'batch' => 'Batch impor ini sudah diproses.',
                ]);
            }

            $locked->update(['status' => 'processing']);
        });

        $sequence = $this->nextSequence($batch->institution_id);
        $imported = 0;

        $batch->rows()
            ->where('status', ImportRow::STATUS_VALID)
            ->orderBy('row_number')
            ->chunkById(50, function ($rows) use ($admin, &$sequence, &$imported): void {
                foreach ($rows as $row) {
                    try {
                        $user = DB::transaction(function () use ($row, $admin, &$sequence): User {
                            $data = $row->data;

                            $user = User::create([
                                'institution_id' => $row->institution_id,
                                'name' => $data['nama'],
                                'nik' => $data['nik'],
                                'email' => $data['email'],
                                'phone' => $data['telepon'],
                                'password' => Str::password(24),
                                'status' => 'aktif',
                            ]);

                            $user->assignRole('asesi');
                            $user->institutions()->attach($user->institution_id, ['role' => 'asesi']);

                            $asesi = Asesi::create([
                                'user_id' => $user->id,
                                'no_registrasi' => $this->nextNoRegistrasi($row->institution_id, $sequence),
                                'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
                                'tempat_lahir' => $data['tempat_lahir'] ?? null,
                                'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                                'kebangsaan' => 'Indonesia',
                                'alamat_rumah' => $data['alamat'] ?? null,
                                'kecamatan_code' => $data['kode_kecamatan'] ?? null,
                                'kota_code' => $data['kode_kota'] ?? null,
                                'provinsi_code' => $data['kode_provinsi'] ?? null,
                                'pendidikan_terakhir' => $data['pendidikan_terakhir'] ?? null,
                                'pekerjaan_instansi' => $data['pekerjaan'] ?? null,
                                'jabatan' => $data['jabatan'] ?? null,
                                'status_verifikasi' => 'belum_terverifikasi',
                                'status_kompetensi' => 'belum_asesmen',
                                'sumber' => 'impor',
                                'import_batch_id' => $row->import_batch_id,
                            ]);

                            $row->update([
                                'status' => ImportRow::STATUS_IMPORTED,
                                'asesi_id' => $asesi->id,
                                'errors' => null,
                            ]);

                            activity('asesi')
                                ->causedBy($admin)
                                ->performedOn($asesi)
                                ->withProperties(['import_batch_id' => $row->import_batch_id])
                                ->log('Asesi diimpor dari Excel.');

                            return $user;
                        });

                        $this->sendActivationLink($user);
                        $imported++;
                    } catch (Throwable $exception) {
                        Log::warning('Baris impor asesi gagal disimpan.', [
                            'batch' => $row->import_batch_id,
                            'row' => $row->row_number,
                            'error' => class_basename($exception),
                        ]);

                        $row->update([
                            'status' => ImportRow::STATUS_GAGAL,
                            'errors' => ['umum' => 'Baris gagal disimpan. Periksa data lalu impor ulang.'],
                        ]);
                    }
                }
            });

        $counts = $batch->rows()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $batch->update([
            'status' => ImportBatch::STATUS_SELESAI,
            'imported_rows' => (int) ($counts[ImportRow::STATUS_IMPORTED] ?? $imported),
            'invalid_rows' => $batch->rows()
                ->whereIn('status', [ImportRow::STATUS_INVALID, ImportRow::STATUS_GAGAL])
                ->count(),
        ]);

        activity('asesi')
            ->causedBy($admin)
            ->performedOn($batch)
            ->withProperties(['imported_rows' => $batch->imported_rows])
            ->log('Impor asesi selesai diproses.');

        return $batch->refresh();
    }

    public function deleteBatch(ImportBatch $batch): void
    {
        if (! $batch->isPreview()) {
            throw ValidationException::withMessages([
                'batch' => 'Batch yang sudah diproses tidak dapat dibatalkan.',
            ]);
        }

        Storage::disk(self::DISK)->delete($batch->file_path);
        $batch->rows()->delete();
        $batch->delete();
    }

    private function parseIntoRows(ImportBatch $batch): void
    {
        try {
            $sheets = Excel::toArray(new AsesiRowsImport, $batch->file_path, self::DISK);
        } catch (Throwable $exception) {
            throw ValidationException::withMessages([
                'file' => 'Berkas tidak dapat dibaca. Pastikan format sesuai template.',
            ]);
        }

        $rawRows = $sheets[0] ?? [];

        if (count($rawRows) > self::MAX_ROWS) {
            throw ValidationException::withMessages([
                'file' => 'Jumlah baris melebihi batas '.self::MAX_ROWS.' baris per impor.',
            ]);
        }

        $prepared = [];

        foreach ($rawRows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $normalized = array_map(
                fn ($value): mixed => is_string($value) ? trim($value) : $value,
                $row,
            );

            if ($this->isEmptyRow($normalized)) {
                continue;
            }

            $prepared[] = [
                'row_number' => $index + 2,
                'raw' => $normalized,
            ];
        }

        if ($prepared === []) {
            throw ValidationException::withMessages([
                'file' => 'Berkas tidak berisi baris data. Gunakan template yang disediakan.',
            ]);
        }

        $context = $this->validationContext($batch, $prepared);
        $records = [];
        $valid = 0;
        $invalid = 0;
        $now = now();

        foreach ($prepared as $item) {
            [$data, $errors] = $this->validateRow($item['raw'], $context);

            if ($errors === []) {
                $valid++;
                $status = ImportRow::STATUS_VALID;
            } else {
                $invalid++;
                $status = ImportRow::STATUS_INVALID;
            }

            $records[] = [
                'id' => (string) Str::uuid(),
                'institution_id' => $batch->institution_id,
                'import_batch_id' => $batch->id,
                'row_number' => $item['row_number'],
                'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
                'errors' => $errors === [] ? null : json_encode($errors, JSON_UNESCAPED_UNICODE),
                'status' => $status,
                'asesi_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($records, 100) as $chunk) {
            ImportRow::insert($chunk);
        }

        $batch->update([
            'total_rows' => count($records),
            'valid_rows' => $valid,
            'invalid_rows' => $invalid,
        ]);
    }

    /**
     * @param  array<int, array{row_number: int, raw: array<string, mixed>}>  $prepared
     * @return array<string, mixed>
     */
    private function validationContext(ImportBatch $batch, array $prepared): array
    {
        $nikCounts = [];
        $emailCounts = [];

        foreach ($prepared as $item) {
            $nik = $this->normalizeNik($item['raw']['nik'] ?? null);
            $email = $this->normalizeEmail($item['raw']['email'] ?? null);

            if ($nik !== null) {
                $nikCounts[$nik] = ($nikCounts[$nik] ?? 0) + 1;
            }

            if ($email !== null) {
                $emailCounts[$email] = ($emailCounts[$email] ?? 0) + 1;
            }
        }

        return [
            'nik_counts' => $nikCounts,
            'email_counts' => $emailCounts,
            'existing_nik_hashes' => array_flip(
                User::withTrashed()
                    ->where('institution_id', $batch->institution_id)
                    ->whereNotNull('nik_hash')
                    ->pluck('nik_hash')
                    ->all()
            ),
            'existing_emails' => array_flip(
                User::withTrashed()
                    ->where('institution_id', $batch->institution_id)
                    ->pluck('email')
                    ->map(fn (?string $email): string => strtolower((string) $email))
                    ->all()
            ),
            'provinces' => array_flip(Province::pluck('code')->all()),
            'cities' => City::pluck('province_code', 'code')->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $context
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    private function validateRow(array $row, array $context): array
    {
        $errors = [];

        $nama = $this->text($row['nama'] ?? null, 150);
        if ($nama === null) {
            $errors['nama'] = 'Nama wajib diisi.';
        }

        $nik = $this->normalizeNik($row['nik'] ?? null);
        if ($nik === null) {
            $errors['nik'] = 'NIK wajib diisi.';
        } elseif (! preg_match('/^\d{16}$/', $nik)) {
            $errors['nik'] = 'NIK harus 16 digit angka.';
        } elseif (($context['nik_counts'][$nik] ?? 0) > 1) {
            $errors['nik'] = 'NIK duplikat di dalam berkas.';
        } elseif (isset($context['existing_nik_hashes'][User::hashNik($nik)])) {
            $errors['nik'] = 'NIK sudah terdaftar pada lembaga ini.';
        }

        $email = $this->normalizeEmail($row['email'] ?? null);
        if ($email === null) {
            $errors['email'] = 'Email wajib diisi.';
        } elseif (! filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
            $errors['email'] = 'Format email tidak valid.';
        } elseif (($context['email_counts'][$email] ?? 0) > 1) {
            $errors['email'] = 'Email duplikat di dalam berkas.';
        } elseif (isset($context['existing_emails'][$email])) {
            $errors['email'] = 'Email sudah terdaftar pada lembaga ini.';
        }

        $telepon = $this->text($row['telepon'] ?? null, 20);
        if ($telepon === null) {
            $errors['telepon'] = 'Nomor telepon wajib diisi.';
        }

        $jenisKelamin = $this->normalizeGender($row['jenis_kelamin'] ?? null);
        if ($jenisKelamin === null) {
            $errors['jenis_kelamin'] = 'Jenis kelamin wajib L atau P.';
        }

        $alamat = $this->text($row['alamat'] ?? null, 1000);
        if ($alamat === null) {
            $errors['alamat'] = 'Alamat wajib diisi.';
        }

        $pendidikan = $this->text($row['pendidikan_terakhir'] ?? null, 100);
        if ($pendidikan === null) {
            $errors['pendidikan_terakhir'] = 'Pendidikan terakhir wajib diisi.';
        }

        $tanggalLahir = null;
        $rawTanggal = $row['tanggal_lahir'] ?? null;
        if (filled($rawTanggal)) {
            $tanggalLahir = $this->normalizeDate($rawTanggal);

            if ($tanggalLahir === null) {
                $errors['tanggal_lahir'] = 'Format tanggal lahir tidak valid (YYYY-MM-DD).';
            } elseif (Carbon::parse($tanggalLahir)->isFuture()) {
                $errors['tanggal_lahir'] = 'Tanggal lahir tidak boleh di masa depan.';
            }
        }

        $provinsi = $this->normalizeCode($row['kode_provinsi'] ?? null);
        if ($provinsi !== null && ! isset($context['provinces'][$provinsi])) {
            $errors['kode_provinsi'] = 'Kode provinsi tidak ditemukan.';
        }

        $kota = $this->normalizeCode($row['kode_kota'] ?? null);
        if ($kota !== null) {
            if (! isset($context['cities'][$kota])) {
                $errors['kode_kota'] = 'Kode kota/kabupaten tidak ditemukan.';
            } elseif ($provinsi !== null && $context['cities'][$kota] !== $provinsi) {
                $errors['kode_kota'] = 'Kota/kabupaten tidak berada di provinsi yang dipilih.';
            }
        }

        $kecamatan = $this->normalizeCode($row['kode_kecamatan'] ?? null);
        if ($kecamatan !== null) {
            $district = District::where('code', $kecamatan)->first();

            if ($district === null) {
                $errors['kode_kecamatan'] = 'Kode kecamatan tidak ditemukan.';
            } elseif ($kota !== null && $district->city_code !== $kota) {
                $errors['kode_kecamatan'] = 'Kecamatan tidak berada di kota/kabupaten yang dipilih.';
            }
        }

        return [
            [
                'nama' => $nama,
                'nik' => $nik,
                'email' => $email,
                'telepon' => $telepon,
                'jenis_kelamin' => $jenisKelamin,
                'tempat_lahir' => $this->text($row['tempat_lahir'] ?? null, 100),
                'tanggal_lahir' => $tanggalLahir,
                'alamat' => $alamat,
                'kode_provinsi' => $provinsi,
                'kode_kota' => $kota,
                'kode_kecamatan' => $kecamatan,
                'pendidikan_terakhir' => $pendidikan,
                'pekerjaan' => $this->text($row['pekerjaan'] ?? null, 200),
                'jabatan' => $this->text($row['jabatan'] ?? null, 150),
            ],
            $errors,
        ];
    }

    private function sendActivationLink(User $user): void
    {
        $url = URL::temporarySignedRoute(
            'asesi.activate',
            now()->addDays(7),
            ['user' => $user->id],
        );

        $this->outbox->queue(
            $user,
            'aktivasi_akun',
            'Aktivasi Akun SiapKompeten',
            sprintf(
                'Akun Anda telah dibuat oleh admin LSP. Atur kata sandi melalui tautan berikut (berlaku 7 hari): %s',
                $url,
            ),
            $user,
        );
    }

    private function nextSequence(string $institutionId): int
    {
        $prefix = now()->format('Ymd');

        return Asesi::withoutTenantScope()
            ->where('institution_id', $institutionId)
            ->where('no_registrasi', 'like', $prefix.'%')
            ->count() + 1;
    }

    private function nextNoRegistrasi(string $institutionId, int &$sequence): string
    {
        $prefix = now()->format('Ymd');

        do {
            $number = $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while (
            Asesi::withoutTenantScope()
                ->where('institution_id', $institutionId)
                ->where('no_registrasi', $number)
                ->exists()
        );

        return $number;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (filled($value)) {
                return false;
            }
        }

        return true;
    }

    private function text(mixed $value, int $maxLength): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $string = trim((string) $value);

        if ($string === '') {
            return null;
        }

        return mb_substr($string, 0, $maxLength);
    }

    private function normalizeNik(mixed $value): ?string
    {
        $digits = preg_replace('/\D+/', '', $this->numericString($value) ?? '');

        return $digits === '' ? null : $digits;
    }

    private function normalizeEmail(mixed $value): ?string
    {
        $string = $this->text($value, 150);

        return $string === null ? null : strtolower($string);
    }

    private function normalizeCode(mixed $value): ?string
    {
        $string = $this->numericString($value);

        return $string === null ? null : ltrim($string, "'");
    }

    private function numericString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_float($value)) {
            return number_format($value, 0, '', '');
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private function normalizeGender(mixed $value): ?string
    {
        $string = strtoupper(trim((string) $value));

        return match ($string) {
            'L', 'LAKI-LAKI', 'LAKI LAKI', 'PRIA' => 'L',
            'P', 'PEREMPUAN', 'WANITA' => 'P',
            default => null,
        };
    }

    private function normalizeDate(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (Throwable) {
                return null;
            }
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }
}
