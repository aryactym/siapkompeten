<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendNotifikasi;
use App\Models\Notifikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotifikasiController extends Controller
{
    public function index(Request $request): View
    {
        $notifikasis = Notifikasi::query()
            ->with('user')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('channel'), fn ($query) => $query->where('channel', $request->string('channel')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.notifikasi.index', [
            'notifikasis' => $notifikasis,
            'status' => $request->string('status')->toString(),
            'channel' => $request->string('channel')->toString(),
        ]);
    }

    public function retry(Notifikasi $notifikasi): RedirectResponse
    {
        if ($notifikasi->status === 'terkirim') {
            return back()->with('error', 'Notifikasi sudah terkirim dan tidak perlu dikirim ulang.');
        }

        $notifikasi->update([
            'status' => 'menunggu',
            'error' => null,
        ]);

        SendNotifikasi::dispatch($notifikasi->id);

        return back()->with('success', 'Notifikasi dijadwalkan untuk dikirim ulang.');
    }
}
