@props([
    'id',
    'title',
    'size' => 'md', // sm, md, lg, xl, full-width
    'footer' => null,
])

<div class="modal modal-blur fade" id="{{ $id }}" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1061 !important;">
    <div class="modal-dialog modal-{{ $size }} modal-dialog-centered modal-dialog-scrollable" role="document" style="z-index: 1061 !important;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{ $slot }}
            </div>
            @if ($footer)
                <div class="modal-footer">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
