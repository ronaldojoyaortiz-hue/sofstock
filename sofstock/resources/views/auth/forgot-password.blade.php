<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Recuperar contraseña | SofStock</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4"><main class="w-full max-w-md rounded-2xl bg-white p-8 shadow-lg">
    <h1 class="text-2xl font-bold text-slate-800">Recuperar contraseña</h1><p class="mt-2 text-slate-600">Escribe tu correo para consultar tu pregunta de seguridad.</p>
    @if ($errors->any())<div class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('password.recovery.question') }}" class="mt-6 space-y-5">@csrf
        <div><label for="correo" class="mb-2 block text-sm font-medium">Correo electrónico</label><input id="correo" name="correo" type="email" value="{{ old('correo') }}" required autocomplete="username" class="w-full rounded-lg border border-slate-300 p-3"></div>
        <button class="w-full rounded-lg bg-green-600 py-3 font-semibold text-white hover:bg-green-700">Continuar</button>
    </form><a href="{{ route('login') }}" class="mt-5 block text-center text-sm text-green-700">Volver al inicio de sesión</a>
</main></body></html>
