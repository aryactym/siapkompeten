<?php

namespace App\Services;

use App\Models\Pendaftaran;
use App\Support\TenantContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class PendaftaranStorage
{
    public const DISK = 'local';

    /**
     * Simpan berkas persyaratan pada disk privat dengan path per tenant.
     */
    public function store(Pendaftaran $pendaftaran, UploadedFile $file): string
    {
        if ($pendaftaran->institution_id !== TenantContext::id()) {
            throw new RuntimeException('Berkas hanya dapat disimpan pada lembaga aktif.');
        }

        if (! $file->isValid()) {
            throw new RuntimeException(
                'Berkas gagal diunggah (kode error: '.$file->getError().'). Coba lagi atau gunakan berkas lain.'
            );
        }

        $extension = strtolower(
            $file->getClientOriginalExtension()
                ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION)
                ?: 'bin'
        );

        $relativePath = sprintf(
            'tenants/%s/pendaftaran/%s/%s.%s',
            $pendaftaran->institution_id,
            $pendaftaran->id,
            Str::uuid()->toString(),
            $extension
        );

        // Gunakan fopen() langsung pada path temp file, bukan pass UploadedFile object,
        // karena getRealPath() di PHP 8.4/Windows kadang return false meski isValid() true.
        $tempPath = $file->getPathname();

        if (blank($tempPath) || ! is_readable($tempPath)) {
            throw new RuntimeException('Berkas sementara tidak dapat dibaca. Coba unggah ulang.');
        }

        $stream = fopen($tempPath, 'rb');

        if ($stream === false) {
            throw new RuntimeException('Berkas sementara tidak dapat dibuka. Coba unggah ulang.');
        }

        try {
            $stored = Storage::disk(self::DISK)->put($relativePath, $stream);
        } finally {
            fclose($stream);
        }

        if (! $stored) {
            throw new RuntimeException('Berkas gagal disimpan.');
        }

        return $relativePath;
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
