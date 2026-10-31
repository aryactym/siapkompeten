<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->toString();
        $roleFilter = $request->string('role')->toString();
        $statusFilter = $request->string('status')->toString();

        $users = User::query()
            ->with(['roles', 'institution'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->when($roleFilter !== '', function ($query) use ($roleFilter): void {
                $query->whereHas('roles', fn ($q) => $q->where('name', $roleFilter));
            })
            ->when($statusFilter !== '', fn ($query) => $query->where('status', $statusFilter))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $roles = Role::where('name', '!=', 'super-admin')->orderBy('name')->pluck('name');

        return view('admin.user.index', [
            'users' => $users,
            'search' => $search,
            'roleFilter' => $roleFilter,
            'statusFilter' => $statusFilter,
            'roles' => $roles,
        ]);
    }

    public function create(): View
    {
        $roles = Role::where('name', '!=', 'super-admin')->orderBy('name')->pluck('name');

        return view('admin.user.create', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'username' => ['nullable', 'string', 'max:50', Rule::unique('users', 'username')],
            'nik' => ['nullable', 'string', 'regex:/^\d{16}$/'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'role' => ['required', 'string', Rule::in(Role::where('name', '!=', 'super-admin')->pluck('name'))],
            'status' => ['required', 'in:aktif,nonaktif,diblokir'],
        ]);

        $institutionId = TenantContext::id();

        $user = User::create([
            'institution_id' => $institutionId,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'] ?: null,
            'nik' => $validated['nik'] ?: null,
            'phone' => $validated['phone'] ?: null,
            'password' => $validated['password'],
            'status' => $validated['status'],
            'email_verified_at' => now(),
        ]);

        $user->assignRole($validated['role']);
        $user->institutions()->attach($institutionId, ['role' => $validated['role']]);

        activity('user')
            ->causedBy($request->user())
            ->performedOn($user)
            ->withProperties(['role' => $validated['role']])
            ->log('Pengguna baru dibuat oleh admin.');

        return redirect()
            ->route('admin.user.index')
            ->with('success', 'Pengguna "'.$user->name.'" berhasil dibuat dengan peran '.$validated['role'].'.');
    }

    public function edit(User $user): View
    {
        $roles = Role::where('name', '!=', 'super-admin')->orderBy('name')->pluck('name');
        $currentRole = $user->roles->where('name', '!=', 'super-admin')->first()?->name;

        return view('admin.user.edit', [
            'user' => $user,
            'roles' => $roles,
            'currentRole' => $currentRole,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'username' => ['nullable', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'nik' => ['nullable', 'string', 'regex:/^\d{16}$/'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'string', Rule::in(Role::where('name', '!=', 'super-admin')->pluck('name'))],
            'status' => ['required', 'in:aktif,nonaktif,diblokir'],
            'password' => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'] ?: null,
            'nik' => $validated['nik'] ?: null,
            'phone' => $validated['phone'] ?: null,
            'status' => $validated['status'],
        ];

        if (filled($validated['password'] ?? null)) {
            $updateData['password'] = $validated['password'];
        }

        $user->update($updateData);

        // Update role jika berubah
        $currentRole = $user->roles->where('name', '!=', 'super-admin')->first()?->name;
        if ($currentRole !== $validated['role']) {
            if ($currentRole) {
                $user->removeRole($currentRole);
            }
            $user->assignRole($validated['role']);

            // Update pivot role
            $user->institutions()->updateExistingPivot(
                $user->institution_id,
                ['role' => $validated['role']]
            );
        }

        activity('user')
            ->causedBy($request->user())
            ->performedOn($user)
            ->withProperties(['role' => $validated['role']])
            ->log('Data pengguna diperbarui oleh admin.');

        return redirect()
            ->route('admin.user.index')
            ->with('success', 'Pengguna "'.$user->name.'" berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        activity('user')
            ->causedBy($request->user())
            ->performedOn($user)
            ->log('Pengguna dihapus oleh admin.');

        return redirect()
            ->route('admin.user.index')
            ->with('success', 'Pengguna "'.$user->name.'" berhasil dihapus.');
    }
}
