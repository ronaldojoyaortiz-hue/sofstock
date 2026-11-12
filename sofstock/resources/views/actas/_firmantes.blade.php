<fieldset class="space-y-4 rounded border border-gray-400 p-4">
    <legend class="px-2 font-semibold">Firmas</legend>

    <details class="relative">
        <summary class="flex w-fit cursor-pointer list-none items-center gap-2 rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">
            <span>Seleccionar roles</span>
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                 viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z"
                    clip-rule="evenodd"/>
            </svg>
        </summary>

        <div id="roles_firmantes"
             class="absolute z-20 mt-2 grid max-h-60 w-72 grid-cols-1 gap-2 overflow-y-auto rounded-lg border border-gray-200 bg-white p-3 shadow-lg">

            @foreach($rolesFirmantes as $rolFirmante)
                <label class="flex cursor-pointer items-center gap-2 rounded p-2 hover:bg-blue-50">
                    <input
                        type="checkbox"
                        name="roles_firmantes[]"
                        value="{{ $rolFirmante->id_rol }}"
                        @checked(in_array(
                            (string) $rolFirmante->id_rol,
                            array_map('strval', old('roles_firmantes', $rolesFirmantesIniciales ?? [])),
                            true
                        ))
                        class="rounded border-gray-400 text-blue-700">

                    <span class="text-sm text-gray-700">
                        {{ $rolFirmante->nombre_rol }}
                    </span>
                </label>
            @endforeach
        </div>
    </details>

    @error('roles_firmantes')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror

    @error('roles_firmantes.*')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror

    <div id="firmas-preview"
         class="grid grid-cols-1 gap-x-8 gap-y-10 sm:grid-cols-2"
         aria-live="polite">
    </div>
</fieldset>

<script>
(() => {
    const roles = document.getElementById('roles_firmantes');
    const preview = document.getElementById('firmas-preview');

    const usuarios = @json($usuariosFirmantes);

    function renderFirmas() {
        preview.replaceChildren();

        const rolesMarcados = Array.from(
            roles.querySelectorAll('input[name="roles_firmantes[]"]:checked')
        );

        const rolesSeleccionados = new Set(
            rolesMarcados.map(input => String(input.value))
        );

        const firmantes = usuarios.filter(usuario =>
            rolesSeleccionados.has(String(usuario.id_rol))
        );

        firmantes.forEach(usuario => {
            const bloque = document.createElement('div');
            bloque.className = 'pt-8 text-center';

            const linea = document.createElement('div');
            linea.className = 'border-t border-gray-700 pt-2';

            const nombre = document.createElement('p');
            nombre.className = 'font-semibold';
            nombre.textContent = usuario.nombre;

            const cargo = document.createElement('p');
            cargo.className = 'text-sm text-gray-600';
            cargo.textContent = usuario.cargo || 'Sin cargo';

            const firma = document.createElement('p');
            firma.className = 'mt-1 text-xs text-gray-500';
            firma.textContent = 'Firma';

            linea.append(nombre, cargo, firma);
            bloque.append(linea);
            preview.append(bloque);
        });
    }

    roles.addEventListener('change', renderFirmas);

    renderFirmas();
})();
</script>
