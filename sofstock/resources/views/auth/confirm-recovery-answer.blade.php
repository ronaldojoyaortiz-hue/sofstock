<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Guardar respuesta | SofStock</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4"><main class="w-full max-w-md rounded-2xl bg-white p-8 shadow-lg">
    <div class="mb-5 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">La nueva contraseña ya se guardó en la ficha del usuario.</div>
    <h1 class="text-2xl font-bold text-slate-800">Guarda de nuevo tu respuesta</h1>
    <p class="mt-2 text-slate-600">Para terminar, vuelve a escribir la respuesta que usarás para recuperar la contraseña.</p>
    @if ($errors->any())<div class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('password.recovery.save-answer') }}" class="mt-6 space-y-5">@csrf
        <p class="rounded-lg bg-slate-50 p-4 font-medium text-slate-700">{{ $pregunta }}</p>
        <div><label for="respuesta_seguridad" class="mb-2 block text-sm font-medium">Respuesta de seguridad</label><input id="respuesta_seguridad" name="respuesta_seguridad" required minlength="2" maxlength="120" autocomplete="off" class="w-full rounded-lg border border-slate-300 p-3"></div>
        <button class="w-full rounded-lg bg-green-600 py-3 font-semibold text-white hover:bg-green-700">Guardar</button>
    </form>
</main></body></html>
