
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
            <div class="mx-auto mb-4 w-16 h-16 sena-verde rounded-2xl
                        flex items-center justify-center text-white
                        text-2xl font-bold">
                S
            </div>

            <h1 class="text-3xl font-bold text-gray-800">
                SofStock
            </h1>

            <p class="mt-2 text-gray-500">
                Sistema de gestión de inventario
            </p>

            <p class="mt-1 text-sm text-gray-400">
                Inicia sesión para continuar
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-5 rounded-lg bg-red-50 border border-red-200
                        p-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
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

        <div class="mt-8 text-center text-xs text-gray-400">
            SofStock · Sistema de gestión de inventario
        </div>

    </main>

</body>
</html>
