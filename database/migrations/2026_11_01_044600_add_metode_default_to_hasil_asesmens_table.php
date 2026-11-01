<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FR.AK.02 Refactor — Commit 2a: Default metode per pendaftaran (simplified)
 *
 * Perubahan:
 *   1. Tambah kolom `metode_default` (JSON array) pada hasil_asesmens
 *      — menyimpan array metode yang dipakai untuk semua unit
 *   2. Kolom lama di hasil_asesmen_metodes tetap; deprecated tapi tidak dihapus
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hasil_asesmens', function (Blueprint $table): void {
            $table->json('metode_default')->nullable()->after('status')
                ->comment('Default metode asesmen (array) — diterapkan ke semua unit');
        });
    }

    public function down(): void
    {
        Schema::table('hasil_asesmens', function (Blueprint $table): void {
            $table->dropColumn('metode_default');
        });
    }
};
