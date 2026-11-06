@extends('layouts.asesor')

@section('title', 'FR.IA.05 Pilihan Ganda')
@section('pretitle', 'Instrumen')
@section('page-title', 'FR.IA.05 — Pertanyaan Tertulis Pilihan Ganda')

@section('actions')
    <a href="{{ route('asesor.jadwal.peserta', $pendaftaran->jadwal) }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
    <a href="{{ route('asesor.ia05.pdf', $pendaftaran) }}" class="btn btn-outline-secondary">
        <i class="ti ti-printer me-1"></i> Cetak PDF
    </a>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @php
        $terkunci = $ia05->isFinal() || $pendaftaran->status !== \App\Models\Pendaftaran::STATUS_ASESMEN_BERJALAN;
    @endphp

    @unless ($terkunci)
        <x-tabler.card title="Tambah Soal Pilihan Ganda" class="mb-3">
            <form method="POST" action="{{ route('asesor.ia05.tambah', $pendaftaran) }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label">Pertanyaan</label>
                    <textarea name="pertanyaan" rows="2" class="form-control" maxlength="2000" required>{{ old('pertanyaan') }}</textarea>
                </div>
                <div class="row g-2">
                    @foreach (['a' => 'Opsi A', 'b' => 'Opsi B', 'c' => 'Opsi C', 'd' => 'Opsi D'] as $key => $label)
                        <div class="col-md-6">
                            <label class="form-label">{{ $label }} @if (in_array($key, ['a', 'b']))<span class="text-danger">*</span>@endif</label>
                            <input type="text" name="opsi_{{ $key }}" class="form-control" maxlength="1000"
                                   value="{{ old('opsi_'.$key) }}" @required(in_array($key, ['a', 'b']))>
                        </div>
                    @endforeach
                </div>
                <div class="row g-2 mt-1 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Kunci jawaban</label>
                        <select name="kunci" class="form-select" required>
                            @foreach (\App\Models\Ia05Soal::OPSI_LABELS as $value => $label)
                                <option value="{{ $value }}" @selected(old('kunci') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i> Tambah Soal
                        </button>
                    </div>
                </div>
            </form>
        </x-tabler.card>
    @endunless

    <form id="ia05-form" method="POST" action="{{ route('asesor.ia05.store', $pendaftaran) }}">
        @csrf

        <x-tabler.card title="Identitas Instrumen" class="mb-3">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Judul</label>
                    <input type="text" name="judul" class="form-control" maxlength="200"
                           value="{{ old('judul', $ia05->judul) }}" placeholder="Contoh: Soal Pilihan Ganda Skema Pemasaran Digital" @disabled($terkunci)>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Petunjuk pengerjaan</label>
                    <input type="text" name="petunjuk" class="form-control" maxlength="5000"
                           value="{{ old('petunjuk', $ia05->petunjuk) }}" placeholder="Pilih satu jawaban paling tepat." @disabled($terkunci)>
                </div>
            </div>
        </x-tabler.card>

        <x-tabler.card title="Daftar Soal" class="mb-3">
            @if ($ia05->soals->isNotEmpty())
                <div class="mb-2 text-secondary small">
                    Jumlah soal: {{ $ia05->soals->count() }} ·
                    Jawaban benar: {{ $ia05->jumlahBenar() }} / {{ $ia05->soals->count() }}
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th class="w-1">No</th>
                            <th style="width:24%">Pertanyaan & Opsi</th>
                            <th style="width:10%">Kunci</th>
                            <th style="width:10%">Jawaban Asesi</th>
                            <th style="width:8%">Benar</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ia05->soals as $soal)
                            <tr>
                                <td class="text-secondary">{{ $loop->iteration }}</td>
                                <td>
                                    <input type="hidden" name="soals[{{ $loop->index }}][id]" value="{{ $soal->id }}">
                                    <textarea name="soals[{{ $loop->index }}][pertanyaan]" rows="2" class="form-control form-control-sm mb-1"
                                              maxlength="2000" @disabled($terkunci)>{{ old("soals.{$loop->index}.pertanyaan", $soal->pertanyaan) }}</textarea>
                                    @foreach ($soal->opsi() as $key => $opsi)
                                        <div class="input-group input-group-sm mb-1">
                                            <span class="input-group-text">{{ strtoupper($key) }}</span>
                                            <input type="text" name="soals[{{ $loop->index }}][opsi_{{ $key }}]" class="form-control"
                                                   maxlength="1000" value="{{ old("soals.{$loop->index}.opsi_{$key}", $opsi) }}" @disabled($terkunci)>
                                        </div>
                                    @endforeach
                                </td>
                                <td>
                                    <select name="soals[{{ $loop->index }}][kunci]" class="form-select form-select-sm" @disabled($terkunci)>
                                        @foreach (\App\Models\Ia05Soal::OPSI_LABELS as $value => $label)
                                            <option value="{{ $value }}" @selected($soal->kunci === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="soals[{{ $loop->index }}][jawaban_asesi]" class="form-select form-select-sm" @disabled($terkunci)>
                                        <option value="">—</option>
                                        @foreach (\App\Models\Ia05Soal::OPSI_LABELS as $value => $label)
                                            <option value="{{ $value }}" @selected($soal->jawaban_asesi === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center">
                                    @if ($soal->jawaban_asesi === null)
                                        <span class="text-secondary">—</span>
                                    @elseif ($soal->benar())
                                        <span class="badge bg-success-lt">Ya</span>
                                    @else
                                        <span class="badge bg-danger-lt">Tidak</span>
                                    @endif
                                </td>
                                <td>
                                    @unless ($terkunci)
                                        <button type="submit" form="ia05-hapus-{{ $soal->id }}" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Hapus soal ini?')">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-secondary text-center">Belum ada soal.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <label class="form-label" for="ia05-umpan">Umpan balik untuk asesi</label>
                <textarea name="umpan_balik" id="ia05-umpan" rows="3" maxlength="2000" class="form-control" @disabled($terkunci)>{{ old('umpan_balik', $ia05->umpan_balik) }}</textarea>
            </div>

            @unless ($terkunci)
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> Simpan
                    </button>
                </div>
            @endunless
        </x-tabler.card>
    </form>

    @unless ($terkunci)
        @foreach ($ia05->soals as $soal)
            <form id="ia05-hapus-{{ $soal->id }}" method="POST"
                  action="{{ route('asesor.ia05.hapus', [$pendaftaran, $soal]) }}" class="d-none">
                @csrf @method('DELETE')
            </form>
        @endforeach
    @endunless

    @unless ($ia05->isFinal())
        @if ($pendaftaran->status === \App\Models\Pendaftaran::STATUS_ASESMEN_BERJALAN && $ia05->soals->count() > 0)
            <form method="POST" action="{{ route('asesor.ia05.final', $pendaftaran) }}">
                @csrf
                <button type="submit" class="btn btn-outline-success" onclick="return confirm('Finalkan FR.IA.05?')">
                    <i class="ti ti-lock me-1"></i> Finalkan
                </button>
            </form>
        @endif
    @endunless

    @if ($ia05->isFinal())
        <div class="alert alert-success mt-3">
            <i class="ti ti-lock me-1"></i> Difinalkan {{ $ia05->finalized_at?->translatedFormat('d F Y H:i') }}.
        </div>
    @endif
@endsection
