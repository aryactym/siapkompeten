@extends('layouts.asesor')

@section('title', $instrumen->label())
@section('pretitle', 'Asesor')
@section('page-title', $instrumen->label())

@section('actions')
    <a href="{{ route('asesor.jadwal.peserta', $pendaftaran->jadwal_id) }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
    <a href="{{ route('asesor.instrumen.pdf', [$pendaftaran, $jenis]) }}" class="btn btn-outline-secondary">
        <i class="ti ti-printer me-1"></i> Cetak PDF
    </a>
@endsection

@section('content')
    <x-tabler.card title="Informasi Instrumen" class="mb-3">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="text-secondary small">Asesi</div>
                <div class="fw-semibold">{{ $pendaftaran->asesi->user->name ?? '—' }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-secondary small">No. Pendaftaran</div>
                <div class="fw-semibold">{{ $pendaftaran->no_pendaftaran }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-secondary small">Status Instrumen</div>
                <div>
                    @if ($instrumen->isFinal())
                        <span class="badge bg-success-lt text-success">Final</span>
                    @else
                        <span class="badge bg-warning-lt text-warning">Draf</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-md-3">
                <div class="text-secondary small">Total Item</div>
                <div class="fw-semibold">{{ $rekap['total'] }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-secondary small">Tercapai</div>
                <div class="fw-semibold text-success">{{ $rekap['tercapai'] }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-secondary small">Belum Tercapai</div>
                <div class="fw-semibold text-danger">{{ $rekap['belum_tercapai'] }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-secondary small">Belum Dinilai</div>
                <div class="fw-semibold text-secondary">{{ $rekap['belum_dinilai'] }}</div>
            </div>
        </div>
    </x-tabler.card>

    @if ($pendaftaran->status !== \App\Models\Pendaftaran::STATUS_ASESMEN_BERJALAN && ! $instrumen->isFinal())
        <div class="alert alert-warning">
            <i class="ti ti-info-circle me-1"></i>
            Instrumen hanya dapat diisi saat asesmen berjalan. Mulai asesmen terlebih dahulu dari halaman peserta.
        </div>
    @endif

    @if ($instrumen->isFinal())
        <div class="alert alert-success">
            <i class="ti ti-lock me-1"></i>
            Instrumen telah difinalkan pada
            {{ $instrumen->finalized_at?->translatedFormat('d F Y H:i') ?? '—' }}
            @if ($instrumen->finalizer)
                oleh {{ $instrumen->finalizer->name }}
            @endif.
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

    @php
        $terkunci = $instrumen->isFinal() || $pendaftaran->status !== \App\Models\Pendaftaran::STATUS_ASESMEN_BERJALAN;
    @endphp

    <form method="POST" action="{{ route('asesor.instrumen.store', [$pendaftaran, $jenis]) }}">
        @csrf

        @foreach ($instrumen->items->groupBy('unit_kompetensi_id') as $unitId => $items)
            <x-tabler.card
                title="{{ $items->first()->unitKompetensi?->kode_unit ?? 'Unit' }} — {{ $items->first()->unitKompetensi?->judul_unit ?? 'Unit Kompetensi' }}"
                class="mb-3"
            >
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th class="w-1">No</th>
                                <th>Kriteria Unjuk Kerja</th>
                                <th class="text-center" style="width:18%">Hasil</th>
                                <th style="width:28%">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    <td class="text-center text-secondary">{{ $item->urutan }}</td>
                                    <td>{{ $item->kuk?->pernyataan_kuk ?? '—' }}</td>
                                    <td class="text-center">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio"
                                                   id="item-{{ $item->id }}-tercapai"
                                                   name="items[{{ $item->id }}][hasil]"
                                                   value="{{ \App\Models\InstrumenAsesmenItem::HASIL_TERCAPAI }}"
                                                   @checked(old("items.{$item->id}.hasil", $item->hasil) === \App\Models\InstrumenAsesmenItem::HASIL_TERCAPAI)
                                                   @disabled($terkunci)>
                                            <label class="form-check-label" for="item-{{ $item->id }}-tercapai">Tercapai</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio"
                                                   id="item-{{ $item->id }}-belum"
                                                   name="items[{{ $item->id }}][hasil]"
                                                   value="{{ \App\Models\InstrumenAsesmenItem::HASIL_BELUM_TERCAPAI }}"
                                                   @checked(old("items.{$item->id}.hasil", $item->hasil) === \App\Models\InstrumenAsesmenItem::HASIL_BELUM_TERCAPAI)
                                                   @disabled($terkunci)>
                                            <label class="form-check-label" for="item-{{ $item->id }}-belum">Belum</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text"
                                               name="items[{{ $item->id }}][catatan]"
                                               class="form-control form-control-sm"
                                               maxlength="1000"
                                               value="{{ old("items.{$item->id}.catatan", $item->catatan) }}"
                                               placeholder="Catatan bukti/observasi…"
                                               @disabled($terkunci)>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-tabler.card>
        @endforeach

        <x-tabler.card title="Catatan Umum" class="mb-3">
            <textarea name="catatan" class="form-control" rows="3" maxlength="2000"
                      placeholder="Catatan umum pelaksanaan instrumen…"
                      @disabled($terkunci)>{{ old('catatan', $instrumen->catatan) }}</textarea>
        </x-tabler.card>

        @unless ($terkunci)
            <div class="btn-list">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i> Simpan Instrumen
                </button>
            </div>
        @endunless
    </form>

    @unless ($instrumen->isFinal())
        @if ($pendaftaran->status === \App\Models\Pendaftaran::STATUS_ASESMEN_BERJALAN)
            <form method="POST" action="{{ route('asesor.instrumen.final', [$pendaftaran, $jenis]) }}" class="mt-2">
                @csrf
                <button type="submit" class="btn btn-outline-success"
                        onclick="return confirm('Finalkan instrumen? Setelah final, isian tidak dapat diubah.')">
                    <i class="ti ti-lock me-1"></i> Finalkan Instrumen
                </button>
            </form>
        @endif
    @endunless
@endsection
