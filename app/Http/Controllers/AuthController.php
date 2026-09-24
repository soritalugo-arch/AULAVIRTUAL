<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        $token = csrf_token();
        $action = route('login.submit');
        $error = session('errors')?->first('email');
        $oldEmail = old('email');

        $html = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AulaVirtual - Iniciar sesion</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <form method="POST" action="$action" class="bg-white p-8 rounded-lg shadow-md w-full max-w-sm">
        <input type="hidden" name="_token" value="$token">
        <h1 class="text-2xl font-bold mb-6 text-center">AulaVirtual</h1>

        <label class="block mb-2 text-sm font-medium">Email</label>
        <input type="email" name="email" value="$oldEmail" required autofocus
               class="w-full border rounded-md px-3 py-2 mb-4" placeholder="correo@aula.edu">

        <label class="block mb-2 text-sm font-medium">Contrasena</label>
        <input type="password" name="password" required
               class="w-full border rounded-md px-3 py-2 mb-4" placeholder="••••••••">

        <p id="error" class="text-red-600 text-sm mb-4 hidden">$error</p>

        <button type="submit"
                class="w-full bg-blue-600 text-white rounded-md py-2 hover:bg-blue-700">
            Iniciar sesion
        </button>
    </form>
    <script>
        const err = document.getElementById('error');
        if (err.textContent.trim() !== '') err.classList.remove('hidden');
    </script>
</body>
</html>
HTML;

        return response($html);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();

            return redirect()->intended($this->homePara(Auth::user()));
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

    private function homePara($user)
    {
        if ($user->roles->contains('nombre', 'admin')) {
            return route('admin.dashboard');
        }

        if ($user->roles->contains('nombre', 'profesor')) {
            return route('profesor.dashboard');
        }

        return route('estudiante.dashboard');
    }
}