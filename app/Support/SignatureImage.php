<?php

namespace App\Support;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Resolusi tanda tangan dari database terenkripsi untuk kebutuhan PDF.
 */
final class SignatureImage
{
    /**
     * Kembalikan data URI PNG, atau null bila belum tersimpan.
     */
    public static function for(?Model $model): ?string
    {
        if ($model instanceof User) {
            return self::dataUri($model->signature_data);
        }

        if ($model instanceof Institution) {
            return self::dataUri($model->tanda_tangan_data);
        }

        return null;
    }

    /**
     * Simpan payload base64 sebagai PNG terenkripsi.
     *
     * @throws \InvalidArgumentException
     */
    public static function encodePng(string $binary): array
    {
        $info = @getimagesizefromstring($binary);
        if (! is_array($info) || ! in_array($info['mime'] ?? null, ['image/png', 'image/jpeg'], true)) {
            throw new \InvalidArgumentException('Berkas bukan gambar PNG/JPEG yang valid.');
        }

        $image = @imagecreatefromstring($binary);
        if ($image === false) {
            throw new \InvalidArgumentException('Gambar tidak dapat diproses.');
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $opaque = imagecreatetruecolor($width, $height);
        imagealphablending($opaque, false);
        imagefill($opaque, 0, 0, imagecolorallocate($opaque, 255, 255, 255));
        imagealphablending($opaque, true);
        imagecopy($opaque, $image, 0, 0, 0, 0, $width, $height);

        ob_start();
        imagepng($opaque);
        $png = ob_get_clean();
        imagedestroy($opaque);
        imagedestroy($image);

        if (! is_string($png) || $png === '') {
            throw new \InvalidArgumentException('Gambar tidak dapat diproses.');
        }

        return [
            'data' => base64_encode($png),
            'hash' => hash('sha256', $png),
        ];
    }

    public static function dataUri(?string $base64): ?string
    {
        if (blank($base64)) {
            return null;
        }

        return 'data:image/png;base64,'.$base64;
    }
}
