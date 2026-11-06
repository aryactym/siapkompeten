@extends('layouts.asesor')

@section('title', 'FR.IA.06 Esai')
@section('pretitle', 'Instrumen')
@section('page-title', 'FR.IA.06 — Pertanyaan Tertulis Esai')

@section('actions')
    <a href="{{ route('asesor.jadwal.peserta', $pendaftaran->jadwal) }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
    <a href="{{ route('asesor.ia06.pdf', $pendaftaran) }}" class="btn btn-outline-secondary">
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
        $terkunci = $ia06->isFinal() || $pendaftaran->status !== \App\Models\Pendaftaran::STATUS_ASESMEN_BERJALAN;
    @endphp

    @unless ($terkunci)
        <x-tabler.card title="Tambah Soal Esai" class="mb-3">
            <form method="POST" action="{{ route('asesor.ia06.tambah', $pendaftaran) }}">
                @csrf
                <div class="mb-2">
                    <label class="form-label">Pertanyaan</label>
                    <textarea name="pertanyaan" rows="2" class="form-control" maxlength="2000" required>{{ old('pertanyaan') }}</textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label">Kunci / rambu-rambu jawaban</label>
                    <textarea name="kunci" rows="2" class="form-control" maxlength="5000">{{ old('kunci') }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="ti ti-plus me-1"></i> Tambah Soal
                </button>
            </form>
        </x-tabler.card>
    @endunless

    <form id="ia06-form" method="POST" action="{{ route('asesor.ia06.store', $pendaftaran) }}">
        @csrf

        <x-tabler.card title="Identitas Instrumen" class="mb-3">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Judul</label>
                    <input type="text" name="judul" class="form-control" maxlength="200"
                           value="{{ old('judul', $ia06->judul) }}" @disabled($terkunci)>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Petunjuk pengerjaan</label>
                    <input type="text" name="petunjuk" class="form-control" maxlength="5000"
                           value="{{ old('petunjuk', $ia06->petunjuk) }}" @disabled($terkunci)>
                </div>
            </div>
        </x-tabler.card>

        <x-tabler.card title="Daftar Soal" class="mb-3">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th class="w-1">No</th>
                            <th style="width:32%">Pertanyaan</th>
                            <th style="width:26%">Kunci / rambu jawaban</th>
                            <th style="width:26%">Jawaban asesi</th>
                            <th style="width:10%">Pencapaian</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ia06->soals as $soal)
                            <tr>
                                <td class="text-secondary">{{ $loop->iteration }}</td>
                                <td>
                                    <input type="hidden" name="soals[{{ $loop->index }}][id]" value="{{ $soal->id }}">
                                    <textarea name="soals[{{ $loop->index }}][pertanyaan]" rows="3" class="form-control form-control-sm"
                                              maxlength="2000" @disabled($terkunci)>{{ old("soals.{$loop->index}.pertanyaan", $soal->pertanyaan) }}</textarea>
                                </td>
                                <td>
                                    <textarea name="soals[{{ $loop->index }}][kunci]" rows="3" class="form-control form-control-sm"
                                              maxlength="5000" @disabled($terkunci)>{{ old("soals.{$loop->index}.kunci", $soal->kunci) }}</textarea>
                                </td>
                                <td>
                                    <textarea name="soals[{{ $loop->index }}][jawaban_asesi]" rows="3" class="form-control form-control-sm"
                                              maxlength="5000" @disabled($terkunci)>{{ old("soals.{$loop->index}.jawaban_asesi", $soal->jawaban_asesi) }}</textarea>
                                </td>
                                <td>
                                    <select name="soals[{{ $loop->index }}][pencapaian]" class="form-select form-select-sm" @disabled($terkunci)>
                                        <option value="">—</option>
                                        <option value="1" @selected($soal->pencapaian === true)>Ya</option>
                                        <option value="0" @selected($soal->pencapaian === false)>Tidak</option>
                                    </select>
                                </td>
                                <td>
                                    @unless ($terkunci)
                                        <button type="submit" form="ia06-hapus-{{ $soal->id }}" class="btn btn-sm btn-outline-danger"
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
                <label class="form-label" for="ia06-umpan">Umpan balik untuk asesi</label>
                <textarea name="umpan_balik" id="ia06-umpan" rows="3" maxlength="2000" class="form-control" @disabled($terkunci)>{{ old('umpan_balik', $ia06->umpan_balik) }}</textarea>
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
        @foreach ($ia06->soals as $soal)
            <form id="ia06-hapus-{{ $soal->id }}" method="POST"
                  action="{{ route('asesor.ia06.hapus', [$pendaftaran, $soal]) }}" class="d-none">
                @csrf @method('DELETE')
            </form>
        @endforeach
    @endunless

    @unless ($ia06->isFinal())
        @if ($pendaftaran->status === \App\Models\Pendaftaran::STATUS_ASESMEN_BERJALAN && $ia06->soals->count() > 0)
            <form method="POST" action="{{ route('asesor.ia06.final', $pendaftaran) }}">
                @csrf
                <button type="submit" class="btn btn-outline-success" onclick="return confirm('Finalkan FR.IA.06?')">
                    <i class="ti ti-lock me-1"></i> Finalkan
                </button>
            </form>
        @endif
    @endunless

    @if ($ia06->isFinal())
        <div class="alert alert-success mt-3">
            <i class="ti ti-lock me-1"></i> Difinalkan {{ $ia06->finalized_at?->translatedFormat('d F Y H:i') }}.
        </div>
    @endif
@endsection
