@extends('layouts.admin')

@section('title', 'Rekening Bank LSP')
@section('pretitle', 'Kelembagaan')
@section('page-title', 'Rekening Bank Penerima')

@section('actions')
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-rekening-baru">
        <i class="ti ti-plus me-1"></i> Tambah Rekening
    </button>
@endsection

@section('content')
<x-tabler.card
    title="Daftar Rekening"
    subtitle="Rekening tujuan transfer yang ditampilkan kepada peserta saat pembayaran (FR-PAY-01)."
>
    @if ($rekeningBanks->isEmpty())
        <x-tabler.empty
            title="Belum ada rekening bank"
            description="Tambahkan rekening LSP agar peserta dapat melakukan pembayaran."
            icon="ti ti-building-bank"
        />
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Nama Bank</th>
                        <th>Nomor Rekening</th>
                        <th>Atas Nama</th>
                        <th>Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rekeningBanks as $rekening)
                        <tr>
                            <td class="fw-semibold">{{ $rekening->nama_bank }}</td>
                            <td>{{ $rekening->no_rekening }}</td>
                            <td>{{ $rekening->atas_nama }}</td>
                            <td>
                                @if ($rekening->is_aktif)
                                    <span class="badge bg-success-lt text-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary-lt text-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modal-rekening-{{ $rekening->id }}">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.rekening.destroy', $rekening) }}"
                                          onsubmit="return confirm('Hapus rekening ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" @disabled($rekening->pembayarans()->exists())>
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-tabler.card>

<!-- Modal Tambah Rekening -->
<div class="modal modal-blur fade" id="modal-rekening-baru" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('admin.rekening.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Tambah Rekening Bank</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('admin.rekening._form', ['rekening' => $newRekeningBank])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary ms-auto">
                    <i class="ti ti-device-floppy me-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Ubah Rekening -->
@foreach ($rekeningBanks as $rekening)
    <div class="modal modal-blur fade" id="modal-rekening-{{ $rekening->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form method="POST" action="{{ route('admin.rekening.update', $rekening) }}" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Ubah Rekening Bank</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('admin.rekening._form', ['rekening' => $rekening])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary ms-auto">
                        <i class="ti ti-device-floppy me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endforeach
@endsection
