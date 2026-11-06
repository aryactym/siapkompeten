<?php

namespace App\Http\Controllers\Admin;

use App\Actions\CreateAsesiManual;
use App\Http\Controllers\Controller;
use App\Models\Asesi;
use App\Models\Pendaftaran;
use App\Models\Skema;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Province;

class AsesiController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->string('tab')->toString() ?: 'semua';
        $provinsiCode = $request->string('provinsi_code')->toString();
        $kotaCode = $request->string('kota_code')->toString();
        $dari = $request->string('dari')->toString() ?: now()->subYear()->toDateString();
        $sampai = $request->string('sampai')->toString() ?: now()->toDateString();

        $asesis = Asesi::query()
            ->with(['user', 'province', 'city'])
            ->when($request->filled('q'), function ($query) use ($request): void {
                $search = '%'.$request->string('q')->trim().'%';
                $query->where(function ($query) use ($search): void {
                    $query->where('no_registrasi', 'like', $search)
                        ->orWhereHas('user', function ($userQuery) use ($search): void {
                            $userQuery->where('name', 'like', $search)
                                ->orWhere('email', 'like', $search);
                        });
                });
            })
            ->when($provinsiCode !== '', fn ($query) => $query->where('provinsi_code', $provinsiCode))
            ->when($kotaCode !== '', fn ($query) => $query->where('kota_code', $kotaCode))
            ->whereBetween('created_at', [$dari.' 00:00:00', $sampai.' 23:59:59'])
            ->when($tab === 'belum_terverifikasi', fn ($query) => $query->belumTerverifikasi())
            ->when($tab === 'terverifikasi', fn ($query) => $query->terverifikasi())
            ->when($tab === 'kompeten', fn ($query) => $query->where('status_kompetensi', 'kompeten'))
            ->when($tab === 'belum_kompeten', fn ($query) => $query->where('status_kompetensi', 'belum_kompeten'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.asesi.index', [
            'asesis' => $asesis,
            'tab' => $tab,
            'search' => $request->string('q')->toString(),
            'provinsiCode' => $provinsiCode,
            'kotaCode' => $kotaCode,
            'dari' => $dari,
            'sampai' => $sampai,
            'provinces' => Province::orderBy('name')->get(),
            'cities' => City::query()
                ->when($provinsiCode !== '', fn ($query) => $query->where('province_code', $provinsiCode))
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Asesi::class);

        return view('admin.asesi.form', [
            'asesi' => new Asesi,
            'provinces' => Province::orderBy('name')->get(),
            'cities' => City::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, CreateAsesiManual $action): RedirectResponse
    {
        $this->authorize('create', Asesi::class);

        $tenantId = (string) TenantContext::requireId();

        $validated = $request->validate([
            // Data akun user
            'name' => ['required', 'string', 'max:150'],
            'nik' => [
                'required',
                'string',
                'digits:16',
                // Cek unik NIK melalui nik_hash (blind index) per tenant
                function (string $attribute, mixed $value, \Closure $fail) use ($tenantId): void {
                    $hash = User::hashNik($value);
                    $exists = User::where('institution_id', $tenantId)
                        ->where('nik_hash', $hash)
                        ->exists();
                    if ($exists) {
                        $fail('NIK ini sudah terdaftar di sistem.');
                    }
                },
            ],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')
                    ->where('institution_id', $tenantId),
            ],
            'phone' => ['required', 'string', 'max:20'],
            // Data profil asesi
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date', 'before:today'],
            'kebangsaan' => ['required', 'string', 'max:100'],
            'alamat_rumah' => ['nullable', 'string', 'max:1000'],
            'kecamatan_code' => ['nullable', 'string', 'max:7'],
            'kota_code' => ['nullable', 'string', 'max:4'],
            'provinsi_code' => ['nullable', 'string', 'max:2'],
            'kode_pos' => ['nullable', 'string', 'max:10'],
            'telp_rumah' => ['nullable', 'string', 'max:50'],
            'telp_kantor' => ['nullable', 'string', 'max:50'],
            'pendidikan_terakhir' => ['nullable', 'string', 'max:100'],
            'pekerjaan_instansi' => ['nullable', 'string', 'max:200'],
            'jabatan' => ['nullable', 'string', 'max:150'],
            'alamat_kantor' => ['nullable', 'string', 'max:1000'],
        ]);

        $asesi = $action->execute($validated, $request->user());

        return redirect()
            ->route('admin.asesi.show', $asesi)
            ->with('success', 'Asesi berhasil ditambahkan. Tautan aktivasi telah dikirim ke email asesi.');
    }

    public function show(Asesi $asesi): View
    {
        $this->authorize('view', $asesi);

        $asesi->load([
            'user',
            'province',
            'city',
            'pendaftarans.skema',
            'pendaftarans.jadwal.tuk',
        ]);

        return view('admin.asesi.show', [
            'asesi' => $asesi,
            'skemas' => Skema::aktif()->orderBy('nama_skema')->get(),
        ]);
    }

    public function edit(Asesi $asesi): View
    {
        $this->authorize('view', $asesi);

        return view('admin.asesi.form', [
            'asesi' => $asesi->load('user'),
            'provinces' => Province::orderBy('name')->get(),
            'cities' => City::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Asesi $asesi): RedirectResponse
    {
        $this->authorize('view', $asesi);

        $tenantId = (string) TenantContext::requireId();
        $user = $asesi->user;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')
                    ->where('institution_id', $tenantId)
                    ->ignore($user->id),
            ],
            'phone' => ['required', 'string', 'max:20'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date', 'before:today'],
            'kebangsaan' => ['nullable', 'string', 'max:100'],
            'alamat_rumah' => ['nullable', 'string', 'max:1000'],
            'kelurahan' => ['nullable', 'string', 'max:100'],
            'provinsi_code' => ['nullable', 'string', 'max:2'],
            'kota_code' => ['nullable', 'string', 'max:4'],
            'kode_pos' => ['nullable', 'string', 'max:10'],
            'telp_rumah' => ['nullable', 'string', 'max:50'],
            'pekerjaan_instansi' => ['nullable', 'string', 'max:200'],
            'jabatan' => ['nullable', 'string', 'max:150'],
            'alamat_kantor' => ['nullable', 'string', 'max:1000'],
            'telp_kantor' => ['nullable', 'string', 'max:50'],
            'pendidikan_terakhir' => ['nullable', 'string', 'max:100'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        $asesi->update(collect($validated)->except(['name', 'email', 'phone'])->all());

        activity('asesi')
            ->causedBy($request->user())
            ->performedOn($asesi)
            ->log('Admin memperbarui data asesi.');

        return redirect()
            ->route('admin.asesi.show', $asesi)
            ->with('success', 'Data asesi berhasil diperbarui.');
    }

    public function storePendaftaran(Request $request, Asesi $asesi): RedirectResponse
    {
        $this->authorize('view', $asesi);
        $this->authorize('create', Pendaftaran::class);

        $tenantId = (string) TenantContext::requireId();

        $validated = $request->validate([
            'skema_id' => [
                'required',
                'uuid',
                Rule::exists('skemas', 'id')
                    ->where('institution_id', $tenantId)
                    ->where('is_active', true),
            ],
            'tujuan_asesmen' => ['required', Rule::in(array_keys(Pendaftaran::TUJUAN_LABELS))],
        ]);

        $alreadyActive = $asesi->pendaftarans()
            ->aktif()
            ->where('skema_id', $validated['skema_id'])
            ->exists();

        if ($alreadyActive) {
            return back()->with('error', 'Asesi sudah memiliki pendaftaran aktif pada skema tersebut.');
        }

        $pendaftaran = DB::transaction(function () use ($asesi, $validated, $request): Pendaftaran {
            $pendaftaran = Pendaftaran::create([
                'no_pendaftaran' => $this->generateNoPendaftaran($asesi->institution_id),
                'asesi_id' => $asesi->id,
                'skema_id' => $validated['skema_id'],
                'tujuan_asesmen' => $validated['tujuan_asesmen'],
                'status' => Pendaftaran::STATUS_DRAF,
            ]);

            activity('pendaftaran')
                ->causedBy($request->user())
                ->performedOn($pendaftaran)
                ->log('Admin mendaftarkan asesi ke skema sertifikasi.');

            return $pendaftaran;
        });

        return redirect()
            ->route('admin.pendaftaran.show', $pendaftaran)
            ->with('success', 'Asesi berhasil didaftarkan ke skema. Unggah dokumen persyaratan dapat dilakukan dari halaman ini atau oleh asesi secara mandiri.');
    }

    public function setPassword(Request $request, Asesi $asesi): RedirectResponse
    {
        $this->authorize('resetPassword', $asesi);

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $asesi->user->update(['password' => $validated['password']]);

        activity('asesi')
            ->causedBy($request->user())
            ->performedOn($asesi)
            ->log('Admin menetapkan kata sandi akun asesi.');

        return back()->with('success', 'Kata sandi asesi berhasil diperbarui.');
    }

    public function verify(Request $request, Asesi $asesi): RedirectResponse
    {
        $this->authorize('verify', $asesi);

        $asesi->update(['status_verifikasi' => 'terverifikasi']);

        activity('asesi')
            ->causedBy($request->user())
            ->performedOn($asesi)
            ->log('Status verifikasi asesi diubah menjadi terverifikasi.');

        return back()->with('success', 'Asesi berhasil diverifikasi.');
    }

    private function generateNoPendaftaran(string $institutionId): string
    {
        $prefix = now()->format('Ymd');
        $sequence = Pendaftaran::withoutTenantScope()
            ->where('institution_id', $institutionId)
            ->where('no_pendaftaran', 'like', $prefix.'%')
            ->count() + 1;

        do {
            $number = $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while (
            Pendaftaran::withoutTenantScope()
                ->where('institution_id', $institutionId)
                ->where('no_pendaftaran', $number)
                ->exists()
        );

        return $number;
    }
}
