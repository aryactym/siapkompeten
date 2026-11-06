@extends('layouts.admin')

@section('title', 'Pleno Asesmen')
@section('pretitle', 'Pleno Asesmen')
@section('page-title', 'Berita Acara Pleno — '.$jadwal->skema->nama_skema)

@section('actions')
    <a href="{{ route('admin.jadwal.show', $jadwal) }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
    @if ($pleno->exists)
        <a href="{{ route('pleno.pdf', $jadwal) }}" class="btn btn-outline-primary">
            <i class="ti ti-file-pdf me-1"></i> Cetak BA
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

    <x-tabler.card title="Informasi Jadwal" class="mb-3">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="text-secondary small">Skema</div>
                <div class="fw-semibold">{{ $jadwal->skema->nama_skema }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-secondary small">TUK</div>
                <div class="fw-semibold">{{ $jadwal->tuk?->nama_tuk ?? '—' }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-secondary small">Tanggal Jadwal</div>
                <div class="fw-semibold">{{ $jadwal->tanggal->translatedFormat('d F Y') }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-secondary small">Status Pleno</div>
                <div><x-tabler.status-badge :status="$pleno->status" :label="$pleno->isFinal() ? 'Final' : 'Draf'" /></div>
            </div>
        </div>
    </x-tabler.card>

    @if ($pleno->isFinal())
        <div class="alert alert-success">
            <i class="ti ti-lock me-1"></i>
            Pleno difinalkan pada {{ $pleno->finalized_at?->translatedFormat('d F Y H:i') ?? '—' }}.
            Keputusan: <strong>{{ $pleno->keputusan === 'disetujui' ? 'Disetujui' : 'Ditolak' }}</strong>.
        </div>
    @elseif ($penghambat->isNotEmpty())
        <div class="alert alert-warning">
            <i class="ti ti-alert-triangle me-1"></i>
            Pleno belum dapat difinalkan. Asesmen belum selesai untuk:
            <strong>{{ $penghambat->map(fn ($p) => $p->asesi?->user?->name ?? $p->no_pendaftaran)->implode(', ') }}</strong>.
        </div>
    @endif

    @php
        $terkunci = $pleno->isFinal();
    @endphp

    <form method="POST" action="{{ route('pleno.store', $jadwal) }}">
        @csrf
        <x-tabler.card title="Data Berita Acara" class="mb-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="pleno-nomor">Nomor BA</label>
                    <input type="text" name="nomor_ba" id="pleno-nomor" class="form-control" maxlength="100"
                           value="{{ old('nomor_ba', $pleno->nomor_ba) }}" @disabled($terkunci)>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="pleno-tanggal">Tanggal Pleno</label>
                    <input type="date" name="tanggal" id="pleno-tanggal" class="form-control"
                           value="{{ old('tanggal', $pleno->tanggal?->format('Y-m-d')) }}" @disabled($terkunci)>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="pleno-keputusan">Keputusan Pleno</label>
                    <select name="keputusan" id="pleno-keputusan" class="form-select" @disabled($terkunci)>
                        <option value="">— Pilih —</option>
                        <option value="disetujui" @selected(old('keputusan', $pleno->keputusan) === 'disetujui')>Disetujui</option>
                        <option value="ditolak" @selected(old('keputusan', $pleno->keputusan) === 'ditolak')>Ditolak</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label" for="pleno-catatan">Catatan</label>
                    <textarea name="catatan" id="pleno-catatan" rows="2" maxlength="2000" class="form-control"
                              @disabled($terkunci)>{{ old('catatan', $pleno->catatan) }}</textarea>
                </div>
            </div>
        </x-tabler.card>

        <x-tabler.card title="Peserta Pleno" class="mb-3">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th class="w-1">No</th>
                            <th>Nama Asesi</th>
                            <th class="text-center">K</th>
                            <th class="text-center">BK</th>
                            <th style="width:30%">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pleno->pesertas as $index => $peserta)
                            <tr>
                                <td class="text-secondary">{{ $index + 1 }}</td>
                                <td>{{ $peserta->pendaftaran?->asesi?->user?->name ?? '—' }}</td>
                                <td class="text-center">
                                    <input type="radio" class="form-check-input"
                                           name="peserta[{{ $index }}][keputusan]"
                                           value="kompeten"
                                           @checked(old("peserta.{$index}.keputusan", $peserta->keputusan) === 'kompeten')
                                           @disabled($terkunci)>
                                </td>
                                <td class="text-center">
                                    <input type="radio" class="form-check-input"
                                           name="peserta[{{ $index }}][keputusan]"
                                           value="belum_kompeten"
                                           @checked(old("peserta.{$index}.keputusan", $peserta->keputusan) === 'belum_kompeten')
                                           @disabled($terkunci)>
                                </td>
                                <td>
                                    <input type="hidden" name="peserta[{{ $index }}][pendaftaran_id]" value="{{ $peserta->pendaftaran_id }}">
                                    <input type="text" name="peserta[{{ $index }}][catatan]" class="form-control form-control-sm" maxlength="500"
                                           value="{{ old("peserta.{$index}.catatan", $peserta->catatan) }}" @disabled($terkunci)>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @unless ($terkunci)
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> Simpan Pleno
                    </button>
                </div>
            @endunless
        </x-tabler.card>
    </form>

    @unless ($pleno->isFinal())
        @if (! $penghambat->isNotEmpty() && $pleno->keputusan)
            <form method="POST" action="{{ route('pleno.final', $jadwal) }}">
                @csrf
                <button type="submit" class="btn btn-outline-success"
                        onclick="return confirm('Finalkan pleno? Setelah final, pleno tidak dapat diubah.')">
                    <i class="ti ti-lock me-1"></i> Finalkan Pleno
                </button>
            </form>
        @endif
    @endunless
@endsection
