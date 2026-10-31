@extends('layouts.admin')

@section('title', 'Lingkup Kompetensi – '.$asesor->nama_lengkap)
@section('pretitle', 'Asesor / Lingkup Kompetensi')
@section('page-title', 'Lingkup Kompetensi: '.$asesor->nama_lengkap)

@section('actions')
    <a href="{{ route('admin.asesor.skema.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
    <a href="{{ route('admin.asesor.edit', $asesor) }}" class="btn btn-outline-primary">
        <i class="ti ti-edit me-1"></i> Edit Profil
    </a>
@endsection

@section('content')
<div class="row row-cards">
    <div class="col-lg-8">
        <x-tabler.card title="Skema yang Dapat Diasesi">
            @if ($asesor->kompetensis->isEmpty())
                <div class="text-secondary text-center py-3">
                    <i class="ti ti-info-circle me-1"></i>
                    Belum ada skema terdaftar — asesor dianggap kompeten di seluruh skema aktif.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Skema</th>
                                <th>Berlaku Mulai</th>
                                <th>Berlaku Hingga</th>
                                <th class="w-1">Hapus</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($asesor->kompetensis as $kompetensi)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $kompetensi->skema?->nama_skema ?? '(Semua Skema)' }}</div>
                                        <div class="text-secondary small">{{ $kompetensi->skema?->kode_skema ?? '' }}</div>
                                    </td>
                                    <td class="text-secondary">{{ $kompetensi->berlaku_mulai?->translatedFormat('d M Y') ?? '—' }}</td>
                                    <td class="text-secondary">
                                        @if ($kompetensi->berlaku_hingga)
                                            <span class="{{ $kompetensi->berlaku_hingga->isPast() ? 'text-danger' : '' }}">
                                                {{ $kompetensi->berlaku_hingga->translatedFormat('d M Y') }}
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        <form method="POST"
                                              action="{{ route('admin.asesor.skema.destroy', [$asesor, $kompetensi]) }}"
                                              onsubmit="return confirm('Hapus lingkup kompetensi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-tabler.card>
    </div>

    <div class="col-lg-4">
        <x-tabler.card title="Tambah Lingkup Skema">
            @if ($availableSkemas->isEmpty())
                <div class="text-secondary small">
                    Seluruh skema aktif sudah terdaftar dalam lingkup asesor ini.
                </div>
            @else
                <form method="POST" action="{{ route('admin.asesor.skema.store', $asesor) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label required">Skema Sertifikasi</label>
                        <select name="skema_id" class="form-select @error('skema_id') is-invalid @enderror" required>
                            <option value="">Pilih skema</option>
                            @foreach ($availableSkemas as $skema)
                                <option value="{{ $skema->id }}" @selected(old('skema_id') === $skema->id)>
                                    {{ $skema->kode_skema }} · {{ $skema->nama_skema }}
                                </option>
                            @endforeach
                        </select>
                        @error('skema_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Berlaku Mulai</label>
                        <input type="date" name="berlaku_mulai" class="form-control"
                               value="{{ old('berlaku_mulai') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Berlaku Hingga</label>
                        <input type="date" name="berlaku_hingga" class="form-control"
                               value="{{ old('berlaku_hingga') }}">
                        <div class="form-hint">Kosongkan jika tidak ada batas masa berlaku.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-plus me-1"></i> Tambahkan
                    </button>
                </form>
            @endif
        </x-tabler.card>

        <x-tabler.card title="Info Asesor" class="mt-3">
            <div class="mb-2">
                <div class="text-secondary small">No. Register</div>
                <div class="fw-semibold">{{ $asesor->no_register }}</div>
            </div>
            <div class="mb-2">
                <div class="text-secondary small">Bidang Keahlian</div>
                <div>{{ $asesor->bidang_keahlian ?: '—' }}</div>
            </div>
            <div class="mb-2">
                <div class="text-secondary small">Lisensi Berlaku Hingga</div>
                <div>
                    @if ($asesor->lisensi_berlaku_hingga)
                        <span class="{{ $asesor->lisensi_berlaku_hingga->isPast() ? 'text-danger fw-semibold' : '' }}">
                            {{ $asesor->lisensi_berlaku_hingga->translatedFormat('d M Y') }}
                        </span>
                    @else
                        —
                    @endif
                </div>
            </div>
            <div>
                <div class="text-secondary small">Status</div>
                <x-tabler.status-badge :status="$asesor->status" />
            </div>
        </x-tabler.card>
    </div>
</div>
@endsection
