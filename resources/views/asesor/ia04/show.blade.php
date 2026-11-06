@extends('layouts.asesor')

@section('title', 'FR.IA.04 Proyek Singkat')
@section('pretitle', 'Instrumen')
@section('page-title', 'FR.IA.04 — Proyek Singkat / Kegiatan Terstruktur')

@section('actions')
    <a href="{{ route('asesor.jadwal.peserta', $pendaftaran->jadwal) }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
    <a href="{{ route('asesor.ia04.pdf', $pendaftaran) }}" class="btn btn-outline-secondary">
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
        $terkunci = $ia04->isFinal() || $pendaftaran->status !== \App\Models\Pendaftaran::STATUS_ASESMEN_BERJALAN;
    @endphp

    @unless ($terkunci)
        <x-tabler.card title="FR.IA.04.B — Tambah Butir Penilaian" class="mb-3">
            <form method="POST" action="{{ route('asesor.ia04.tambah', $pendaftaran) }}">
                @csrf
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label">Lingkup penyajian</label>
                        <input type="text" name="lingkup" class="form-control" maxlength="200"
                               placeholder="Contoh: Perencanaan pemasaran digital" value="{{ old('lingkup') }}">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Pertanyaan</label>
                        <input type="text" name="pertanyaan" class="form-control" maxlength="2000" required
                               placeholder="Pertanyaan yang diajukan setelah presentasi" value="{{ old('pertanyaan') }}">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm mt-2">
                    <i class="ti ti-plus me-1"></i> Tambah Butir
                </button>
            </form>
        </x-tabler.card>
    @endunless

    <form id="ia04-form" method="POST" action="{{ route('asesor.ia04.store', $pendaftaran) }}">
        @csrf

        <x-tabler.card title="FR.IA.04.A — Penjelasan Singkat Proyek" class="mb-3">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Judul kegiatan terstruktur</label>
                    <input type="text" name="judul_kegiatan" class="form-control" maxlength="200"
                           value="{{ old('judul_kegiatan', $ia04->judul_kegiatan) }}" @disabled($terkunci)>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kelompok pekerjaan (unit kompetensi skema)</label>
                    <div class="border rounded p-2 small" style="max-height: 120px; overflow-y: auto;">
                        @forelse ($units as $unit)
                            <div>{{ $unit->unitKompetensi?->kode_unit }} — {{ $unit->unitKompetensi?->judul_unit }}</div>
                        @empty
                            <span class="text-secondary">Belum ada unit kompetensi.</span>
                        @endforelse
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Skenario / instruksi untuk asesi</label>
                    <textarea name="skenario" rows="4" maxlength="5000" class="form-control" @disabled($terkunci)>{{ old('skenario', $ia04->skenario) }}</textarea>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Hal yang harus disiapkan / dilakukan / dihasilkan</label>
                    <textarea name="hal_dipersiapkan" rows="3" maxlength="5000" class="form-control" @disabled($terkunci)>{{ old('hal_dipersiapkan', $ia04->hal_dipersiapkan) }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Waktu persiapan</label>
                    <input type="text" name="waktu_persiapan" class="form-control" maxlength="100"
                           placeholder="Contoh: 60 menit" value="{{ old('waktu_persiapan', $ia04->waktu_persiapan) }}" @disabled($terkunci)>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Hal yang perlu didemonstrasikan / dipresentasikan</label>
                    <textarea name="hasil_dipresentasikan" rows="3" maxlength="5000" class="form-control" @disabled($terkunci)>{{ old('hasil_dipresentasikan', $ia04->hasil_dipresentasikan) }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Waktu presentasi</label>
                    <input type="text" name="waktu_presentasi" class="form-control" maxlength="100"
                           placeholder="Contoh: 30 menit" value="{{ old('waktu_presentasi', $ia04->waktu_presentasi) }}" @disabled($terkunci)>
                </div>
            </div>
        </x-tabler.card>

        <x-tabler.card title="FR.IA.04.B — Penilaian Proyek" class="mb-3">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th class="w-1">No</th>
                            <th style="width:26%">Lingkup penyajian</th>
                            <th>Pertanyaan</th>
                            <th style="width:22%">Tanggapan asesi</th>
                            <th style="width:12%">Pencapaian</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ia04->items as $item)
                            <tr>
                                <td class="text-secondary">{{ $loop->iteration }}</td>
                                <td>
                                    <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                                    <input type="text" name="items[{{ $loop->index }}][lingkup]" class="form-control form-control-sm"
                                           maxlength="200" value="{{ old("items.{$loop->index}.lingkup", $item->lingkup) }}" @disabled($terkunci)>
                                </td>
                                <td>{{ $item->pertanyaan }}</td>
                                <td>
                                    <textarea name="items[{{ $loop->index }}][tanggapan]" rows="2" class="form-control form-control-sm"
                                              maxlength="2000" @disabled($terkunci)>{{ old("items.{$loop->index}.tanggapan", $item->tanggapan) }}</textarea>
                                </td>
                                <td>
                                    <select name="items[{{ $loop->index }}][pencapaian]" class="form-select form-select-sm" @disabled($terkunci)>
                                        <option value="">—</option>
                                        <option value="1" @selected($item->pencapaian === true)>Ya</option>
                                        <option value="0" @selected($item->pencapaian === false)>Tidak</option>
                                    </select>
                                </td>
                                <td>
                                    @unless ($terkunci)
                                        <button type="submit" form="ia04-hapus-{{ $item->id }}" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Hapus butir ini?')">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-secondary text-center">Belum ada butir penilaian.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <label class="form-label d-block">Rekomendasi asesor</label>
                @foreach (\App\Models\Ia04Asesmen::REKOMENDASI_LABELS as $value => $label)
                    <label class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="rekomendasi" value="{{ $value }}"
                               @checked(old('rekomendasi', $ia04->rekomendasi) === $value) @disabled($terkunci)>
                        <span class="form-check-label">{{ $label }}</span>
                    </label>
                @endforeach
            </div>

            <div class="mt-3">
                <label class="form-label" for="ia04-umpan">Umpan balik untuk asesi</label>
                <textarea name="umpan_balik" id="ia04-umpan" rows="3" maxlength="2000" class="form-control" @disabled($terkunci)>{{ old('umpan_balik', $ia04->umpan_balik) }}</textarea>
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
        @foreach ($ia04->items as $item)
            <form id="ia04-hapus-{{ $item->id }}" method="POST"
                  action="{{ route('asesor.ia04.hapus', [$pendaftaran, $item]) }}" class="d-none">
                @csrf @method('DELETE')
            </form>
        @endforeach
    @endunless

    @unless ($ia04->isFinal())
        @if ($pendaftaran->status === \App\Models\Pendaftaran::STATUS_ASESMEN_BERJALAN && $ia04->items->count() > 0)
            <form method="POST" action="{{ route('asesor.ia04.final', $pendaftaran) }}">
                @csrf
                <button type="submit" class="btn btn-outline-success" onclick="return confirm('Finalkan FR.IA.04?')">
                    <i class="ti ti-lock me-1"></i> Finalkan
                </button>
            </form>
        @endif
    @endunless

    @if ($ia04->isFinal())
        <div class="alert alert-success mt-3">
            <i class="ti ti-lock me-1"></i> Difinalkan {{ $ia04->finalized_at?->translatedFormat('d F Y H:i') }}.
            Rekomendasi: <strong>{{ \App\Models\Ia04Asesmen::REKOMENDASI_LABELS[$ia04->rekomendasi] ?? '—' }}</strong>
        </div>
    @endif
@endsection
