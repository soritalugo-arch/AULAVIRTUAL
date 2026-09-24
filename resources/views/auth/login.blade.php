<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AulaVirtual - Iniciar sesion</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <form method="POST" action="{{ route('login.submit') }}" class="bg-white p-8 rounded-lg shadow-md w-full max-w-sm">
        @csrf
        <h1 class="text-2xl font-bold mb-6 text-center">AulaVirtual</h1>

        <label for="email" class="block mb-2 text-sm font-medium">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
               class="w-full border rounded-md px-3 py-2 mb-4" placeholder="correo@aula.edu">

        <label for="password" class="block mb-2 text-sm font-medium">Contrasena</label>
        <input type="password" id="password" name="password" required
               class="w-full border rounded-md px-3 py-2 mb-4" placeholder="••••••••">

        @if ($errors->any())
            <p id="error" class="text-red-600 text-sm mb-4 hidden">{{ $errors->first('email') }}</p>
        @else
            <p id="error" class="text-red-600 text-sm mb-4 hidden"></p>
        @endif

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