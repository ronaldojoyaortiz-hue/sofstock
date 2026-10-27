<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\DetalleFacturaController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\InmobiliarioController;
use App\Http\Controllers\ContratoController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.authenticate');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::middleware('auth')->group(function () {
    Route::get('/cambiar-contrasena', [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::post('/cambiar-contrasena', [AuthController::class, 'updatePassword'])->name('password.update');
});
Route::redirect('/', '/login')->name('home');
Route::get('/dashboard', DashboardController::class)->middleware('auth')->name('dashboard.index');
Route::resource('categorias', CategoriaController::class);
Route::resource('roles', RolController::class);
Route::resource('productos', ProductoController::class);
Route::resource('facturas', FacturaController::class)->only(['index', 'show']);
Route::resource('detallefacturas', DetalleFacturaController::class);
Route::resource('proveedores', ProveedorController::class);
Route::resource('usuarios', UsuarioController::class);
Route::post('/usuarios/{id}/restablecer-contrasena', [UsuarioController::class, 'restablecerContrasena'])->middleware('auth')->name('usuarios.restablecer-contrasena');
Route::get('auditorias', [AuditoriaController::class, 'index'])->name('auditoria.index');
Route::get('auditorias/{id}', [AuditoriaController::class, 'show'])->whereNumber('id')->name('auditoria.show');
Route::get('/contratos', [ContratoController::class, 'all'])->name('contratos.index');
Route::get('/contratos/crear', [ContratoController::class, 'createGlobal'])->name('contratos.create');
Route::post('/contratos/crear', [ContratoController::class, 'selectInmueble'])->name('contratos.select-inmueble');
Route::get('/inmuebles/{inmueble}/archivos/{indice}', [InmobiliarioController::class, 'archivo'])
    ->whereNumber(['inmueble', 'indice'])
    ->middleware('auth')
    ->name('inmuebles.archivos.show');
Route::get('/inmuebles/{inmueble}/contratos/{contrato}/archivos/{indice}', [ContratoController::class, 'archivo'])
    ->whereNumber(['inmueble', 'contrato', 'indice'])
    ->middleware('auth')
    ->name('inmuebles.contratos.archivos.show');
Route::resource('inmuebles', InmobiliarioController::class);
Route::resource('inmuebles.contratos', ContratoController::class);
