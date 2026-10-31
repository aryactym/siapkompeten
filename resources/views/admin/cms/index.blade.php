@extends('layouts.admin')

@section('title', 'CMS Konten')
@section('pretitle', 'CMS & Publik')
@section('page-title', 'Konten Frontpage (CMS)')

@section('actions')
    <a href="{{ route('admin.cms.create') }}" class="btn btn-primary">
        <i class="ti ti-plus me-1"></i> Tambah Konten
    </a>
@endsection

@section('content')
<form method="GET" action="{{ route('admin.cms.index') }}" class="row g-2 align-items-center mb-3">
    <div class="col-auto">
        <select name="kategori" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Semua kategori</option>
            @foreach ($kategoris as $key => $label)
                <option value="{{ $key }}" @selected($kategori === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</form>

<x-tabler.card title="Daftar Konten">
    @if ($contents->isEmpty())
        <x-tabler.empty title="Belum ada konten" description="Tambahkan konten frontpage." icon="ti ti-file-text" />
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Judul</th>
                        <th>Tanggal Terbit</th>
                        <th class="text-center">Urutan</th>
                        <th class="text-center">Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($contents as $content)
                        <tr>
                            <td><span class="badge bg-blue-lt text-blue">{{ $kategoris[$content->kategori] ?? $content->kategori }}</span></td>
                            <td class="fw-semibold">{{ $content->judul }}</td>
                            <td class="text-secondary small">{{ $content->tanggal_terbit?->translatedFormat('d M Y') ?? '—' }}</td>
                            <td class="text-center">{{ $content->urutan }}</td>
                            <td class="text-center">
                                @if ($content->is_active)
                                    <span class="badge bg-green-lt text-green">Aktif</span>
                                @else
                                    <span class="badge bg-secondary-lt">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <a href="{{ route('admin.cms.edit', $content) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.cms.destroy', $content) }}" onsubmit="return confirm('Hapus konten ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="ti ti-trash"></i></button>
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
@endsection
