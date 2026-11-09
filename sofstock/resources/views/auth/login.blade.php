
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | SofStock</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            background: #f8fafc;
        }

        .sena-verde {
            background-color: #39A900;
        }

        .sena-verde:hover {
            background-color: #2f8b00;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <main class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">

        <div class="text-center mb-8">
            <img
                src="{{ asset('images/sofstock-logo.png') }}"
                alt="SofStock: gestiona, controla y crece"
                class="mx-auto mb-4 h-40 w-40 rounded-full object-cover shadow-md"
            >

            <h1 class="text-3xl font-bold text-gray-800">
                SofStock
            </h1>

            <p class="mt-2 text-gray-500">
                Sistema de gestión de inventario
            </p>

        </div>

        @if ($errors->any())
            <div class="mb-5 rounded-lg bg-red-50 border border-red-200
                        p-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif
        @if (session('success'))
            <div class="mb-5 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif
        @if (session('warning'))
            <div class="mb-5 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">{{ session('warning') }}</div>
        @endif

        <form method="POST" action="{{ route('login.authenticate') }}">
            @csrf

            <div class="mb-5">
                <label for="correo"
                       class="block mb-2 text-sm font-semibold text-gray-700">
                    Correo electrónico
                </label>

                <input
                    type="email"
                    id="correo"
                    name="correo"
                    value="{{ old('correo') }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="ejemplo@correo.com"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3
                           outline-none focus:ring-2 focus:ring-green-500
                           focus:border-green-500"
                >
            </div>

            <div class="mb-6">
                <label for="contrasena"
                       class="block mb-2 text-sm font-semibold text-gray-700">
                    Contraseña
                </label>

                <input
                    type="password"
                    id="contrasena"
                    name="contrasena"
                    required
                    autocomplete="current-password"
                    placeholder="Ingresa tu contraseña"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3
                           outline-none focus:ring-2 focus:ring-green-500
                           focus:border-green-500"
                >
            </div>

            <button
                type="submit"
                class="w-full sena-verde text-white font-semibold
                       rounded-lg py-3 transition duration-200">
                Iniciar sesión
            </button>
        </form>

        <a href="{{ route('password.forgot') }}" class="mt-5 block text-center text-sm font-medium text-green-700 hover:text-green-800">¿Olvidaste tu contraseña?</a>
        @if ($setupDisponible)
            <a href="{{ route('initial-admin.create') }}" class="mt-3 block text-center text-sm font-medium text-green-700 hover:text-green-800">Crear la primera cuenta de administrador</a>
        @else
            <a href="{{ route('register') }}" class="mt-3 block text-center text-sm font-medium text-green-700 hover:text-green-800">Crear cuenta</a>
        @endif

        <div class="mt-8 text-center text-xs text-gray-400">
            SofStock · Sistema de gestión de inventario
        </div>

    </main>

</body>
</html>
