@extends('layouts.admin')

@section('title', 'Pratinjau Impor Asesi')
@section('pretitle', 'Asesi & Pendaftaran')
@section('page-title', 'Pratinjau Impor Asesi')

@section('actions')
    <a href="{{ route('admin.asesi.import.report', $batch) }}" class="btn btn-outline-secondary">
        <i class="ti ti-file-spreadsheet me-1"></i> Unduh Laporan
    </a>
    <a href="{{ route('admin.asesi.import.create') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
@endsection

@section('content')
<x-tabler.card>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <div class="fw-semibold">{{ $batch->original_name }}</div>
            <div class="text-secondary small">
                Diunggah {{ $batch->created_at->translatedFormat('d F Y H:i') }}
                oleh {{ $batch->user?->name ?? '—' }}
            </div>
        </div>
        <span class="badge {{ $batch->status === 'selesai' ? 'bg-green-lt' : 'bg-blue-lt' }} fs-6">
            {{ ucfirst($batch->status) }}
        </span>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="border rounded p-3 text-center">
                <div class="text-secondary small">Total Baris</div>
                <div class="h2 mb-0">{{ $batch->total_rows }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="border rounded p-3 text-center">
                <div class="text-secondary small">Siap Diimpor</div>
                <div class="h2 mb-0 text-success">{{ $batch->valid_rows }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="border rounded p-3 text-center">
                <div class="text-secondary small">Bermasalah</div>
                <div class="h2 mb-0 text-danger">{{ $batch->invalid_rows }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="border rounded p-3 text-center">
                <div class="text-secondary small">Sudah Diimpor</div>
                <div class="h2 mb-0">{{ $batch->imported_rows }}</div>
            </div>
        </div>
    </div>

    @if ($batch->isPreview())
        <div class="alert alert-info">
            Baris bermasalah tidak akan diimpor. Perbaiki berkas lalu unggah ulang bila diperlukan.
            Baris valid akan dibuatkan akun dan dikirimi tautan aktivasi kata sandi melalui email.
        </div>

        <div class="d-flex gap-2 mb-4">
            @if ($batch->valid_rows > 0)
                <form method="POST" action="{{ route('admin.asesi.import.commit', $batch) }}"
                      onsubmit="return confirm('Impor {{ $batch->valid_rows }} baris valid sekarang?')">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check me-1"></i> Import {{ $batch->valid_rows }} Baris
                    </button>
                </form>
            @endif

            <form method="POST" action="{{ route('admin.asesi.import.destroy', $batch) }}"
                  onsubmit="return confirm('Batalkan dan hapus batch impor ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">Batalkan Batch</button>
            </form>
        </div>
    @elseif ($batch->isFinished())
        <div class="alert alert-success">
            Impor selesai. {{ $batch->imported_rows }} akun asesi dibuat.
            Unduh laporan untuk melihat rincian baris berhasil dan gagal.
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-vcenter">
            <thead>
                <tr>
                    <th class="w-1">Baris</th>
                    <th>Nama</th>
                    <th>NIK</th>
                    <th>Email / Telepon</th>
                    <th>Wilayah</th>
                    <th>Status</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    @php
                        $rowErrors = $row->errors ?? [];
                        $data = $row->data ?? [];
                    @endphp
                    <tr class="{{ $rowErrors !== [] ? 'table-danger' : '' }}">
                        <td class="text-secondary">{{ $row->row_number }}</td>
                        <td>
                            <div class="fw-semibold {{ isset($rowErrors['nama']) ? 'text-danger' : '' }}">
                                {{ $data['nama'] ?? '—' }}
                            </div>
                            <div class="text-secondary small">{{ $data['pendidikan_terakhir'] ?? '' }}</div>
                        </td>
                        <td class="{{ isset($rowErrors['nik']) ? 'text-danger fw-semibold' : '' }}">
                            {{ $data['nik'] ?? '—' }}
                        </td>
                        <td class="small">
                            <div class="{{ isset($rowErrors['email']) ? 'text-danger fw-semibold' : '' }}">
                                {{ $data['email'] ?? '—' }}
                            </div>
                            <div class="{{ isset($rowErrors['telepon']) ? 'text-danger' : '' }}">
                                {{ $data['telepon'] ?? '—' }}
                            </div>
                        </td>
                        <td class="small text-secondary">
                            {{ $data['kode_kota'] ?? '—' }}
                            @if (! empty($data['kode_provinsi']))
                                <div>Prov. {{ $data['kode_provinsi'] }}</div>
                            @endif
                        </td>
                        <td>
                            @php
                                $badge = match ($row->status) {
                                    'imported' => 'bg-green-lt',
                                    'gagal' => 'bg-orange-lt',
                                    'invalid' => 'bg-red-lt',
                                    default => 'bg-blue-lt',
                                };
                            @endphp
                            <span class="badge {{ $badge }}">{{ ucfirst($row->status) }}</span>
                        </td>
                        <td class="small text-danger">
                            {{ implode(' ', $rowErrors) ?: '—' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $rows->links() }}</div>
</x-tabler.card>
@endsection
