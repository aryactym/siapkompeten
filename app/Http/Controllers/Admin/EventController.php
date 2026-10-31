<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventAsesmen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        $events = EventAsesmen::query()
            ->withCount('jadwals')
            ->orderByDesc('tahun')
            ->orderByDesc('tanggal_mulai')
            ->paginate(15);

        return view('admin.event.index', [
            'events' => $events,
        ]);
    }

    public function create(): View
    {
        return view('admin.event.form', [
            'event' => new EventAsesmen(['tahun' => now()->year, 'periode' => now()->month]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateEvent($request);

        $event = EventAsesmen::create($validated);

        activity('event')
            ->causedBy($request->user())
            ->performedOn($event)
            ->log('Event uji kompetensi dibuat.');

        return redirect()
            ->route('admin.event.index')
            ->with('success', 'Event uji kompetensi berhasil dibuat.');
    }

    public function edit(EventAsesmen $event): View
    {
        return view('admin.event.form', [
            'event' => $event,
        ]);
    }

    public function update(Request $request, EventAsesmen $event): RedirectResponse
    {
        $validated = $this->validateEvent($request);

        $event->update($validated);

        activity('event')
            ->causedBy($request->user())
            ->performedOn($event)
            ->log('Event uji kompetensi diperbarui.');

        return redirect()
            ->route('admin.event.index')
            ->with('success', 'Event uji kompetensi berhasil diperbarui.');
    }

    public function destroy(Request $request, EventAsesmen $event): RedirectResponse
    {
        if ($event->jadwals()->exists()) {
            return back()->with('error', 'Event masih memiliki jadwal uji dan tidak dapat dihapus.');
        }

        activity('event')
            ->causedBy($request->user())
            ->performedOn($event)
            ->log('Event uji kompetensi dihapus.');

        $event->delete();

        return redirect()
            ->route('admin.event.index')
            ->with('success', 'Event uji kompetensi berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateEvent(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'tahun' => ['required', 'integer', 'between:2000,2100'],
            'periode' => ['nullable', 'integer', 'between:1,12'],
            'tempat_ringkas' => ['nullable', 'string', 'max:200'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ]);
    }
}
