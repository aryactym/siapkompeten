<?php

namespace App\Support;

use App\Models\Institution;

/**
 * Single-tenant context: satu instalasi = satu lembaga.
 *
 * Membaca institution aktif langsung dari database (di-cache di memory per request).
 * Method set() dan clear() dipertahankan sebagai no-op agar kode yang sudah ada
 * tidak perlu diubah, namun tidak lagi menulis ke session.
 */
final class TenantContext
{
    /** @deprecated Tidak digunakan di branch single — dipertahankan untuk kompatibilitas. */
    public const SESSION_ID = 'active_institution_id';

    /** @deprecated Tidak digunakan di branch single — dipertahankan untuk kompatibilitas. */
    public const SESSION_NAME = 'active_institution_name';

    /** @deprecated Tidak digunakan di branch single — dipertahankan untuk kompatibilitas. */
    public const SESSION_LOGO = 'active_institution_logo';

    private static ?Institution $cached = null;

    /**
     * Kembalikan institution aktif. Di-cache per-request (static property).
     */
    public static function institution(): ?Institution
    {
        if (self::$cached === null) {
            self::$cached = Institution::query()
                ->where('is_active', true)
                ->orderByDesc('is_setup_completed')
                ->orderBy('name')
                ->first();
        }

        return self::$cached;
    }

    public static function id(): ?string
    {
        return self::institution()?->id;
    }

    public static function name(): ?string
    {
        return self::institution()?->name;
    }

    public static function requireId(): string
    {
        return self::id() ?? throw new \LogicException(
            'Tidak ada institution aktif. Pastikan minimal satu institution dengan is_active=true tersedia di database.'
        );
    }

    /**
     * No-op untuk session di branch single, tapi tetap update in-memory cache
     * agar seeder dan test setup yang memanggil set() bekerja dengan benar.
     */
    public static function set(Institution $institution): void
    {
        self::$cached = $institution;
    }

    /**
     * No-op di branch single. Dipertahankan untuk kompatibilitas dengan logout flow.
     */
    public static function clear(): void
    {
        // single-tenant: tidak ada session tenant yang perlu dibersihkan
        self::$cached = null;
    }
}
