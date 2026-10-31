<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Portal aktif yang dipilih pengguna saat login.
 *
 * Menyimpan pilihan di session ('active_portal') sehingga seorang user
 * yang memiliki lebih dari satu role (mis. asesor sekaligus asesi) dapat
 * memilih konteks kerja tanpa perlu dua akun terpisah.
 *
 * Nilai yang valid: 'admin' | 'asesor' | 'asesi'
 */
class ActivePortal
{
    public const PORTAL_ADMIN = 'admin';

    public const PORTAL_ASESOR = 'asesor';

    public const PORTAL_ASESI = 'asesi';

    public const SESSION_KEY = 'active_portal';

    /** Simpan portal pilihan ke session. */
    public static function set(Request $request, string $portal): void
    {
        $request->session()->put(self::SESSION_KEY, $portal);
    }

    /** Baca portal aktif dari session. */
    public static function get(Request $request): ?string
    {
        return $request->session()->get(self::SESSION_KEY);
    }

    /** Hapus portal aktif dari session (dipakai saat logout). */
    public static function forget(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
    }

    /**
     * Tentukan portal aktif untuk user.
     * Urutan prioritas jika tidak ada di session:
     *   admin/super-admin → admin | asesor → asesor | asesi → asesi
     */
    public static function resolve(Request $request, User $user): string
    {
        $stored = self::get($request);

        // Pastikan nilai session masih valid untuk role yang dimiliki user.
        if ($stored !== null && self::userCanUsePortal($user, $stored)) {
            return $stored;
        }

        // Fallback: pilih portal tertinggi yang dimiliki user.
        return self::defaultPortal($user);
    }

    /** Apakah user memiliki role yang sesuai untuk portal ini? */
    public static function userCanUsePortal(User $user, string $portal): bool
    {
        return match ($portal) {
            self::PORTAL_ADMIN => $user->hasAnyRole(['super-admin', 'admin', 'pimpinan']),
            self::PORTAL_ASESOR => $user->hasRole('asesor'),
            self::PORTAL_ASESI => $user->hasRole('asesi'),
            default => false,
        };
    }

    /**
     * Daftar portal yang tersedia untuk user (dipakai di view pilihan login).
     *
     * @return array<string, string> portal => label
     */
    public static function availablePortals(User $user): array
    {
        $portals = [];

        if ($user->hasRole('asesi')) {
            $portals[self::PORTAL_ASESI] = 'Peserta Sertifikasi (Asesi)';
        }

        if ($user->hasRole('asesor')) {
            $portals[self::PORTAL_ASESOR] = 'Asesor Kompetensi';
        }

        if ($user->hasAnyRole(['super-admin', 'admin', 'pimpinan'])) {
            $portals[self::PORTAL_ADMIN] = 'Administrator / Pengelola LSP';
        }

        return $portals;
    }

    /** Fallback portal berdasarkan hierarki role. */
    public static function defaultPortalFor(User $user): string
    {
        if ($user->hasAnyRole(['super-admin', 'admin', 'pimpinan'])) {
            return self::PORTAL_ADMIN;
        }

        if ($user->hasRole('asesor')) {
            return self::PORTAL_ASESOR;
        }

        return self::PORTAL_ASESI;
    }

    /** @deprecated Gunakan defaultPortalFor() */
    private static function defaultPortal(User $user): string
    {
        return self::defaultPortalFor($user);
    }
}
