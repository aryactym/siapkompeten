<?php

namespace App\Http\Controllers\Asesi;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Pendaftaran;
use App\Models\RekeningBank;
use App\Services\BuktiBayarStorage;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PembayaranController extends Controller
{
    /**
     * Tampilan tagihan, rekening tujuan, dan bukti bayar (FR-PAY-04).
     */
    public function show(Request $request, Pendaftaran $pendaftaran, BuktiBayarStorage $storage): View
    {
        $this->authorize('view', $pendaftaran);

        $rekeningBanks = RekeningBank::query()
            ->where('is_aktif', true)
            ->orderBy('nama_bank')
            ->get();

        return view('asesi.pembayaran.show', [
            'pendaftaran' => $pendaftaran,
            'rekeningBanks' => $rekeningBanks,
            'pembayaran' => $pendaftaran->pembayaran,
            'buktiTersedia' => $storage->exists($pendaftaran->pembayaran?->bukti_path),
            'rincianBiaya' => $pendaftaran->skema->biayaTagihan(),
            'totalTagihan' => $pendaftaran->totalTagihan(),
            'dapatMengunggah' => $pendaftaran->isWaitingPayment(),
        ]);
    }

    /**
     * Unduh bukti pembayaran sendiri dari disk privat.
     */
    public function bukti(Request $request, Pendaftaran $pendaftaran, BuktiBayarStorage $storage): BinaryFileResponse|StreamedResponse
    {
        $this->authorize('view', $pendaftaran);

        $pembayaran = $pendaftaran->pembayaran;

        abort_unless($pembayaran !== null && $storage->exists($pembayaran->bukti_path), 404, 'Berkas tidak ditemukan.');

        return Storage::disk(BuktiBayarStorage::DISK)->download(
            $pembayaran->bukti_path,
            $pembayaran->bukti_original_name ?: 'bukti-pembayaran'
        );
    }

    /**
     * Unggah bukti pembayaran (disk privat) dan tandai pendaftaran menunggu validasi.
     */
    public function store(Request $request, Pendaftaran $pendaftaran, BuktiBayarStorage $storage): RedirectResponse
    {
        $this->authorize('view', $pendaftaran);

        abort_unless(
            $pendaftaran->isWaitingPayment(),
            403,
            'Pendaftaran tidak berada pada tahap pembayaran.'
        );

        $validated = $request->validate([
            'rekening_bank_id' => [
                'required',
                'uuid',
                Rule::exists('rekening_banks', 'id')
                    ->where('institution_id', TenantContext::id())
                    ->where('is_aktif', true),
            ],
            'tanggal_bayar' => ['required', 'date', 'before_or_equal:today'],
            'catatan' => ['nullable', 'string', 'max:500'],
            'bukti' => [
                'required',
                'file',
                'mimes:'.implode(',', config('pendaftaran.document_mimes')),
                'mimetypes:application/pdf,image/jpeg,image/png',
                'max:'.config('pendaftaran.document_max_size_kb'),
            ],
        ]);

        $file = $validated['bukti'];
        $oldPath = null;
        $path = $storage->store($pendaftaran, $file);

        DB::transaction(function () use ($pendaftaran, $validated, $file, $path, $request, &$oldPath): void {
            $pembayaran = Pembayaran::firstOrNew(['pendaftaran_id' => $pendaftaran->id]);

            $oldPath = $pembayaran->bukti_path;
            $pembayaran->fill([
                'total' => $pendaftaran->totalTagihan(),
                'rekening_bank_id' => $validated['rekening_bank_id'],
                'bukti_path' => $path,
                'bukti_original_name' => $file->getClientOriginalName(),
                'bukti_mime' => $file->getMimeType(),
                'bukti_size' => $file->getSize(),
                'tanggal_bayar' => $validated['tanggal_bayar'],
                'status' => Pembayaran::STATUS_MENUNGGU,
                'catatan' => $validated['catatan'] ?? null,
                'divalidasi_oleh' => null,
                'divalidasi_pada' => null,
            ]);
            $pembayaran->save();

            $pendaftaran->update(['status' => Pendaftaran::STATUS_PEMBAYARAN_DIAJUKAN]);

            activity('pembayaran')
                ->causedBy($request->user())
                ->performedOn($pendaftaran)
                ->log('Asesi mengunggah bukti pembayaran.');
        });

        if (filled($oldPath) && $oldPath !== $path) {
            $storage->delete($oldPath);
        }

        return redirect()
            ->route('asesi.pembayaran.show', $pendaftaran)
            ->with('success', 'Bukti pembayaran berhasil diunggah dan menunggu validasi admin.');
    }
}
