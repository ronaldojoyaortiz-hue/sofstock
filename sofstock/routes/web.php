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
use App\Http\Controllers\InmobiliarioController;


Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.authenticate');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::redirect('/', '/dashboard')->name('home');
Route::get('/dashboard', DashboardController::class)->name('dashboard.index');
Route::resource('categorias', CategoriaController::class);
Route::resource('roles', RolController::class);
Route::resource('productos', ProductoController::class);
Route::resource('facturas', FacturaController::class);
Route::resource('detallefacturas', DetalleFacturaController::class);
Route::resource('proveedores', ProveedorController::class);
Route::resource('usuarios', UsuarioController::class);
Route::get('/inmuebles/{inmueble}/archivos/{indice}', [InmobiliarioController::class, 'archivo'])
    ->whereNumber(['inmueble', 'indice'])
    ->middleware('auth')
    ->name('inmuebles.archivos.show');
Route::resource('inmuebles', InmobiliarioController::class);
