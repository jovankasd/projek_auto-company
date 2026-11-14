<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => UserRole::Customer,
            'is_active' => true,
        ]);

        event(new Registered($user));

        Auth::login($user);

        if ($request->session()->has('pending_test_drive')) {
            return redirect()->route('home')
                ->with('open_modal', 'test_drive')
                ->with('info', 'Pendaftaran akun berhasil! Data test drive Anda telah dipulihkan. Silakan periksa kembali dan klik konfirmasi untuk menyelesaikan pengajuan.');
        }

        if ($request->session()->has('pending_service_booking')) {
            return redirect()->route('home')
                ->with('open_modal', 'service_booking')
                ->with('info', 'Pendaftaran akun berhasil! Data booking servis Anda telah dipulihkan. Silakan periksa kembali dan klik konfirmasi untuk menyelesaikan booking.');
        }

        return redirect(route('dashboard', absolute: false));
    }
}
