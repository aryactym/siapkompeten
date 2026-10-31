@extends('layouts.admin')

@section('title', 'Biaya Skema')
@section('pretitle', 'Kelembagaan')
@section('page-title', 'Biaya per Skema Sertifikasi')

@section('actions')
    <form method="GET" action="{{ route('admin.biaya.index') }}" class="d-flex gap-2">
        <select name="skema" class="form-select form-select-sm" style="width: 18rem;" onchange="this.form.submit()">
            <option value="">Semua skema</option>
            @foreach ($skemas as $skema)
                <option value="{{ $skema->id }}" @selected((string) request('skema') === (string) $skema->id)>
                    {{ $skema->nama_skema }}
                </option>
            @endforeach
        </select>
    </form>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-biaya-baru" @disabled($skemas->isEmpty())>
        <i class="ti ti-plus me-1"></i> Tambah Biaya
    </button>
@endsection

@section('content')
<x-tabler.card
    title="Rincian Biaya"
    subtitle="Biaya per skema termasuk yang ditanggung fasilitator/sponsor dengan nominal peserta nol (FR-PAY-02/03)."
>
    @if ($skemas->isEmpty())
        <x-tabler.empty
            title="Belum ada skema sertifikasi"
            description="Buat skema sertifikasi terlebih dahulu sebelum menetapkan biaya."
            icon="ti ti-certificate"
        />
    @elseif ($biayas->isEmpty())
        <x-tabler.empty
            title="Belum ada biaya"
            description="Tetapkan jenis biaya dan nominal untuk skema terpilih."
            icon="ti ti-cash-banknote"
        />
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Skema</th>
                        <th>Jenis Biaya</th>
                        <th class="text-end">Nominal</th>
                        <th>Ditanggung</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($biayas as $biaya)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $biaya->skema->nama_skema }}</div>
                                <div class="text-secondary small">{{ $biaya->skema->kode_skema }}</div>
                            </td>
                            <td>{{ $biaya->jenis_biaya }}</td>
                            <td class="text-end">Rp {{ number_format((int) $biaya->nominal, 0, ',', '.') }}</td>
                            <td>
                                @if ($biaya->ditanggungPeserta())
                                    <span class="badge bg-primary-lt text-primary">Peserta</span>
                                @else
                                    <span class="badge bg-info-lt text-info">Fasilitator</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#modal-biaya-{{ $biaya->id }}">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.biaya.destroy', $biaya) }}"
                                          onsubmit="return confirm('Hapus biaya ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
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

<!-- Modal Tambah Biaya -->
<div class="modal modal-blur fade" id="modal-biaya-baru" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('admin.biaya.store') }}" class="modal-content js-form-uang">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Tambah Biaya Skema</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('admin.biaya._form', ['biaya' => $newBiaya, 'skemas' => $skemas])
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

<!-- Modal Ubah Biaya -->
@foreach ($biayas as $biaya)
    <div class="modal modal-blur fade" id="modal-biaya-{{ $biaya->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form method="POST" action="{{ route('admin.biaya.update', $biaya) }}" class="modal-content js-form-uang">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Ubah Biaya Skema</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('admin.biaya._form', ['biaya' => $biaya, 'skemas' => $skemas])
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

@push('scripts')
<script>
    // Format otomatis input nominal (FR-PAY-06): tampil berformat, tersimpan angka bulat.
    document.addEventListener('input', function (event) {
        if (!event.target.matches('[data-uang]')) {
            return;
        }

        const digits = event.target.value.replace(/\D/g, '');
        event.target.value = digits ? Number(digits).toLocaleString('id-ID') : '';
    });

    document.querySelectorAll('.js-form-uang').forEach(function (form) {
        form.addEventListener('submit', function () {
            form.querySelectorAll('[data-uang]').forEach(function (input) {
                input.value = input.value.replace(/\D/g, '');
            });
        });
    });
</script>
@endpush
@endsection
