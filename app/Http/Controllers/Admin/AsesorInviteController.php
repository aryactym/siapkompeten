<?php

namespace App\Http\Controllers\Admin;

use App\Actions\InviteAsesor;
use App\Http\Controllers\Controller;
use App\Models\AsesorInvitation;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Undang Keanggotaan asesor (FR-ASR-09): admin mengundang calon asesor yang
 * langsung dibuatkan akun + profil, lalu menerima tautan aktivasi via email.
 */
class AsesorInviteController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AsesorInvitation::class);

        $invitations = AsesorInvitation::query()
            ->with(['asesor.user', 'invitedBy'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Segarkan status kedaluwarsa untuk tampilan.
        $invitations->getCollection()->transform(function (AsesorInvitation $invitation): AsesorInvitation {
            if ($invitation->isPending() && $invitation->isExpired()) {
                $invitation->status = AsesorInvitation::STATUS_KEDALUWARSA;
            }

            // Asesor dianggap diterima begitu akunnya sudah pernah login (aktivasi selesai).
            if ($invitation->status === AsesorInvitation::STATUS_MENUNGGU
                && $invitation->asesor?->user?->last_login_at !== null) {
                $invitation->status = AsesorInvitation::STATUS_DITERIMA;
                $invitation->responded_at = $invitation->asesor->user->last_login_at;
            }

            return $invitation;
        });

        return view('admin.asesor.invite.index', [
            'invitations' => $invitations,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', AsesorInvitation::class);

        return view('admin.asesor.invite.create', [
            'invitation' => new AsesorInvitation,
        ]);
    }

    public function store(Request $request, InviteAsesor $action): RedirectResponse
    {
        $this->authorize('create', AsesorInvitation::class);

        $tenantId = (string) TenantContext::requireId();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'nik' => ['nullable', 'string', 'digits:16'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->where('institution_id', $tenantId),
            ],
            'no_register' => [
                'required',
                'string',
                'max:100',
                Rule::unique('asesors', 'no_register')->where('institution_id', $tenantId),
            ],
            'bidang_keahlian' => ['nullable', 'string', 'max:150'],
        ]);

        try {
            $invitation = $action->execute($validated, $request->user());
        } catch (ValidationException $e) {
            throw $e;
        }

        $action->sendActivation($invitation);

        return redirect()
            ->route('admin.asesor.invite.show', $invitation)
            ->with('success', 'Undangan asesor dibuat. Tautan aktivasi telah dikirim ke email asesor.');
    }

    public function show(AsesorInvitation $invitation): View
    {
        $this->authorize('viewAny', AsesorInvitation::class);

        $invitation->load(['asesor.user', 'invitedBy']);

        return view('admin.asesor.invite.show', [
            'invitation' => $invitation,
        ]);
    }

    public function destroy(Request $request, AsesorInvitation $invitation): RedirectResponse
    {
        $this->authorize('delete', $invitation);

        if ($invitation->asesor !== null) {
            $invitation->asesor->update(['status' => 'nonaktif']);
        }

        $invitation->update([
            'status' => AsesorInvitation::STATUS_DIBATALKAN,
            'responded_at' => now(),
        ]);

        activity('asesor')
            ->causedBy($request->user())
            ->performedOn($invitation)
            ->log('Undangan asesor dibatalkan.');

        return redirect()
            ->route('admin.asesor.invite.index')
            ->with('success', 'Undangan asesor dibatalkan. Akun asesor dinonaktifkan.');
    }
}
