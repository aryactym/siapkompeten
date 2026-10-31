<?php

namespace App\Services;

use App\Models\Pendaftaran;
use App\Support\TenantContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class BuktiBayarStorage
{
    public const DISK = 'local';

    /**
     * Simpan bukti pembayaran pada disk privat dengan path per tenant (A01/A04).
     */
    public function store(Pendaftaran $pendaftaran, UploadedFile $file): string
    {
        if ($pendaftaran->institution_id !== TenantContext::id()) {
            throw new RuntimeException('Berkas hanya dapat disimpan pada lembaga aktif.');
        }

        $extension = strtolower($file->extension() ?: $file->getClientOriginalExtension() ?: 'bin');
        $filename = Str::uuid()->toString().'.'.$extension;
        $directory = 'tenants/'.$pendaftaran->institution_id.'/pembayaran/'.$pendaftaran->id;

        // Gunakan put() dengan konten file langsung untuk menghindari
        // ValueError pada PHP 8.4 saat getRealPath() mengembalikan false.
        $contents = $file->get();
        $path = $directory.'/'.$filename;

        $stored = Storage::disk(self::DISK)->put($path, $contents);

        if (! $stored) {
            throw new RuntimeException('Berkas gagal disimpan.');
        }

        return $path;
    }

    public function delete(?string $path): void
    {
        if (filled($path)) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    public function exists(?string $path): bool
    {
        return filled($path) && Storage::disk(self::DISK)->exists($path);
    }
}
