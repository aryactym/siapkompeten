@extends('layouts.admin')

@section('title', 'Dashboard')
@section('pretitle', 'Ringkasan')
@section('page-title', 'Dashboard Lembaga')

@section('content')
<div class="row row-cards" id="dashboard-grid">
    @foreach ($widgets as $item)
        <div class="col-md-{{ $item['width'] >= 12 ? 12 : ($item['width'] >= 6 ? 6 : 4) }}">
            <div class="card" data-widget="{{ $item['key'] }}" data-widget-width="{{ $item['width'] }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h3 class="card-title mb-0">{{ $item['widget']?->title() ?? $item['key'] }}</h3>
                    </div>
                    <div class="widget-skeleton">
                        <div class="placeholder-glow">
                            <span class="placeholder col-12"></span>
                            <span class="placeholder col-8"></span>
                            <span class="placeholder col-6"></span>
                        </div>
                    </div>
                    <div class="widget-body d-none"></div>
                    <div class="widget-empty d-none">
                        <x-tabler.empty title="Belum ada data" />
                    </div>
                    <div class="widget-error d-none text-secondary small">
                        Gagal memuat widget. <button type="button" class="btn btn-sm btn-link widget-retry">Coba lagi</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@push('scripts')
<script type="module">
    import '/resources/js/dashboard/index.js';
</script>
@endpush
@endsection
