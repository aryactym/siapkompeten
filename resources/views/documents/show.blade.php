@extends('layouts.app')

@section('title', $document->title)

@section('content')
    <h1 class="page-title">{{ $document->title }}</h1>
    <p class="page-sub">Detail dokumen milik lembaga aktif.</p>

    <div class="card">
        <dl class="meta">
            <div>
                <dt>Nama berkas asli</dt>
                <dd>{{ $document->original_name }}</dd>
            </div>
            <div>
                <dt>Tipe</dt>
                <dd>{{ $document->mime_type ?: '-' }}</dd>
            </div>
            <div>
                <dt>Ukuran</dt>
                <dd>{{ number_format($document->size / 1024, 1, ',', '.') }} KB</dd>
            </div>
            <div>
                <dt>Diunggah</dt>
                <dd>{{ $document->created_at?->format('d/m/Y H:i') }}</dd>
            </div>
        </dl>

        <div class="actions">
            <a class="btn" href="{{ route('documents.download', $document) }}">Unduh berkas</a>
            <a class="btn btn-secondary" href="{{ route('dashboard') }}">Kembali</a>
        </div>
    </div>
@endsection
