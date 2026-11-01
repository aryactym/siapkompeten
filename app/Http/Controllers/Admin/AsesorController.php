<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asesor;
use App\Models\Skema;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Province;

class AsesorController extends Controller
{
    public function index(Request $request): View
    {
        $searchQuery = $request->string('q')->trim()->toString();
        $selectedStatus = $request->string('status')->trim()->toString();
        $selectedJenisKelamin = $request->string('jenis_kelamin')->trim()->toString();
        $selectedSkema = $request->string('skema')->trim()->toString();

        $asesors = Asesor::query()
            ->with(['user', 'city'])
            ->withCount('penugasan')
            ->when($searchQuery !== '', function ($query) use ($searchQuery): void {
                $like = '%'.$searchQuery.'%';
                $query->where(function ($query) use ($like): void {
                    $query->where('no_register', 'like', $like)
                        ->orWhere('bidang_keahlian', 'like', $like)
                        ->orWhereHas('user', fn ($q) => $q->where('name', 'like', $like));
                });
            })
            ->when($selectedStatus !== '', fn ($query) => $query->where('status', $selectedStatus))
            ->when($selectedJenisKelamin !== '', fn ($query) => $query->where('jenis_kelamin', $selectedJenisKelamin))
            ->when($selectedSkema !== '', function ($query) use ($selectedSkema): void {
                $query->whereHas(
                    'kompetensis',
                    fn ($q) => $q->where('skema_id', $selectedSkema)
                );
            })
            ->orderBy('no_register')
            ->paginate(15)
            ->withQueryString();

        $skemas = Skema::query()->orderBy('nama_skema')->get(['id', 'kode_skema', 'nama_skema']);

        return view('admin.asesor.index', [
            'asesors' => $asesors,
            'searchQuery' => $searchQuery,
            'selectedStatus' => $selectedStatus,
            'selectedJenisKelamin' => $selectedJenisKelamin,
            'selectedSkema' => $selectedSkema,
            'skemas' => $skemas,
        ]);
    }

    public function create(): View
    {
        $candidates = User::role('asesor')
            ->where('institution_id', TenantContext::id())
            ->whereDoesntHave('asesor')
            ->orderBy('name')
            ->get();

        return view('admin.asesor.form', [
            'asesor' => new Asesor(['status' => 'aktif', 'kebangsaan' => 'Indonesia']),
            'candidates' => $candidates,
            'provinces' => Province::orderBy('name')->get(),
            'cities' => City::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = (string) TenantContext::id();

        $validated = $request->validate([
            'user_id' => [
                'required',
                'uuid',
                Rule::exists('users', 'id')->where('institution_id', $tenantId),
                Rule::unique('asesors', 'user_id')->where('institution_id', $tenantId),
            ],
            'no_register' => [
                'required',
                'string',
                'max:100',
                Rule::unique('asesors', 'no_register')->where('institution_id', $tenantId),
            ],
            'no_lisensi' => ['nullable', 'string', 'max:100'],
            'lisensi_berlaku_hingga' => ['nullable', 'date'],
            'gelar_depan' => ['nullable', 'string', 'max:50'],
            'gelar_belakang' => ['nullable', 'string', 'max:50'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date', 'before:today'],
            'kebangsaan' => ['required', 'string', 'max:100'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'province_code' => ['nullable', 'string', 'max:2'],
            'city_code' => ['nullable', 'string', 'max:4'],
            'kode_pos' => ['nullable', 'string', 'max:10'],
            'pendidikan_terakhir' => ['nullable', 'string', 'max:100'],
            'bidang_keahlian' => ['nullable', 'string', 'max:150'],
            'institusi_asal' => ['nullable', 'string', 'max:200'],
            'telepon' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ]);

        $user = User::whereKey($validated['user_id'])->firstOrFail();
        abort_unless($user->hasRole('asesor'), 422, 'Pengguna bukan berperan asesor.');

        $asesor = Asesor::create($validated);

        activity('asesor')
            ->causedBy($request->user())
            ->performedOn($asesor)
            ->log('Profil asesor dibuat.');

        return redirect()
            ->route('admin.asesor.index')
            ->with('success', 'Profil asesor berhasil dibuat.');
    }

    public function edit(Asesor $asesor): View
    {
        return view('admin.asesor.form', [
            'asesor' => $asesor->load('user'),
            'candidates' => collect(),
            'provinces' => Province::orderBy('name')->get(),
            'cities' => City::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Asesor $asesor): RedirectResponse
    {
        $tenantId = (string) TenantContext::id();

        $validated = $request->validate([
            'no_register' => [
                'required',
                'string',
                'max:100',
                Rule::unique('asesors', 'no_register')
                    ->where('institution_id', $tenantId)
                    ->ignore($asesor->id),
            ],
            'no_lisensi' => ['nullable', 'string', 'max:100'],
            'lisensi_berlaku_hingga' => ['nullable', 'date'],
            'gelar_depan' => ['nullable', 'string', 'max:50'],
            'gelar_belakang' => ['nullable', 'string', 'max:50'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date', 'before:today'],
            'kebangsaan' => ['required', 'string', 'max:100'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'province_code' => ['nullable', 'string', 'max:2'],
            'city_code' => ['nullable', 'string', 'max:4'],
            'kode_pos' => ['nullable', 'string', 'max:10'],
            'pendidikan_terakhir' => ['nullable', 'string', 'max:100'],
            'bidang_keahlian' => ['nullable', 'string', 'max:150'],
            'institusi_asal' => ['nullable', 'string', 'max:200'],
            'telepon' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ]);

        $asesor->update($validated);

        activity('asesor')
            ->causedBy($request->user())
            ->performedOn($asesor)
            ->log('Profil asesor diperbarui.');

        return redirect()
            ->route('admin.asesor.index')
            ->with('success', 'Profil asesor berhasil diperbarui.');
    }
}
