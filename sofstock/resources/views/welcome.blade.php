@extends('layouts.app')

@section('title', 'Panel de administración')
@section('page-title', 'Panel de administración')

@section('content')

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">
            Panel de administración
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Selecciona la sección que deseas administrar.
        </p>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">

        {{-- Categorías --}}
        <a href="{{ route('categorias.index') }}"
           class="group rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-md">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 7h5l2 2h11v10H3V7z"/>
                </svg>
            </div>

            <h2 class="text-lg font-semibold text-slate-800 group-hover:text-blue-600">
                Categorías
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Administra las categorías de productos.
            </p>
        </a>


        {{-- Roles --}}
        <a href="{{ route('roles.index') }}"
           class="group rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-md">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 text-purple-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                </svg>
            </div>

            <h2 class="text-lg font-semibold text-slate-800 group-hover:text-purple-600">
                Roles
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Gestiona los roles del sistema.
            </p>
        </a>


        {{-- Productos --}}
        <a href="{{ route('productos.index') }}"
           class="group rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-md">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                </svg>
            </div>

            <h2 class="text-lg font-semibold text-slate-800 group-hover:text-emerald-600">
                Productos
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Administra los productos del inventario.
            </p>
        </a>


        {{-- Facturas --}}
        <a href="{{ route('facturas.index') }}"
           class="group rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-md">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 14h6M9 18h6M7 3h10a2 2 0 012 2v16l-7-3-7 3V5a2 2 0 012-2z"/>
                </svg>
            </div>

            <h2 class="text-lg font-semibold text-slate-800 group-hover:text-amber-600">
                Facturas
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Consulta y administra las facturas.
            </p>
        </a>


        {{-- Detalles de factura --}}
        <a href="{{ route('detallefacturas.index') }}"
           class="group rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-md">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-cyan-100 text-cyan-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 10h16M4 14h10M4 18h10"/>
                </svg>
            </div>

            <h2 class="text-lg font-semibold text-slate-800 group-hover:text-cyan-600">
                Detalles de factura
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Consulta los detalles de las facturas.
            </p>
        </a>


        {{-- Proveedores --}}
        <a href="{{ route('proveedores.index') }}"
           class="group rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-md">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-rose-100 text-rose-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M9 10h.01M12 10h.01M15 10h.01"/>
                </svg>
            </div>

            <h2 class="text-lg font-semibold text-slate-800 group-hover:text-rose-600">
                Proveedores
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Gestiona los proveedores del sistema.
            </p>
        </a>


        {{-- Usuarios --}}
        <a href="{{ route('usuarios.index') }}"
           class="group rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-md">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                </svg>
            </div>

            <h2 class="text-lg font-semibold text-slate-800 group-hover:text-slate-900">
                Usuarios
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Gestiona los usuarios de tu sistema.
            </p>
        </a>

    </div>

@endsection

