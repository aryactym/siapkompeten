<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Province;

class WilayahController extends Controller
{
    /**
     * Kembalikan daftar kota/kabupaten untuk suatu provinsi.
     *
     * GET /wilayah/provinsi/{code}/kota
     * Throttle: dikonfigurasi di routes/web.php
     */
    public function kotaByProvinsi(string $code): JsonResponse
    {
        // Validasi: kode provinsi harus 2 digit angka
        if (! preg_match('/^\d{2}$/', $code)) {
            abort(404, 'Kode provinsi tidak valid.');
        }

        $provinceExists = Province::where('code', $code)->exists();

        if (! $provinceExists) {
            abort(404, 'Provinsi tidak ditemukan.');
        }

        $cities = City::where('province_code', $code)
            ->orderBy('name')
            ->get(['code', 'name'])
            ->map(fn (City $city) => [
                'code' => $city->code,
                'nama' => $this->normalisasiNamaWilayah($city->name),
            ])
            ->sortBy('nama')
            ->values();

        return response()->json($cities);
    }

    /**
     * Normalisasi nama kota/kabupaten:
     * - Trim spasi berlebih
     * - Title Case
     * - "KAB " / "KAB. " → "Kabupaten "
     * - "KOTA " → "Kota "
     */
    private function normalisasiNamaWilayah(string $name): string
    {
        // Trim dan normalisasi spasi ganda
        $name = trim(preg_replace('/\s+/', ' ', $name));

        // Konversi ke Title Case dulu
        $name = mb_convert_case(mb_strtolower($name), MB_CASE_TITLE, 'UTF-8');

        // Normalisasi awalan "Kab." / "Kab " → "Kabupaten "
        $name = preg_replace('/^Kab\.?\s+/i', 'Kabupaten ', $name);

        return $name;
    }
}
