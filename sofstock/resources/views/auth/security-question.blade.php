<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configurar recuperación | SofStock</title><script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
    <main class="w-full max-w-lg rounded-2xl bg-white p-8 shadow-lg">
        <h1 class="text-2xl font-bold text-slate-800">Configura tu recuperación</h1>
        <p class="mt-2 text-slate-600">Antes de continuar, elige una pregunta y guarda una respuesta que recuerdes. No aparecerán en tu ficha de usuario.</p>
        @if ($errors->any())<div class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('security-question.save') }}" class="mt-6 space-y-5">@csrf
            <div><label for="pregunta_seguridad" class="mb-2 block text-sm font-medium">Pregunta de seguridad</label><select id="pregunta_seguridad" name="pregunta_seguridad" required class="w-full rounded-lg border border-slate-300 p-3"><option value="">Selecciona una pregunta</option>@foreach ($preguntas as $pregunta)<option value="{{ $pregunta }}" @selected(old('pregunta_seguridad') === $pregunta)>{{ $pregunta }}</option>@endforeach</select></div>
            <div><label for="respuesta_seguridad" class="mb-2 block text-sm font-medium">Tu respuesta</label><input id="respuesta_seguridad" name="respuesta_seguridad" required minlength="2" maxlength="120" autocomplete="off" class="w-full rounded-lg border border-slate-300 p-3"></div>
            <button class="w-full rounded-lg bg-green-600 py-3 font-semibold text-white hover:bg-green-700">Guardar y continuar</button>
        </form>
    </main>
</body></html>
