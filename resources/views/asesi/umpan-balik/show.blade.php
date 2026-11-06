@extends('layouts.asesi')

@section('title', 'Umpan Balik FR.AK.03')
@section('pretitle', 'Umpan Balik Asesmen')
@section('page-title', 'FR.AK.03 — Umpan Balik dan Catatan Asesmen')

@section('actions')
    <a href="{{ route('asesi.pendaftaran.show', $pendaftaran) }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
    @if ($umpanBalik?->isSubmitted())
        <a href="{{ route('asesi.umpan-balik.pdf', $pendaftaran) }}" class="btn btn-outline-primary">
            <i class="ti ti-file-pdf me-1"></i> Unduh PDF
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
            <div class="col-md-4">
                <div class="text-secondary small">Skema</div>
                <div class="fw-semibold">{{ $pendaftaran->skema->nama_skema }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-secondary small">Asesor</div>
                <div class="fw-semibold">{{ $asesor?->nama_lengkap ?? '—' }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-secondary small">Tanggal Asesmen</div>
                <div class="fw-semibold">
                    {{ $pendaftaran->jadwal?->tanggal?->translatedFormat('d F Y') ?? '—' }}
                </div>
            </div>
        </div>
    </x-tabler.card>

    @if ($pendaftaran->status !== \App\Models\Pendaftaran::STATUS_SELESAI)
        <div class="alert alert-warning">
            <i class="ti ti-clock me-1"></i>
            Umpan balik dapat diisi setelah asesor menetapkan keputusan asesmen.
        </div>
    @endif

    @if ($umpanBalik?->isSubmitted())
        <div class="alert alert-success">
            <i class="ti ti-lock me-1"></i>
            Umpan balik sudah dikirim pada
            {{ $umpanBalik->disubmit_pada?->translatedFormat('d F Y H:i') }} dan tidak dapat diubah.
        </div>

        <x-tabler.card title="Rekap Umpan Balik" class="mb-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-secondary small">Jawaban Ya</div>
                    <div class="fw-semibold text-success">{{ $rekap['ya'] }} / {{ $rekap['total'] }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-secondary small">Jawaban Tidak</div>
                    <div class="fw-semibold text-danger">{{ $rekap['tidak'] }} / {{ $rekap['total'] }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-secondary small">Diisi Oleh</div>
                    <div class="fw-semibold">{{ $umpanBalik->pengisi?->name ?? '—' }}</div>
                </div>
            </div>
        </x-tabler.card>
    @endif

    <x-tabler.card title="Komponen Umpan Balik" class="mb-3">
        <p class="text-secondary small">
            Isi penilaian Anda terhadap proses asesmen yang telah dilaksanakan.
        </p>

        <form method="POST" action="{{ route('asesi.umpan-balik.store', $pendaftaran) }}" id="form-ak03">
            @csrf

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th class="w-1">No</th>
                            <th>Komponen</th>
                            <th class="text-center" style="width:16%">Ya</th>
                            <th class="text-center" style="width:16%">Tidak</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($komponen as $key => $pernyataan)
                            @php
                                $jawaban = old('komponen.'.$key, $umpanBalik?->jawaban($key));
                            @endphp
                            <tr>
                                <td class="text-secondary">{{ $loop->iteration }}</td>
                                <td>{{ $pernyataan }}</td>
                                <td class="text-center">
                                    <input class="form-check-input" type="radio"
                                           id="komponen-{{ $key }}-ya"
                                           name="komponen[{{ $key }}]"
                                           value="{{ \App\Models\UmpanBalikAsesmen::JAWABAN_YA }}"
                                           @checked($jawaban === \App\Models\UmpanBalikAsesmen::JAWABAN_YA)
                                           @disabled($umpanBalik?->isSubmitted() || $pendaftaran->status !== \App\Models\Pendaftaran::STATUS_SELESAI)>
                                </td>
                                <td class="text-center">
                                    <input class="form-check-input" type="radio"
                                           id="komponen-{{ $key }}-tidak"
                                           name="komponen[{{ $key }}]"
                                           value="{{ \App\Models\UmpanBalikAsesmen::JAWABAN_TIDAK }}"
                                           @checked($jawaban === \App\Models\UmpanBalikAsesmen::JAWABAN_TIDAK)
                                           @disabled($umpanBalik?->isSubmitted() || $pendaftaran->status !== \App\Models\Pendaftaran::STATUS_SELESAI)>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <label class="form-label" for="ak03-catatan">Catatan/komentar lainnya (apabila ada)</label>
                <textarea name="catatan" id="ak03-catatan" rows="3" maxlength="2000" class="form-control"
                          placeholder="Tuliskan catatan tambahan…"
                          @disabled($umpanBalik?->isSubmitted() || $pendaftaran->status !== \App\Models\Pendaftaran::STATUS_SELESAI)>{{ old('catatan', $umpanBalik?->catatan) }}</textarea>
            </div>

            @if (! $umpanBalik?->isSubmitted() && $pendaftaran->status === \App\Models\Pendaftaran::STATUS_SELESAI)
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary"
                            onclick="return confirm('Kirim umpan balik? Setelah dikirim, jawaban tidak dapat diubah.')">
                        <i class="ti ti-send me-1"></i> Kirim Umpan Balik
                    </button>
                </div>
            @endif
        </form>
    </x-tabler.card>
@endsection
