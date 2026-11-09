<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $query = User::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('is_active', $request->boolean('status'));
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $roles = UserRole::cases();

        return view('dashboard.users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        $roles = UserRole::cases();

        return view('dashboard.users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['required', 'string', 'max:20'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'password' => ['required', Password::defaults()],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'email_verified_at' => now(),
        ]);

        $roleEnum = UserRole::from((string) $validated['role']);

        return redirect()->route('users.index')->with('success', "Pengguna {$user->name} ({$roleEnum->label()}) berhasil ditambahkan.");
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $roles = UserRole::cases();

        return view('dashboard.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone_number' => ['required', 'string', 'max:20'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'password' => ['nullable', Password::defaults()],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $isActive = $request->boolean('is_active');
        $currentRoleValue = $user->role instanceof UserRole ? $user->role->value : (string) $user->role;

        if ($user->id === $request->user()?->id) {
            if (! $isActive) {
                return back()->withInput()->withErrors([
                    'is_active' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.',
                ]);
            }

            if ((string) $validated['role'] !== $currentRoleValue) {
                return back()->withInput()->withErrors([
                    'role' => 'Anda tidak dapat mengubah peran akun Anda sendiri.',
                ]);
            }
        }

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'role' => $validated['role'],
            'is_active' => $isActive,
        ];

        $passwordChanged = ! empty($validated['password']);
        if ($passwordChanged) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        if (! $user->is_active || ($passwordChanged && $user->id !== $request->user()?->id)) {
            $user->revokeAccess();
        }

        return redirect()->route('users.index')->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $user->name;
        $user->forceFill(['is_active' => false])->saveQuietly();
        $user->revokeAccess();
        $user->delete();

        return redirect()->route('users.index')->with('success', "Pengguna {$name} berhasil dinonaktifkan/dihapus.");
    }
}
