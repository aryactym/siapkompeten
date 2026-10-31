<?php

namespace App\Services;

use App\Support\TenantContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class InstitutionStorage
{
    /**
     * Simpan berkas unggahan pada folder lembaga aktif.
     *
     * Memakai Storage::putFileAs sehingga pemisah direktori ditangani Laravel
     * (aman untuk Windows 11, Linux, dan driver storage lain).
     */
    public function store(UploadedFile $file): string
    {
        return $this->storeForInstitution(TenantContext::requireId(), $file);
    }

    public function storeForInstitution(string $institutionId, UploadedFile $file, string $folder = 'uploads'): string
    {
        $extension = strtolower(
            $file->extension() ?: $file->getClientOriginalExtension() ?: 'bin'
        );

        $filename = Str::uuid()->toString().'.'.$extension;

        $path = Storage::disk('public')->putFileAs(
            $folder.'/'.$institutionId,
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
        if ($path !== null && $path !== '') {
            Storage::disk('public')->delete($path);
        }
    }
}
