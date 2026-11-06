@extends('layouts.admin')

@section('title', 'Validasi Asesmen FR.VA')
@section('pretitle', 'Validasi Asesmen')
@section('page-title', 'FR.VA — '.$pendaftaran->skema->nama_skema)

@section('actions')
    <a href="{{ route('admin.pendaftaran.show', $pendaftaran) }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
    @if ($validasi->exists)
        <a href="{{ route('validasi.pdf', $pendaftaran) }}" class="btn btn-outline-primary">
            <i class="ti ti-file-pdf me-1"></i> Cetak PDF
        </a>
    @endif
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success">
            <i class="ti ti-check me-1"></i> {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <x-tabler.card title="Informasi Pendaftaran" class="mb-3">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="text-secondary small">Asesi</div>
                <div class="fw-semibold">{{ $pendaftaran->asesi->user->name ?? '—' }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-secondary small">No. Pendaftaran</div>
                <div class="fw-semibold">{{ $pendaftaran->no_pendaftaran }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-secondary small">Skema</div>
                <div class="fw-semibold">{{ $pendaftaran->skema->nama_skema }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-secondary small">Status Pendaftaran</div>
                <div><x-tabler.status-badge :status="$pendaftaran->status" /></div>
            </div>
        </div>
    </x-tabler.card>

    @if ($validasi->isFinal())
        <div class="alert alert-success">
            <i class="ti ti-lock me-1"></i>
            Validasi difinalkan pada {{ $validasi->finalized_at?->translatedFormat('d F Y H:i') ?? '—' }}
            @if ($validasi->finalizer)
                oleh {{ $validasi->finalizer->name }}
            @endif.
            Keputusan: <strong>{{ \App\Models\ValidasiAsesmen::KEPUTUSAN_LABELS[$validasi->keputusan] ?? $validasi->keputusan }}</strong>.
        </div>
    @endif

    @php
        $terkunci = $validasi->isFinal();
    @endphp

    <form method="POST" action="{{ route('validasi.store', $pendaftaran) }}">
        @csrf
        <x-tabler.card title="Aspek Validasi" class="mb-3">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="va-validitas">Validitas</label>
                    <textarea name="aspek_validitas" id="va-validitas" rows="3" maxlength="2000" class="form-control"
                              @disabled($terkunci)>{{ old('aspek_validitas', $validasi->aspek_validitas) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="va-reliabilitas">Reliabilitas</label>
                    <textarea name="aspek_reliabilitas" id="va-reliabilitas" rows="3" maxlength="2000" class="form-control"
                              @disabled($terkunci)>{{ old('aspek_reliabilitas', $validasi->aspek_reliabilitas) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="va-keadilan">Keadilan</label>
                    <textarea name="aspek_keadilan" id="va-keadilan" rows="3" maxlength="2000" class="form-control"
                              @disabled($terkunci)>{{ old('aspek_keadilan', $validasi->aspek_keadilan) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="va-kesesuaian">Kesesuaian</label>
                    <textarea name="aspek_kesesuaian" id="va-kesesuaian" rows="3" maxlength="2000" class="form-control"
                              @disabled($terkunci)>{{ old('aspek_kesesuaian', $validasi->aspek_kesesuaian) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="va-keputusan">Keputusan</label>
                    <select name="keputusan" id="va-keputusan" class="form-select" @disabled($terkunci)>
                        <option value="">— Pilih keputusan —</option>
                        @foreach (\App\Models\ValidasiAsesmen::KEPUTUSAN_LABELS as $value => $label)
                            <option value="{{ $value }}" @selected(old('keputusan', $validasi->keputusan) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="va-catatan">Catatan</label>
                    <textarea name="catatan" id="va-catatan" rows="3" maxlength="2000" class="form-control"
                              @disabled($terkunci)>{{ old('catatan', $validasi->catatan) }}</textarea>
                </div>
            </div>

            @unless ($terkunci)
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> Simpan Validasi
                    </button>
                </div>
            @endunless
        </x-tabler.card>
    </form>

    @unless ($validasi->isFinal())
        @if ($validasi->keputusan)
            <form method="POST" action="{{ route('validasi.final', $pendaftaran) }}">
                @csrf
                <button type="submit" class="btn btn-outline-success"
                        onclick="return confirm('Finalkan validasi asesmen?')">
                    <i class="ti ti-lock me-1"></i> Finalkan Validasi
                </button>
            </form>
        @endif
    @endunless
@endsection
