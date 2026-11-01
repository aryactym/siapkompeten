<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MAK-02 Refactor — Commit 1b: hasil_asesmen_units
 *
 * Tambah:
 *   - catatan (text nullable) — wajib diisi bila keputusan berbeda dari saran otomatis.
 *
 * Deprecated:
 *   - pencapaian (boolean) — kini jadi indikator hitung otomatis di PHP, bukan input.
 *     Kolom dipertahankan untuk backward compat; tidak diisi lagi oleh UI baru.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hasil_asesmen_units', function (Blueprint $table): void {
            $table->text('catatan')->nullable()->after('pencapaian')
                ->comment('Wajib bila keputusan berbeda dari saran otomatis');
        });
    }

    public function down(): void
    {
        Schema::table('hasil_asesmen_units', function (Blueprint $table): void {
            $table->dropColumn('catatan');
        });
    }
};
