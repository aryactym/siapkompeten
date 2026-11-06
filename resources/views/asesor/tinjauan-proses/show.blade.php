@extends('layouts.asesor')

@section('title', 'Tinjauan Proses FR.AK.06')
@section('pretitle', 'Tinjauan Proses Asesmen')
@section('page-title', 'FR.AK.06 — '.$jadwal->skema->nama_skema)

@section('actions')
    <a href="{{ route('asesor.jadwal.peserta', $jadwal) }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
    @if ($tinjauan->exists)
        <a href="{{ route('asesor.tinjauan-proses.pdf', $jadwal) }}" class="btn btn-outline-primary">
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

    @if ($tinjauan->isFinal())
        <div class="alert alert-success">
            <i class="ti ti-lock me-1"></i>
            Tinjauan difinalkan pada {{ $tinjauan->finalized_at?->translatedFormat('d F Y H:i') ?? '—' }}
            @if ($tinjauan->finalizer)
                oleh {{ $tinjauan->finalizer->name }}
            @endif.
        </div>
    @elseif ($penghambat->isNotEmpty())
        <div class="alert alert-warning">
            <i class="ti ti-alert-triangle me-1"></i>
            Tinjauan belum dapat difinalkan. Asesmen belum selesai untuk:
            <strong>{{ $penghambat->map(fn ($p) => $p->asesi?->user?->name ?? $p->no_pendaftaran)->implode(', ') }}</strong>.
        </div>
    @endif

    <div class="alert alert-info">
        <i class="ti ti-info-circle me-1"></i>
        Peninjauan dapat dilakukan oleh lead asesor atau asesor pelaksana. Isilah setiap sel dengan jenis bukti/instrumen
        yang menunjukkan terpenuhinya prinsip atau dimensi kompetensi.
    </div>

    @php
        $terkunci = $tinjauan->isFinal();
    @endphp

    <form method="POST" action="{{ route('asesor.tinjauan-proses.store', $jadwal) }}">
        @csrf

        <x-tabler.card title="Prosedur Asesmen terhadap Prinsip Asesmen" class="mb-3">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th style="min-width:180px">Aspek yang ditinjau</th>
                            @foreach (\App\Models\TinjauanProsesAsesmen::PRINSIP as $prinsipKey => $prinsipLabel)
                                <th style="min-width:160px">{{ $prinsipLabel }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (\App\Models\TinjauanProsesAsesmen::PROSES as $prosesKey => $prosesLabel)
                            <tr>
                                <td class="fw-semibold">{{ $prosesLabel }}</td>
                                @foreach (\App\Models\TinjauanProsesAsesmen::PRINSIP as $prinsipKey => $prinsipLabel)
                                    @php
                                        $field = "proses.{$prosesKey}.{$prinsipKey}";
                                    @endphp
                                    <td>
                                        <input type="text"
                                               name="proses[{{ $prosesKey }}][{{ $prinsipKey }}]"
                                               class="form-control form-control-sm"
                                               maxlength="255"
                                               aria-label="{{ $prosesLabel }} — {{ $prinsipLabel }}"
                                               value="{{ old($field, $tinjauan->nilaiProses($prosesKey, $prinsipKey)) }}"
                                               @disabled($terkunci)>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-2">
                <label class="form-label" for="ak06-rekomendasi-proses">Rekomendasi untuk peningkatan (prosedur)</label>
                <textarea name="rekomendasi_proses" id="ak06-rekomendasi-proses" rows="3" maxlength="2000"
                          class="form-control"
                          @disabled($terkunci)>{{ old('rekomendasi_proses', $tinjauan->rekomendasi_proses) }}</textarea>
            </div>
        </x-tabler.card>

        <x-tabler.card title="Konsistensi Keputusan Asesmen" class="mb-3">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th style="min-width:220px">Aspek yang ditinjau</th>
                            @foreach (\App\Models\TinjauanProsesAsesmen::DIMENSI as $dimensiKey => $dimensiLabel)
                                <th style="min-width:150px">{{ $dimensiLabel }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-semibold">
                                Bukti dari berbagai asesmen diperiksa untuk konsistensi dimensi kompetensi
                            </td>
                            @foreach (\App\Models\TinjauanProsesAsesmen::DIMENSI as $dimensiKey => $dimensiLabel)
                                @php
                                    $field = "dimensi.{$dimensiKey}";
                                @endphp
                                <td>
                                    <input type="text"
                                           name="dimensi[{{ $dimensiKey }}]"
                                           class="form-control form-control-sm"
                                           maxlength="255"
                                           aria-label="{{ $dimensiLabel }}"
                                           value="{{ old($field, $tinjauan->nilaiDimensi($dimensiKey)) }}"
                                           @disabled($terkunci)>
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-2">
                <label class="form-label" for="ak06-rekomendasi-dimensi">Rekomendasi untuk peningkatan (dimensi)</label>
                <textarea name="rekomendasi_dimensi" id="ak06-rekomendasi-dimensi" rows="3" maxlength="2000"
                          class="form-control"
                          @disabled($terkunci)>{{ old('rekomendasi_dimensi', $tinjauan->rekomendasi_dimensi) }}</textarea>
            </div>

            <div class="mt-3">
                <label class="form-label" for="ak06-komentar">Komentar</label>
                <textarea name="komentar" id="ak06-komentar" rows="2" maxlength="2000" class="form-control"
                          @disabled($terkunci)>{{ old('komentar', $tinjauan->komentar) }}</textarea>
            </div>

            @unless ($terkunci)
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> Simpan Tinjauan
                    </button>
                </div>
            @endunless
        </x-tabler.card>
    </form>

    @unless ($tinjauan->isFinal())
        @if ($penghambat->isEmpty())
            <form method="POST" action="{{ route('asesor.tinjauan-proses.final', $jadwal) }}">
                @csrf
                <button type="submit" class="btn btn-outline-success"
                        onclick="return confirm('Finalkan tinjauan proses? Setelah final, tinjauan tidak dapat diubah.')">
                    <i class="ti ti-lock me-1"></i> Finalkan Tinjauan
                </button>
            </form>
        @endif
    @endunless
@endsection
