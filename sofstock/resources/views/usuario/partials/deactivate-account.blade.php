<x-card class="mt-6 border border-amber-200" title="Desactivar mi cuenta">
    <p class="mb-4 text-sm text-slate-600">
        Tu sesión se cerrará y no podrás volver a ingresar hasta que un administrador reactive tu cuenta. Tus datos, actas y auditorías se conservarán.
    </p>
    <form method="POST" action="{{ route('account.deactivate') }}" class="space-y-4"
        onsubmit="return confirm('¿Estás seguro de que deseas desactivar tu cuenta? No podrás volver a ingresar hasta que un administrador la reactive.')">
        @csrf
        <div>
            <label for="contrasena_actual" class="mb-1.5 block text-sm font-medium text-slate-700">Confirma tu contraseña actual</label>
            <input id="contrasena_actual" name="contrasena_actual" type="password" required autocomplete="current-password"
                class="w-full max-w-md rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200">
            @error('contrasena_actual')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <label class="flex items-start gap-2 text-sm text-slate-700">
            <input type="checkbox" name="confirmar_desactivacion" value="1" required class="mt-0.5 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
            <span>Entiendo que mi cuenta quedará desactivada y necesitaré que un administrador la reactive.</span>
        </label>
        @error('confirmar_desactivacion')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
        <button type="submit" class="inline-flex items-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
            Desactivar mi cuenta
        </button>
    </form>
</x-card>
