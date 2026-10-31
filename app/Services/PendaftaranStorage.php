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

        $extension = strtolower($file->extension() ?: $file->getClientOriginalExtension() ?: 'bin');
        $filename = Str::uuid()->toString().'.'.$extension;

        $path = Storage::disk(self::DISK)->putFileAs(
            'tenants/'.$pendaftaran->institution_id.'/pendaftaran/'.$pendaftaran->id,
            $file,
            $filename
        );

        if ($path === false) {
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
