@extends(auth()->user()->hasRole('asesor') ? 'layouts.asesor' : 'layouts.asesi')

@section('title', 'Tanda Tangan')

@section('page-title', 'Manajemen Tanda Tangan')

@section('content')
<div class="row">
    <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Manajemen Tanda Tangan</h5>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($hasSignature)
                        <div class="mb-4">
                            <h6 class="mb-3">Tanda Tangan Saat Ini:</h6>
                            <div class="border p-3 d-inline-block bg-light">
                                <img src="{{ asset('storage/' . $user->signature_path) }}" 
                                     alt="Tanda Tangan" 
                                     class="img-fluid"
                                     style="max-height: 150px; max-width: 300px;">
                            </div>
                            <div class="mt-3">
                                <form action="{{ route(auth()->user()->hasRole('asesor') ? 'asesor.signature.delete' : 'asesi.signature.delete') }}" 
                                      method="POST" 
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Hapus tanda tangan?')">
                                        Hapus Tanda Tangan
                                    </button>
                                </form>
                            </div>
                        </div>
                        <hr>
                    @endif

                    <h6 class="mb-3">Gambar Tanda Tangan:</h6>

                    <div class="d-flex gap-2 mb-3">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="tab-canvas" onclick="switchTab('canvas')">Gambar Langsung</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="tab-upload" onclick="switchTab('upload')">Upload File</button>
                    </div>

                    <div class="tab-content">
                        <!-- Canvas Tab -->
                        <div class="tab-pane fade show active" id="canvas-panel" role="tabpanel">
                            <form action="{{ route(auth()->user()->hasRole('asesor') ? 'asesor.signature.store' : 'asesi.signature.store') }}" 
                                  method="POST" 
                                  id="canvas-form">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label">Gambar Tanda Tangan Anda</label>
                                    <div class="border border-2 p-0" style="background: white; cursor: crosshair; display: inline-block; width: 100%;">
                                        <canvas id="signature-canvas" 
                                                width="800" 
                                                height="250"
                                                style="display: block; border: 1px solid #dee2e6; max-width: 100%; height: auto;">
                                        </canvas>
                                    </div>
                                    <small class="form-text text-muted d-block mt-2">Gambar tanda tangan dengan mouse atau trackpad</small>
                                </div>

                                <div class="mb-3">
                                    <button type="button" class="btn btn-sm btn-secondary" id="clear-canvas">
                                        Hapus Gambar
                                    </button>
                                </div>

                                <input type="hidden" name="signature_canvas" id="signature_canvas_data">
                                <input type="hidden" name="signature_source" value="canvas">
                                <button type="submit" class="btn btn-primary" id="submit-canvas">Simpan Tanda Tangan</button>
                            </form>
                        </div>

                        <!-- Upload Tab -->
                        <div class="tab-pane fade" id="upload-panel" role="tabpanel">
                            <form action="{{ route(auth()->user()->hasRole('asesor') ? 'asesor.signature.store' : 'asesi.signature.store') }}" 
                                  method="POST" 
                                  enctype="multipart/form-data"
                                  id="upload-form">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label">Pilih File Tanda Tangan</label>
                                    <input type="file" name="signature_file" 
                                           class="form-control @error('signature_file') is-invalid @enderror" 
                                           accept="image/png,image/jpeg,image/jpg">
                                    @error('signature_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="form-text text-muted">Format: PNG, JPG, JPEG. Max 2MB.</small>
                                </div>

                                <div class="mb-3">
                                    <div id="upload-preview" class="border rounded p-2 bg-light d-none" style="max-height: 150px; max-width: 300px;">
                                        <img id="upload-preview-img" src="" alt="Preview" class="img-fluid">
                                    </div>
                                </div>

                                <input type="hidden" name="signature_source" value="upload">
                                <button type="submit" class="btn btn-primary" id="submit-upload">Simpan Tanda Tangan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Upload preview
    const fileInput = document.getElementById('signature_file');
    const preview = document.getElementById('upload-preview');
    
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    preview.src = event.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    }

    // Canvas drawing
    const canvas = document.getElementById('signature-canvas');
    if (canvas) {
        const ctx = canvas.getContext('2d', { willReadFrequently: true });
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;

        // Get scale ratio untuk high DPI
        const dpr = window.devicePixelRatio || 1;
        const rect = canvas.getBoundingClientRect();
        
        // Scale canvas internal resolution
        canvas.width = 800 * dpr;
        canvas.height = 250 * dpr;
        ctx.scale(dpr, dpr);

        function getMousePos(e) {
            const rect = canvas.getBoundingClientRect();
            return {
                x: (e.clientX - rect.left) * (canvas.width / dpr) / rect.width,
                y: (e.clientY - rect.top) * (canvas.height / dpr) / rect.height
            };
        }

        function startDrawing(e) {
            isDrawing = true;
            const pos = getMousePos(e);
            lastX = pos.x;
            lastY = pos.y;
        }

        function draw(e) {
            if (!isDrawing) return;
            e.preventDefault();
            
            const pos = getMousePos(e);
            
            ctx.strokeStyle = '#000';
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            
            lastX = pos.x;
            lastY = pos.y;
        }

        function stopDrawing(e) {
            if (isDrawing) {
                isDrawing = false;
                e.preventDefault();
            }
        }

        // Mouse events
        canvas.addEventListener('mousedown', startDrawing, false);
        canvas.addEventListener('mousemove', draw, false);
        canvas.addEventListener('mouseup', stopDrawing, false);
        canvas.addEventListener('mouseout', stopDrawing, false);

        // Touch events (trackpad & stylus)
        canvas.addEventListener('touchstart', function(e) {
            e.preventDefault();
            if (e.touches.length > 0) {
                const touch = e.touches[0];
                isDrawing = true;
                const rect = canvas.getBoundingClientRect();
                lastX = (touch.clientX - rect.left) * (canvas.width / dpr) / rect.width;
                lastY = (touch.clientY - rect.top) * (canvas.height / dpr) / rect.height;
            }
        }, false);

        canvas.addEventListener('touchmove', function(e) {
            e.preventDefault();
            if (e.touches.length > 0 && isDrawing) {
                const touch = e.touches[0];
                const rect = canvas.getBoundingClientRect();
                
                const x = (touch.clientX - rect.left) * (canvas.width / dpr) / rect.width;
                const y = (touch.clientY - rect.top) * (canvas.height / dpr) / rect.height;
                
                ctx.strokeStyle = '#000';
                ctx.lineWidth = 2.5;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                
                ctx.beginPath();
                ctx.moveTo(lastX, lastY);
                ctx.lineTo(x, y);
                ctx.stroke();
                
                lastX = x;
                lastY = y;
            }
        }, false);

        canvas.addEventListener('touchend', function(e) {
            e.preventDefault();
            isDrawing = false;
        }, false);

        // Clear button
        document.getElementById('clear-canvas').addEventListener('click', function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        });

        // Form submit
        document.getElementById('canvas-form').addEventListener('submit', function(e) {
            e.preventDefault();
            document.getElementById('signature_canvas_data').value = canvas.toDataURL('image/png');
            this.submit();
        });

        // Switch tab
        window.switchTab = function(tab) {
            const canvasPanel = document.getElementById('canvas-panel');
            const uploadPanel = document.getElementById('upload-panel');
            const btnCanvas = document.getElementById('tab-canvas');
            const btnUpload = document.getElementById('tab-upload');

            if (tab === 'canvas') {
                canvasPanel.classList.add('show', 'active');
                uploadPanel.classList.remove('show', 'active');
                btnCanvas.classList.add('btn-primary');
                btnCanvas.classList.remove('btn-outline-primary');
                btnUpload.classList.add('btn-outline-primary');
                btnUpload.classList.remove('btn-primary');
            } else {
                uploadPanel.classList.add('show', 'active');
                canvasPanel.classList.remove('show', 'active');
                btnUpload.classList.add('btn-primary');
                btnUpload.classList.remove('btn-outline-primary');
                btnCanvas.classList.add('btn-outline-primary');
                btnCanvas.classList.remove('btn-primary');
            }
        };

        // Upload preview
        const uploadInput = document.getElementById('signature_file');
        const uploadPreview = document.getElementById('upload-preview');
        const uploadPreviewImg = document.getElementById('upload-preview-img');

        if (uploadInput) {
            uploadInput.addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        uploadPreviewImg.src = event.target.result;
                        uploadPreview.style.display = 'block';
                    };
                    reader.readAsDataURL(e.target.files[0]);
                }
            });
        }
    }
});
</script>
@endpush
@endsection

