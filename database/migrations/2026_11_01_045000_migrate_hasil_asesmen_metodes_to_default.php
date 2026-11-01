<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * FR.AK.02 Refactor — Commit 5a: Migrate hasil_asesmen_metodes → metode_default
 *
 * Semua unit dengan metode yang sama → jadi default.
 * Jika unit berbeda → ambil metode pertama/terbanyak.
 * Hasil: set metode_default pada hasil_asesmens dari data existing.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Ambil semua hasil dan metode per unit
        $hasilList = DB::table('hasil_asesmens as h')
            ->select('h.id', 'h.institution_id')
            ->get();

        foreach ($hasilList as $hasil) {
            // Ambil metode per unit untuk hasil ini
            $metodePerUnit = DB::table('hasil_asesmen_metodes as m')
                ->join('hasil_asesmen_units as hu', 'm.hasil_unit_id', '=', 'hu.id')
                ->where('hu.hasil_id', $hasil->id)
                ->pluck('m.metode');

            // Ambil unique metode
            $metodeUnique = $metodePerUnit->unique()->values()->toArray();

            // Set default
            DB::table('hasil_asesmens')
                ->where('id', $hasil->id)
                ->update(['metode_default' => json_encode($metodeUnique)]);

            // Catat override sementara di metodes? Tidak perlu - default sudah cukup
            // Semua unit akan pakai metode ini
        }
    }

    public function down(): void
    {
        // Tidak revert data migrasi
    }
};
