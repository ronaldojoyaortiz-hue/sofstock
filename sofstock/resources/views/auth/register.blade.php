<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear cuenta | SofStock</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
    <main class="w-full max-w-2xl rounded-2xl bg-white p-8 shadow-lg">
        <h1 class="text-2xl font-bold text-slate-800">Crear cuenta</h1>
        <p class="mt-2 text-slate-600">El administrador debe aprobar tu solicitud antes de que puedas iniciar sesión.</p>
        @if ($errors->any())
            <div class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-5">
            @csrf
            <div>
                <label for="nombre" class="mb-2 block text-sm font-medium">Nombre</label>
                <input id="nombre" name="nombre" value="{{ old('nombre') }}" required maxlength="100" autocomplete="given-name" class="w-full rounded-lg border border-slate-300 p-3">
            </div>
            <div>
                <label for="apellido" class="mb-2 block text-sm font-medium">Apellido</label>
                <input id="apellido" name="apellido" value="{{ old('apellido') }}" required maxlength="100" autocomplete="family-name" class="w-full rounded-lg border border-slate-300 p-3">
            </div>
            <div>
                <label for="correo" class="mb-2 block text-sm font-medium">Correo electrónico</label>
                <input id="correo" name="correo" type="email" value="{{ old('correo') }}" required maxlength="150" autocomplete="email" class="w-full rounded-lg border border-slate-300 p-3">
            </div>
            <div>
                <label for="telefono" class="mb-2 block text-sm font-medium">Teléfono</label>
                <input id="telefono" name="telefono" type="tel" value="{{ old('telefono') }}" maxlength="20" autocomplete="tel" class="w-full rounded-lg border border-slate-300 p-3">
            </div>
            <div>
                <label for="direccion" class="mb-2 block text-sm font-medium">Dirección</label>
                <input id="direccion" name="direccion" value="{{ old('direccion') }}" maxlength="255" autocomplete="street-address" class="w-full rounded-lg border border-slate-300 p-3">
            </div>
            <div>
                <label for="genero" class="mb-2 block text-sm font-medium">Género</label>
                <select id="genero" name="genero" class="w-full rounded-lg border border-slate-300 p-3">
                    <option value="">Selecciona una opción</option>
                    @foreach ($generos as $genero)
                        <option value="{{ $genero }}" @selected(old('genero') === $genero)>{{ $genero }}</option>
                    @endforeach
                </select>
            </div>
            <div id="genero-detalle-contenedor" @if (old('genero') !== 'Otro') hidden @endif>
                <label for="genero_detalle" class="mb-2 block text-sm font-medium">¿Cuál?</label>
                <input id="genero_detalle" name="genero_detalle" value="{{ old('genero_detalle') }}" maxlength="100" @required(old('genero') === 'Otro') @disabled(old('genero') !== 'Otro') class="w-full rounded-lg border border-slate-300 p-3">
                @error('genero_detalle')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="tipo_documento" class="mb-2 block text-sm font-medium">Tipo de documento</label>
                <select id="tipo_documento" name="tipo_documento" class="w-full rounded-lg border border-slate-300 p-3">
                    <option value="">Selecciona un tipo de documento</option>
                    @foreach ($tiposDocumento as $tipoDocumento)
                        <option value="{{ $tipoDocumento }}" @selected(old('tipo_documento') === $tipoDocumento)>{{ $tipoDocumento }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="documento" class="mb-2 block text-sm font-medium">Número de documento</label>
                <input id="documento" name="documento" value="{{ old('documento') }}" maxlength="30" autocomplete="off" class="w-full rounded-lg border border-slate-300 p-3">
            </div>
            <div>
                <label for="contrasena" class="mb-2 block text-sm font-medium">Contraseña (mínimo 8 caracteres)</label>
                <input id="contrasena" name="contrasena" type="password" required minlength="8" autocomplete="new-password" class="w-full rounded-lg border border-slate-300 p-3">
            </div>
            <div>
                <label for="contrasena_confirmation" class="mb-2 block text-sm font-medium">Confirmar contraseña</label>
                <input id="contrasena_confirmation" name="contrasena_confirmation" type="password" required minlength="8" autocomplete="new-password" class="w-full rounded-lg border border-slate-300 p-3">
            </div>
            <button class="w-full rounded-lg bg-green-600 py-3 font-semibold text-white hover:bg-green-700">Enviar solicitud</button>
        </form>
        <a href="{{ route('login') }}" class="mt-5 block text-center text-sm text-green-700">Volver al inicio de sesión</a>
    </main>
    <script>
        const generoSelect = document.getElementById('genero');
        const generoDetalleContainer = document.getElementById('genero-detalle-contenedor');
        const generoDetalleInput = document.getElementById('genero_detalle');

        generoSelect.addEventListener('change', () => {
            const requiereDetalle = generoSelect.value === 'Otro';
            generoDetalleContainer.hidden = !requiereDetalle;
            generoDetalleInput.disabled = !requiereDetalle;
            generoDetalleInput.required = requiereDetalle;
        });
    </script>
</body>
</html>
