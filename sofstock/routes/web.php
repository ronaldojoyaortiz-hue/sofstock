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
use App\Http\Controllers\SeguridadPermisosController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\BusquedaController;
use App\Http\Controllers\NotificacionController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.authenticate');
Route::redirect('/', '/login')->name('home');
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/cambiar-contrasena', [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::get('/buscar', [BusquedaController::class, 'index'])->name('search.index');
    Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::get('/notificaciones/crear', [NotificacionController::class, 'create'])->middleware('permiso:notificaciones,crear')->name('notificaciones.create');
    Route::post('/notificaciones', [NotificacionController::class, 'store'])->middleware('permiso:notificaciones,crear')->name('notificaciones.store');
    Route::patch('/notificaciones/{notificacion}/leida', [NotificacionController::class, 'marcarLeida'])->whereNumber('notificacion')->name('notificaciones.leida');
    Route::delete('/notificaciones/{notificacion}', [NotificacionController::class, 'destroy'])->whereNumber('notificacion')->middleware('permiso:notificaciones,eliminar')->name('notificaciones.destroy');
    Route::post('/cambiar-contrasena', [AuthController::class, 'updatePassword'])->name('password.update');
    Route::get('/seguridad/permisos', [SeguridadPermisosController::class, 'index'])->middleware('role_or_permission:administrador|gestionar seguridad')->name('seguridad.permisos.index');
    Route::get('/seguridad/permisos/asignaciones', [SeguridadPermisosController::class, 'assignments'])->middleware('role_or_permission:administrador|gestionar seguridad')->name('seguridad.permisos.assignments');
    Route::get('/seguridad/permisos/roles', [SeguridadPermisosController::class, 'roles'])->middleware('role_or_permission:administrador|gestionar seguridad')->name('seguridad.permisos.roles');
    Route::put('/seguridad/permisos/roles/{role}', [SeguridadPermisosController::class, 'updateRole'])->middleware('role_or_permission:administrador|gestionar seguridad')->name('seguridad.permisos.update');
    Route::get('/seguridad/permisos/roles/{role}', [SeguridadPermisosController::class, 'showRole'])->whereNumber('role')->middleware('role_or_permission:administrador|gestionar seguridad')->name('seguridad.permisos.roles.show');
    Route::delete('/seguridad/permisos/roles/{role}', [SeguridadPermisosController::class, 'destroyRole'])->whereNumber('role')->middleware('role_or_permission:administrador|gestionar seguridad')->name('seguridad.permisos.roles.destroy');
    Route::get('/seguridad/permisos/roles/{role}/permisos/{permission}', [SeguridadPermisosController::class, 'showAssignment'])->whereNumber(['role', 'permission'])->middleware('role_or_permission:administrador|gestionar seguridad')->name('seguridad.permisos.assignment.show');
    Route::delete('/seguridad/permisos/roles/{role}/permisos/{permission}', [SeguridadPermisosController::class, 'destroyAssignment'])->whereNumber(['role', 'permission'])->middleware('role_or_permission:administrador|gestionar seguridad')->name('seguridad.permisos.assignment.destroy');
    Route::get('/dashboard', DashboardController::class)->middleware('permiso:dashboard,ver')->name('dashboard.index');
    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('settings.index');
    Route::put('/configuracion', [ConfiguracionController::class, 'update'])->name('settings.update');
    Route::get('/reportes', [ReporteController::class, 'index'])->middleware('permiso:reportes,ver')->name('reports.index');
    Route::get('/reportes/descargar', [ReporteController::class, 'descargar'])->middleware('permiso:reportes,ver')->name('reports.download');
    Route::post('/reportes/generar', [ReporteController::class, 'generar'])->middleware('permiso:reportes,ver')->name('reports.generate');
    Route::put('/reportes/{reporte}', [ReporteController::class, 'actualizar'])->whereNumber('reporte')->middleware('permiso:reportes,editar')->name('reports.update');
    Route::delete('/reportes/{reporte}', [ReporteController::class, 'eliminar'])->whereNumber('reporte')->middleware('permiso:reportes,eliminar')->name('reports.destroy');
    Route::get('/reportes/{reporte}/editar', [ReporteController::class, 'editar'])->whereNumber('reporte')->middleware('permiso:reportes,editar')->name('reports.edit');
    Route::get('/reportes/{reporte}', [ReporteController::class, 'show'])->whereNumber('reporte')->middleware('permiso:reportes,ver')->name('reports.show');
    Route::post('/reportes/presupuesto', [ReporteController::class, 'guardarPresupuesto'])->middleware('permiso:reportes,editar')->name('reports.budget');
    Route::post('/reportes/egresos', [ReporteController::class, 'guardarEgreso'])->middleware('permiso:reportes,crear')->name('reports.expenses.store');
    Route::delete('/reportes/egresos/{egreso}', [ReporteController::class, 'eliminarEgreso'])->whereNumber('egreso')->middleware('permiso:reportes,eliminar')->name('reports.expenses.destroy');
    Route::post('/reportes/productos-danados', [ReporteController::class, 'guardarMerma'])->middleware('permiso:reportes,crear')->name('reports.spoilage.store');
    Route::resource('categorias', CategoriaController::class)->middleware('permiso:categorias');
    Route::resource('roles', RolController::class)->middleware('permiso:roles');
    Route::resource('productos', ProductoController::class)->middleware('permiso:productos');
    Route::resource('facturas', FacturaController::class)
        ->only(['index', 'show'])
        ->middleware('permiso:facturas');
    Route::resource('detallefacturas', DetalleFacturaController::class)
        ->except(['show'])
        ->middleware('permiso:facturas');
    Route::resource('proveedores', ProveedorController::class)->middleware('permiso:proveedores');
    Route::resource('usuarios', UsuarioController::class)->middleware('permiso:usuarios');
    Route::post('/usuarios/{id}/restablecer-contrasena', [UsuarioController::class, 'restablecerContrasena'])->middleware('permiso:usuarios,editar')->name('usuarios.restablecer-contrasena');
    Route::get('auditorias', [AuditoriaController::class, 'index'])->middleware('permiso:auditorias,ver')->name('auditorias.index');
    Route::get('auditorias/{id}', [AuditoriaController::class, 'show'])->whereNumber('id')->middleware('permiso:auditorias,ver')->name('auditorias.show');
    Route::get('/contratos', [ContratoController::class, 'all'])->middleware('permiso:contratos,ver')->name('contratos.index');
    Route::get('/contratos/crear', [ContratoController::class, 'createGlobal'])->middleware('permiso:contratos,crear')->name('contratos.create');
    Route::post('/contratos/crear', [ContratoController::class, 'selectInmueble'])->middleware('permiso:contratos,crear')->name('contratos.select-inmueble');
    Route::get('/inmuebles/{inmueble}/archivos/{indice}', [InmobiliarioController::class, 'archivo'])->whereNumber(['inmueble', 'indice'])->middleware('permiso:inmuebles,ver')->name('inmuebles.archivos.show');
    Route::get('/inmuebles/{inmueble}/contratos/{contrato}/archivos/{indice}', [ContratoController::class, 'archivo'])->whereNumber(['inmueble', 'contrato', 'indice'])->middleware('permiso:contratos,ver')->name('inmuebles.contratos.archivos.show');
    Route::resource('inmuebles', InmobiliarioController::class)->middleware('permiso:inmuebles');
    Route::resource('inmuebles.contratos', ContratoController::class)->middleware('permiso:contratos');
});
