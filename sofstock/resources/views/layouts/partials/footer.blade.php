<footer class="border-t border-slate-200 bg-white">
    <div class="px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-sm text-slate-400">
        <p>&copy; {{ date('Y') }} {{ config('app.name', 'AdminPanel') }}. Todos los derechos reservados.</p>
        <p>Versión {{ config('app.version', '1.0.0') }}</p>
    </div>
</footer>
