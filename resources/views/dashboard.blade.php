@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1 class="page-title">Dashboard</h1>
    <p class="page-sub">
        Data yang tampil hanya milik lembaga aktif:
        <strong>{{ session(\App\Support\TenantContext::SESSION_NAME) }}</strong>.
    </p>

    <div class="card">
        <h2>Unggah dokumen lembaga</h2>
        <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid">
                <div class="field">
                    <label for="title">Judul dokumen</label>
                    <input id="title" name="title" class="input" type="text" value="{{ old('title') }}" required maxlength="200">
                </div>
                <div class="field">
                    <label for="file">Berkas (PDF/JPG/PNG, maks. 5 MB)</label>
                    <input id="file" name="file" class="input" type="file" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
            </div>
            <div class="actions" style="margin-top: 14px;">
                <button type="submit" class="btn">Simpan dokumen</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Dokumen lembaga</h2>

        @if ($documents->isEmpty())
            <div class="empty">
                Belum ada dokumen pada lembaga ini.
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Berkas</th>
                            <th>Diunggah</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($documents as $document)
                            <tr>
                                <td>
                                    <a href="{{ route('documents.show', $document) }}">{{ $document->title }}</a>
                                    <div class="muted" style="font-size: 12px;">
                                        {{ $document->uploader?->name ?? 'Sistem' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge">{{ strtoupper(pathinfo($document->original_name, PATHINFO_EXTENSION)) }}</span>
                                    <span class="muted" style="font-size: 12px; margin-left: 6px;">
                                        {{ number_format($document->size / 1024, 1, ',', '.') }} KB
                                    </span>
                                </td>
                                <td class="muted">{{ $document->created_at?->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="actions">
                                        <a class="btn btn-secondary btn-sm" href="{{ route('documents.show', $document) }}">Detail</a>
                                        <a class="btn btn-sm" href="{{ route('documents.download', $document) }}">Unduh</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 14px;">
                {{ $documents->links() }}
            </div>
        @endif
    </div>

    <p class="muted" style="font-size: 12px;">
        Tenant context: <code>{{ $institutionId }}</code>
    </p>
@endsection
