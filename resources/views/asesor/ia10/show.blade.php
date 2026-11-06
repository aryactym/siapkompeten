@extends('layouts.asesor')
@section('title', 'FR.IA.10 Klarifikasi Pihak Ketiga')
@section('pretitle', 'Instrumen')
@section('page-title', 'FR.IA.10 — Klarifikasi Bukti Pihak Ketiga')
@section('actions')
    <a href="{{ route('asesor.jadwal.peserta', $pendaftaran->jadwal) }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i> Kembali</a>
    <a href="{{ route('asesor.ia10.pdf', $pendaftaran) }}" class="btn btn-outline-secondary"><i class="ti ti-printer me-1"></i> Cetak PDF</a>
@endsection
@section('content')
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    @php $terkunci = $ia10->isFinal() || $pendaftaran->status !== \App\Models\Pendaftaran::STATUS_ASESMEN_BERJALAN; @endphp
    <form method="POST" action="{{ route('asesor.ia10.store', $pendaftaran) }}">
        @csrf
        <x-tabler.card title="Data Pihak Ketiga" class="mb-3">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Nama</label><input type="text" name="nama_pihak_ketiga" class="form-control" maxlength="200" value="{{ old('nama_pihak_ketiga', $ia10->nama_pihak_ketiga) }}" @disabled($terkunci)></div>
                <div class="col-md-4"><label class="form-label">Jabatan</label><input type="text" name="jabatan" class="form-control" maxlength="200" value="{{ old('jabatan', $ia10->jabatan) }}" @disabled($terkunci)></div>
                <div class="col-md-4"><label class="form-label">Instansi</label><input type="text" name="instansi" class="form-control" maxlength="200" value="{{ old('instansi', $ia10->instansi) }}" @disabled($terkunci)></div>
            </div>
        </x-tabler.card>
        <x-tabler.card title="Klarifikasi Bukti" class="mb-3">
            <textarea name="klarifikasi" rows="6" maxlength="5000" class="form-control" @disabled($terkunci)>{{ old('klarifikasi', $ia10->klarifikasi) }}</textarea>
            @unless ($terkunci)
                <div class="mt-3"><button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i> Simpan</button></div>
            @endunless
        </x-tabler.card>
    </form>
    @unless ($ia10->isFinal())
        @if ($pendaftaran->status === \App\Models\Pendaftaran::STATUS_ASESMEN_BERJALAN && $ia10->klarifikasi)
            <form method="POST" action="{{ route('asesor.ia10.final', $pendaftaran) }}">
                @csrf
                <button type="submit" class="btn btn-outline-success" onclick="return confirm('Finalkan FR.IA.10?')"><i class="ti ti-lock me-1"></i> Finalkan</button>
            </form>
        @endif
    @endunless
    @if ($ia10->isFinal())
        <div class="alert alert-success mt-3"><i class="ti ti-lock me-1"></i> Difinalkan {{ $ia10->finalized_at?->translatedFormat('d F Y H:i') }}.</div>
    @endif
@endsection
