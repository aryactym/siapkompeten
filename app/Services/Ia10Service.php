<?php

namespace App\Services;

use App\Models\Ia10Asesmen;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Ia10Service
{
    public function ensure(Pendaftaran $p): Ia10Asesmen
    {
        return Ia10Asesmen::firstOrCreate(['pendaftaran_id' => $p->id], ['institution_id' => $p->institution_id, 'status' => Ia10Asesmen::STATUS_DRAF]);
    }

    public function simpan(Ia10Asesmen $ia, array $data, User $oleh): Ia10Asesmen
    {
        if ($ia->isFinal()) {
            throw ValidationException::withMessages(['ia10' => 'Sudah final.']);
        }
        $ia->fill([
            'nama_pihak_ketiga' => $data['nama_pihak_ketiga'] ?? null,
            'jabatan' => $data['jabatan'] ?? null,
            'instansi' => $data['instansi'] ?? null,
            'klarifikasi' => $data['klarifikasi'] ?? null,
        ]);
        $ia->save();

        return $ia->fresh();
    }

    public function finalkan(Ia10Asesmen $ia, User $oleh): Ia10Asesmen
    {
        if ($ia->isFinal()) {
            throw ValidationException::withMessages(['ia10' => 'Sudah final.']);
        }
        if (blank($ia->klarifikasi)) {
            throw ValidationException::withMessages(['klarifikasi' => 'Klarifikasi wajib diisi.']);
        }
        DB::transaction(function () use ($ia, $oleh) {
            $ia->update(['status' => Ia10Asesmen::STATUS_FINAL, 'finalized_at' => now(), 'finalized_by' => $oleh->id]);
            activity('asesmen')->causedBy($oleh)->performedOn($ia->pendaftaran)->log('Asesor memfinalkan FR.IA.10.');
        });

        return $ia->fresh();
    }
}
