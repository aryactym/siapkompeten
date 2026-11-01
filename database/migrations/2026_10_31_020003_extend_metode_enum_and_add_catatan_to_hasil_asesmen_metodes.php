<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * MAK-02 Refactor — Commit 1c: hasil_asesmen_metodes
 *
 * Perubahan:
 *   1. Perluas ENUM `metode` — tambah: demonstrasi, wawancara, portofolio.
 *      Nilai lama (observasi, tes_lisan, tes_tertulis) tetap valid.
 *   2. Tambah kolom `catatan` (text nullable).
 *   3. Deprecated: `pencapaian` (boolean) — digantikan oleh `keputusan` enum.
 *      Kolom dipertahankan; tidak diisi lagi oleh UI baru.
 */
return new class extends Migration
{
    public function up(): void
    {
        // SQLite tidak mendukung MODIFY COLUMN / ENUM — lewati
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                ALTER TABLE hasil_asesmen_metodes
                MODIFY COLUMN metode ENUM(
                    'observasi','tes_lisan','tes_tertulis',
                    'demonstrasi','wawancara','portofolio'
                ) NOT NULL
            ");
        }

        Schema::table('hasil_asesmen_metodes', function (Blueprint $table): void {
            $table->text('catatan')->nullable()->after('keputusan')
                ->comment('Catatan metode asesmen');
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Hapus baris dengan metode baru sebelum menyempitkan ENUM
            DB::statement("
                DELETE FROM hasil_asesmen_metodes
                WHERE metode IN ('demonstrasi','wawancara','portofolio')
            ");

            DB::statement("
                ALTER TABLE hasil_asesmen_metodes
                MODIFY COLUMN metode ENUM('observasi','tes_lisan','tes_tertulis') NOT NULL
            ");
        }

        Schema::table('hasil_asesmen_metodes', function (Blueprint $table): void {
            $table->dropColumn('catatan');
        });
    }
};
