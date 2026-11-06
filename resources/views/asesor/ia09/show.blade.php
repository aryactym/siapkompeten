@extends('layouts.asesor')

@section('title', 'FR.IA.07 PMO')
@section('pretitle', 'Instrumen')
@section('page-title', 'FR.IA.07 — Pertanyaan Wawancara')

@section('actions')
    <a href="{{ route('asesor.jadwal.peserta', $pendaftaran->jadwal) }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
    <a href="{{ route('asesor.ia09.pdf', $pendaftaran) }}" class="btn btn-outline-secondary">
        <i class="ti ti-printer me-1"></i> Cetak PDF
    </a>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @php
        $terkunci = $ia09->isFinal() || $pendaftaran->status !== \App\Models\Pendaftaran::STATUS_ASESMEN_BERJALAN;
    @endphp

    @unless ($terkunci)
        <x-tabler.card title="Tambah Pertanyaan" class="mb-3">
            <form method="POST" action="{{ route('asesor.ia09.tambah', $pendaftaran) }}">
                @csrf
                <div class="mb-2">
                    <textarea name="pertanyaan" rows="2" class="form-control" maxlength="1000" placeholder="Tulis pertanyaan pendukung observasi…" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="ti ti-plus me-1"></i> Tambah
                </button>
            </form>
        </x-tabler.card>
    @endunless

    <form method="POST" action="{{ route('asesor.ia09.store', $pendaftaran) }}">
        @csrf
        <x-tabler.card title="Daftar Pertanyaan" class="mb-3">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th class="w-1">No</th>
                            <th>Pertanyaan</th>
                            <th style="width:12%">Pencapaian</th>
                            <th style="width:28%">Tanggapan Asesi</th>
                            <th style="width:18%">Catatan</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ia09->items as $item)
                            <tr>
                                <td class="text-secondary">{{ $item->urutan }}</td>
                                <td>
                                    {{ $item->pertanyaan }}
                                    <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                                </td>
                                <td>
                                    <select name="items[{{ $loop->index }}][pencapaian]" class="form-select form-select-sm" @disabled($terkunci)>
                                        <option value="">—</option>
                                        <option value="1" @selected($item->pencapaian === true)>Ya</option>
                                        <option value="0" @selected($item->pencapaian === false)>Tidak</option>
                                    </select>
                                </td>
                                <td>
                                    <textarea name="items[{{ $loop->index }}][tanggapan]" rows="2" class="form-control form-control-sm" maxlength="2000" @disabled($terkunci)>{{ old("items.{$loop->index}.tanggapan", $item->tanggapan) }}</textarea>
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $loop->index }}][catatan]" class="form-control form-control-sm" maxlength="1000"
                                           value="{{ old("items.{$loop->index}.catatan", $item->catatan) }}" @disabled($terkunci)>
                                </td>
                                <td>
                                    @unless ($terkunci)
                                        <form method="POST" action="{{ route('asesor.ia09.hapus', [$pendaftaran, $item]) }}" onsubmit="return confirm('Hapus pertanyaan?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-secondary text-center">Belum ada pertanyaan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <label class="form-label" for="ia09-umpan">Umpan balik untuk asesi</label>
                <textarea name="umpan_balik" id="ia09-umpan" rows="3" maxlength="2000" class="form-control" @disabled($terkunci)>{{ old('umpan_balik', $ia09->umpan_balik) }}</textarea>
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

    @unless ($ia09->isFinal())
        @if ($pendaftaran->status === \App\Models\Pendaftaran::STATUS_ASESMEN_BERJALAN && $ia09->items->count() > 0)
            <form method="POST" action="{{ route('asesor.ia09.final', $pendaftaran) }}">
                @csrf
                <button type="submit" class="btn btn-outline-success" onclick="return confirm('Finalkan FR.IA.07?')">
                    <i class="ti ti-lock me-1"></i> Finalkan
                </button>
            </form>
        @endif
    @endunless

    @if ($ia09->isFinal())
        <div class="alert alert-success mt-3">
            <i class="ti ti-lock me-1"></i> Difinalkan {{ $ia09->finalized_at?->translatedFormat('d F Y H:i') }}.
        </div>
    @endif
@endsection
