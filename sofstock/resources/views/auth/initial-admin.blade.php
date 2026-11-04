<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear administrador | SofStock</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #f8fafc; }
        .sena-verde { background-color: #39A900; }
        .sena-verde:hover { background-color: #2f8b00; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <main class="w-full max-w-md rounded-2xl bg-white p-8 shadow-lg">
        <div class="mb-8 text-center">
            <div class="sena-verde mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl text-2xl font-bold text-white">S</div>
            <h1 class="text-3xl font-bold text-gray-800">Crear administrador</h1>
            <p class="mt-2 text-sm text-gray-500">Registra la primera cuenta para configurar SofStock.</p>
        </div>

        @if ($errors->any())
            <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('initial-admin.store') }}" class="space-y-5">
            @csrf
            <div>
                <label for="nombre" class="mb-2 block text-sm font-semibold text-gray-700">Nombre</label>
                <input id="nombre" name="nombre" value="{{ old('nombre') }}" required maxlength="100" autocomplete="given-name" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500">
                @error('nombre') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="apellido" class="mb-2 block text-sm font-semibold text-gray-700">Apellido</label>
                <input id="apellido" name="apellido" value="{{ old('apellido') }}" required maxlength="100" autocomplete="family-name" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500">
                @error('apellido') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="correo" class="mb-2 block text-sm font-semibold text-gray-700">Correo electrónico</label>
                <input id="correo" name="correo" type="email" value="{{ old('correo') }}" required maxlength="150" autocomplete="username" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500">
                @error('correo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="contrasena" class="mb-2 block text-sm font-semibold text-gray-700">Contraseña (mínimo 8 caracteres)</label>
                <input id="contrasena" name="contrasena" type="password" required minlength="8" autocomplete="new-password" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500">
                @error('contrasena') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="contrasena_confirmation" class="mb-2 block text-sm font-semibold text-gray-700">Confirmar contraseña</label>
                <input id="contrasena_confirmation" name="contrasena_confirmation" type="password" required minlength="8" autocomplete="new-password" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <button type="submit" class="sena-verde w-full rounded-lg py-3 font-semibold text-white transition duration-200">Crear cuenta de administrador</button>
        </form>

        <a href="{{ route('login') }}" class="mt-5 block text-center text-sm font-medium text-green-700 hover:text-green-800">Volver al inicio de sesión</a>
    </main>
</body>
</html>
