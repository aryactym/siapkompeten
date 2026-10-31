@props([
    'url',
    'filename' => 'Dokumen',
    'mime' => 'application/pdf',
    'downloadUrl' => null,
])

@php
    $isPdf = str_contains($mime, 'pdf');
    $isImage = str_contains($mime, 'image') || in_array(pathinfo($url, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'webp']);
@endphp

<div class="card card-sm">
    <div class="card-body p-2">
        @if ($isImage)
            <div class="text-center bg-light rounded p-2">
                <img src="{{ $url }}" alt="{{ $filename }}" class="img-fluid rounded" style="max-height: 400px; object-fit: contain;">
            </div>
        @elseif ($isPdf)
            <div class="ratio ratio-16x9 bg-light rounded">
                <iframe src="{{ $url }}#toolbar=0" class="rounded w-100 h-100" frameborder="0"></iframe>
            </div>
        @else
            <div class="d-flex align-items-center gap-3 p-3 bg-light rounded">
                <i class="ti ti-file-text fs-1 text-primary"></i>
                <div>
                    <div class="font-weight-medium">{{ $filename }}</div>
                    <div class="text-secondary fs-6">{{ $mime }}</div>
                </div>
            </div>
        @endif
    </div>
    @if ($downloadUrl)
        <div class="card-footer py-2 d-flex justify-content-between align-items-center bg-transparent">
            <span class="text-secondary fs-6">{{ $filename }}</span>
            <a href="{{ $downloadUrl }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">
                <i class="ti ti-download me-1"></i> Unduh Berkas
            </a>
        </div>
    @endif
</div>
