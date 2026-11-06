<?php

namespace App\Services;

use App\Models\Pendaftaran;
use App\Models\User;
use App\Models\ValidasiAsesmen;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ValidasiAsesmenService
{
    /**
     * @param  array{aspek_validitas?: string|null, aspek_reliabilitas?: string|null, aspek_keadilan?: string|null, aspek_kesesuaian?: string|null, keputusan?: string|null, catatan?: string|null}  $data
     */
    public function simpan(Pendaftaran $pendaftaran, array $data, User $oleh): ValidasiAsesmen
    {
        $validasi = ValidasiAsesmen::firstOrNew(['pendaftaran_id' => $pendaftaran->id]);

        if ($validasi->exists && $validasi->isFinal()) {
            throw ValidationException::withMessages([
                'validasi' => 'Validasi sudah final dan tidak dapat diubah.',
            ]);
        }

        $validasi->fill([
            'institution_id' => $pendaftaran->institution_id,
            'validator_id' => $oleh->id,
            'aspek_validitas' => $data['aspek_validitas'] ?? null,
            'aspek_reliabilitas' => $data['aspek_reliabilitas'] ?? null,
            'aspek_keadilan' => $data['aspek_keadilan'] ?? null,
            'aspek_kesesuaian' => $data['aspek_kesesuaian'] ?? null,
            'keputusan' => $data['keputusan'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ]);
        $validasi->save();

        return $validasi;
    }

    public function finalkan(Pendaftaran $pendaftaran, User $oleh): ValidasiAsesmen
    {
        $validasi = ValidasiAsesmen::firstOrNew(['pendaftaran_id' => $pendaftaran->id]);

        if ($validasi->exists && $validasi->isFinal()) {
            throw ValidationException::withMessages([
                'validasi' => 'Validasi sudah final.',
            ]);
        }

        if (blank($validasi->keputusan)) {
            throw ValidationException::withMessages([
                'keputusan' => 'Keputusan validasi wajib diisi sebelum finalisasi.',
            ]);
        }

        return DB::transaction(function () use ($validasi, $pendaftaran, $oleh): ValidasiAsesmen {
            $validasi->fill([
                'institution_id' => $pendaftaran->institution_id,
                'validator_id' => $validasi->validator_id ?? $oleh->id,
                'status' => ValidasiAsesmen::STATUS_FINAL,
                'finalized_at' => now(),
                'finalized_by' => $oleh->id,
            ]);
            $validasi->save();

            activity('asesmen')
                ->causedBy($oleh)
                ->performedOn($pendaftaran)
                ->withProperties(['validasi_id' => $validasi->id, 'keputusan' => $validasi->keputusan])
                ->log('Komite teknis memfinalkan validasi asesmen (FR.VA).');

            return $validasi;
        });
    }
}
