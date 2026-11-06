@extends('layouts.asesor')

@section('title', 'Laporan Asesmen FR.AK.05')
@section('pretitle', 'Laporan Asesmen')
@section('page-title', 'FR.AK.05 — '.$jadwal->skema->nama_skema)

@section('actions')
    <a href="{{ route('asesor.jadwal.peserta', $jadwal) }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
    @if ($laporan->exists)
        <a href="{{ route('asesor.laporan-asesmen.pdf', $jadwal) }}" class="btn btn-outline-primary">
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

    <x-tabler.card title="Informasi Asesmen" class="mb-3">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="text-secondary small">Skema</div>
                <div class="fw-semibold">{{ $jadwal->skema->nama_skema }}</div>
                <div class="text-secondary small">{{ $jadwal->skema->kode_skema }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-secondary small">TUK</div>
                <div class="fw-semibold">{{ $jadwal->tuk?->nama_tuk ?? '—' }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-secondary small">Tanggal</div>
                <div class="fw-semibold">{{ $jadwal->tanggal->translatedFormat('d F Y') }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-secondary small">Asesor</div>
                <div class="fw-semibold">{{ $asesor?->nama_lengkap ?? '—' }}</div>
            </div>
        </div>
    </x-tabler.card>

    @if ($laporan->isFinal())
        <div class="alert alert-success">
            <i class="ti ti-lock me-1"></i>
            Laporan difinalkan pada {{ $laporan->finalized_at?->translatedFormat('d F Y H:i') ?? '—' }}
            @if ($laporan->finalizer)
                oleh {{ $laporan->finalizer->name }}
            @endif.
        </div>
    @elseif ($penghambat->isNotEmpty())
        <div class="alert alert-warning">
            <i class="ti ti-alert-triangle me-1"></i>
            Laporan belum dapat difinalkan. Asesmen belum selesai untuk:
            <strong>{{ $penghambat->map(fn ($p) => $p->asesi?->user?->name ?? $p->no_pendaftaran)->implode(', ') }}</strong>.
        </div>
    @endif

    <x-tabler.card title="Rekap Hasil Asesi" class="mb-3">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th class="w-1">No</th>
                        <th>Nama Asesi</th>
                        <th class="text-center" style="width:10%">K</th>
                        <th class="text-center" style="width:10%">BK</th>
                        <th>Keterangan (unit BK)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rekap as $baris)
                        <tr>
                            <td class="text-secondary">{{ $loop->iteration }}</td>
                            <td>{{ $baris['nama'] }}</td>
                            <td class="text-center">{{ $baris['rekomendasi'] === 'K' ? '✓' : '' }}</td>
                            <td class="text-center">{{ $baris['rekomendasi'] === 'BK' ? '✓' : '' }}</td>
                            <td class="small">{{ $baris['keterangan'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-secondary text-center">Belum ada asesi pada jadwal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p class="text-secondary small mb-0">
            Rekap diturunkan otomatis dari FR.AK.02 final. K = Kompeten, BK = Belum Kompeten.
        </p>
    </x-tabler.card>

    @php
        $terkunci = $laporan->isFinal();
    @endphp

    <form method="POST" action="{{ route('asesor.laporan-asesmen.store', $jadwal) }}">
        @csrf
        <x-tabler.card title="Catatan Laporan" class="mb-3">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="ak05-positif">Aspek positif dalam asesmen</label>
                    <textarea name="aspek_positif" id="ak05-positif" rows="3" maxlength="2000" class="form-control"
                              @disabled($terkunci)>{{ old('aspek_positif', $laporan->aspek_positif) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="ak05-negatif">Aspek negatif dalam asesmen</label>
                    <textarea name="aspek_negatif" id="ak05-negatif" rows="3" maxlength="2000" class="form-control"
                              @disabled($terkunci)>{{ old('aspek_negatif', $laporan->aspek_negatif) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="ak05-penolakan">Pencatatan penolakan hasil asesmen</label>
                    <textarea name="pencatatan_penolakan" id="ak05-penolakan" rows="3" maxlength="2000" class="form-control"
                              @disabled($terkunci)>{{ old('pencatatan_penolakan', $laporan->pencatatan_penolakan) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="ak05-saran">Saran perbaikan</label>
                    <textarea name="saran_perbaikan" id="ak05-saran" rows="3" maxlength="2000" class="form-control"
                              @disabled($terkunci)>{{ old('saran_perbaikan', $laporan->saran_perbaikan) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label" for="ak05-catatan">Catatan</label>
                    <textarea name="catatan" id="ak05-catatan" rows="2" maxlength="2000" class="form-control"
                              @disabled($terkunci)>{{ old('catatan', $laporan->catatan) }}</textarea>
                </div>
            </div>

            @unless ($terkunci)
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> Simpan Laporan
                    </button>
                </div>
            @endunless
        </x-tabler.card>
    </form>

    @unless ($laporan->isFinal())
        @if ($penghambat->isEmpty())
            <form method="POST" action="{{ route('asesor.laporan-asesmen.final', $jadwal) }}">
                @csrf
                <button type="submit" class="btn btn-outline-success"
                        onclick="return confirm('Finalkan laporan asesmen? Setelah final, laporan tidak dapat diubah.')">
                    <i class="ti ti-lock me-1"></i> Finalkan Laporan
                </button>
            </form>
        @endif
    @endunless
@endsection
