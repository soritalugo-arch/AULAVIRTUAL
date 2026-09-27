<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();

            return redirect()->intended(route(self::nombreRutaHome(Auth::user())));
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Nombre de la ruta del panel según el rol del usuario.
     */
    public static function nombreRutaHome($user): string
    {
        if ($user->roles->contains('nombre', 'admin')) {
            return 'admin.dashboard';
        }

        if ($user->roles->contains('nombre', 'profesor')) {
            return 'profesor.dashboard';
        }

        return 'estudiante.dashboard';
    }
}
