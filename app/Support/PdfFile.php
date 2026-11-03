<?php

namespace App\Support;

use RuntimeException;

/**
 * Resolusi berkas tanda tangan/logo yang disimpan di disk publik.
 *
 * PDF DomPDF membaca berkas langsung dari filesystem, sehingga membutuhkan
 * symlink `public/storage`. Bila symlink belum dibuat, tanda tangan akan
 * hilang diam-diam. Helper ini melempar galat yang jelas agar operator
 * diingatkan menjalankan `php artisan storage:link`.
 */
final class PdfFile
{
    /**
     * Path absolut berkas di disk publik, atau null bila tidak tersedia.
     *
     * @throws RuntimeException bila symlink `public/storage` belum dibuat.
     */
    public static function path(?string $relativePath): ?string
    {
        if ($relativePath === null || trim($relativePath) === '') {
            return null;
        }

        self::ensurePublicStorageLink();

        $absolutePath = public_path('storage/'.ltrim($relativePath, '/'));

        return is_file($absolutePath) ? $absolutePath : null;
    }

    /**
     * Pastikan symlink `public/storage` tersedia sebelum membaca berkas.
     *
     * @throws RuntimeException
     */
    public static function ensurePublicStorageLink(): void
    {
        if (! file_exists(public_path('storage'))) {
            throw new RuntimeException(
                'Direktori public/storage tidak ditemukan. Jalankan "php artisan storage:link" sebelum mencetak PDF.'
            );
        }
    }
}
