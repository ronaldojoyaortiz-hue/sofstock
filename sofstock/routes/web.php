<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\DetalleFacturaController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UsuarioController;

Route::redirect('/', '/dashboard')->name('home');
Route::get('/dashboard', DashboardController::class)->name('dashboard.index');
Route::view('/welcome', 'welcome')->name('welcome.index');

Route::resource('categorias', CategoriaController::class);
Route::resource('roles', RolController::class);
Route::resource('productos', ProductoController::class);
Route::resource('facturas', FacturaController::class);
Route::resource('detallefacturas', DetalleFacturaController::class);
Route::resource('proveedores', ProveedorController::class);
Route::resource('usuarios', UsuarioController::class);
