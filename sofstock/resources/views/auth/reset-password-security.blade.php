<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Restablecer contraseña | SofStock</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4"><main class="w-full max-w-md rounded-2xl bg-white p-8 shadow-lg">
    <h1 class="text-2xl font-bold text-slate-800">Verifica tu identidad</h1>
    @if ($errors->any())<div class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('password.recovery.reset') }}" class="mt-6 space-y-5">@csrf
        <p class="rounded-lg bg-slate-50 p-4 font-medium text-slate-700">{{ $pregunta }}</p>
        <div><label for="respuesta_seguridad" class="mb-2 block text-sm font-medium">Respuesta</label><input id="respuesta_seguridad" name="respuesta_seguridad" required maxlength="120" autocomplete="off" class="w-full rounded-lg border border-slate-300 p-3"></div>
        <div><label for="contrasena" class="mb-2 block text-sm font-medium">Nueva contraseña</label><input id="contrasena" type="password" name="contrasena" required minlength="8" autocomplete="new-password" class="w-full rounded-lg border border-slate-300 p-3"></div>
        <div><label for="contrasena_confirmation" class="mb-2 block text-sm font-medium">Confirma la contraseña</label><input id="contrasena_confirmation" type="password" name="contrasena_confirmation" required minlength="8" autocomplete="new-password" class="w-full rounded-lg border border-slate-300 p-3"></div>
        <button class="w-full rounded-lg bg-green-600 py-3 font-semibold text-white hover:bg-green-700">Restablecer contraseña</button>
    </form><a href="{{ route('login') }}" class="mt-5 block text-center text-sm text-green-700">Volver al inicio de sesión</a>
</main></body></html>
