<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * MAK-02 Refactor — Commit 1a: hasil_asesmen_kuks
 *
 * Tambah:
 *   - tercapai (boolean nullable) — menggantikan pencapaian (boolean non-null).
 *                                   null = belum dinilai, true = ya, false = tidak.
 *   - catatan  (text nullable)    — catatan asesor per KUK.
 *
 * Deprecated (kolom lama dipertahankan, tidak dihapus):
 *   - pencapaian  → data lama dimigrasikan ke tercapai (1 → true, 0 → null).
 *   - bukti_no    → deprecated; asesor kini memilih teks bukti dari apl02_jawabans.
 *   - valid, autentik, terkini, memadai → deprecated; diganti VATM di level unit (commit berikutnya).
 *     Kolom `autentik` secara semantik sama dengan `asli` — rename dilakukan di sini.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hasil_asesmen_kuks', function (Blueprint $table): void {
            // Kolom baru
            $table->boolean('tercapai')->nullable()->after('kuk_id')
                ->comment('null=belum dinilai, true=tercapai, false=tidak tercapai');
            $table->text('catatan')->nullable()->after('tercapai')
                ->comment('Catatan asesor untuk KUK ini');
            // Rename autentik → asli (semantik VATM: Valid, Asli, Terkini, Memadai)
            $table->renameColumn('autentik', 'asli');
        });

        // Migrasi data: pencapaian=true → tercapai=true; false → null (belum dinilai bukan "tidak")
        DB::statement('UPDATE hasil_asesmen_kuks SET tercapai = CASE WHEN pencapaian = 1 THEN 1 ELSE NULL END');
    }

    public function down(): void
    {
        Schema::table('hasil_asesmen_kuks', function (Blueprint $table): void {
            $table->renameColumn('asli', 'autentik');
            $table->dropColumn(['tercapai', 'catatan']);
        });
    }
};
