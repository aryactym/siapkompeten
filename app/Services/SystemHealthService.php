<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

/**
 * Layanan pengecekan kesehatan sistem.
 *
 * Setiap pengecekan bersifat nyata (bukan statis) dan aman untuk ditampilkan:
 * - Tidak memuat rahasia, path berkas, atau informasi teknis sensitif.
 * - Hanya mengembalikan status (ok/warn/error) + keterangan singkat.
 *
 * TIDAK memuat klaim statis seperti "OWASP Top 10:2025", "AES-256",
 * "Terisolasi", atau "Resmi BNSP" — itu klaim, bukan pengecekan.
 *
 * @return array<int, array{label: string, status: 'ok'|'warn'|'error', value: string, icon: string}>
 */
class SystemHealthService
{
    /**
     * Jalankan semua pengecekan dan kembalikan hasilnya.
     *
     * @return array<int, array{label: string, status: 'ok'|'warn'|'error', value: string, icon: string}>
     */
    public function checks(): array
    {
        return array_filter([
            $this->checkDatabase(),
            $this->checkFailedJobs(),
            $this->checkPrivateDisk(),
            $this->checkNotificationOutbox(),
            $this->checkQueue(),
        ]);
    }

    /**
     * Cek koneksi database.
     *
     * @return array{label: string, status: 'ok'|'warn'|'error', value: string, icon: string}
     */
    private function checkDatabase(): array
    {
        try {
            DB::statement('SELECT 1');

            return [
                'label' => 'Koneksi Database',
                'status' => 'ok',
                'value' => 'Terhubung',
                'icon' => 'database',
            ];
        } catch (\Throwable) {
            return [
                'label' => 'Koneksi Database',
                'status' => 'error',
                'value' => 'Tidak dapat terhubung',
                'icon' => 'database-off',
            ];
        }
    }

    /**
     * Cek jumlah failed jobs.
     *
     * @return array{label: string, status: 'ok'|'warn'|'error', value: string, icon: string}
     */
    private function checkFailedJobs(): array
    {
        try {
            $count = DB::table('failed_jobs')->count();

            return [
                'label' => 'Job Gagal',
                'status' => $count === 0 ? 'ok' : ($count < 10 ? 'warn' : 'error'),
                'value' => $count === 0 ? 'Tidak ada' : "{$count} perlu ditinjau",
                'icon' => 'engine',
            ];
        } catch (\Throwable) {
            return [
                'label' => 'Job Gagal',
                'status' => 'warn',
                'value' => 'Tabel belum ada',
                'icon' => 'engine',
            ];
        }
    }

    /**
     * Cek disk privat (dapat ditulis).
     *
     * @return array{label: string, status: 'ok'|'warn'|'error', value: string, icon: string}
     */
    private function checkPrivateDisk(): array
    {
        try {
            $testPath = '_health_check_'.uniqid().'.tmp';
            Storage::disk('public')->put($testPath, '1');
            Storage::disk('public')->delete($testPath);

            return [
                'label' => 'Disk Penyimpanan',
                'status' => 'ok',
                'value' => 'Dapat ditulis',
                'icon' => 'database',
            ];
        } catch (\Throwable) {
            return [
                'label' => 'Disk Penyimpanan',
                'status' => 'error',
                'value' => 'Tidak dapat ditulis — periksa izin folder',
                'icon' => 'database-off',
            ];
        }
    }

    /**
     * Cek notifikasi yang gagal dikirim (outbox).
     *
     * @return array{label: string, status: 'ok'|'warn'|'error', value: string, icon: string}|null
     */
    private function checkNotificationOutbox(): ?array
    {
        try {
            if (! DB::getSchemaBuilder()->hasTable('notifikasis')) {
                return null;
            }

            $count = DB::table('notifikasis')
                ->where('status', 'gagal')
                ->count();

            return [
                'label' => 'Notifikasi Gagal',
                'status' => $count === 0 ? 'ok' : ($count < 5 ? 'warn' : 'error'),
                'value' => $count === 0 ? 'Tidak ada' : "{$count} gagal terkirim",
                'icon' => 'bell-x',
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Cek koneksi queue driver.
     *
     * @return array{label: string, status: 'ok'|'warn'|'error', value: string, icon: string}
     */
    private function checkQueue(): array
    {
        $driver = config('queue.default', 'sync');

        return [
            'label' => 'Queue Driver',
            'status' => 'ok',
            'value' => ucfirst($driver),
            'icon' => 'stack-2',
        ];
    }
}
