<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\OrdenCompraController;
use App\Http\Controllers\OrdenVentaController;
use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// ==========================================================
// RUTAS PROTEGIDAS PARA: ADMIN (Acceso Total)
// ==========================================================
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    
    // Gestión de Usuarios
    Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios/guardar', [UserController::class, 'store'])->name('usuarios.store');
    Route::put('/usuarios/actualizar/{id}', [UserController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/eliminar/{id}', [UserController::class, 'destroy'])->name('usuarios.destroy');

});

// ==========================================================
// RUTAS PROTEGIDAS PARA: ADMIN Y JEFE DE ALMACÉN
// ==========================================================
Route::middleware(['auth', 'verified', 'role:almacen'])->group(function () {
    
    // Rutas para Almacén
    Route::get('/almacen', [AlmacenController::class, 'index'])->name('almacen.index');
    Route::post('/almacen/guardar', [AlmacenController::class, 'store'])->name('almacen.store'); 
    Route::put('/almacen/actualizar/{id}', [AlmacenController::class, 'update'])->name('almacen.update');
    Route::delete('/almacen/eliminar/{id}', [AlmacenController::class, 'destroy'])->name('almacen.destroy');

    // Rutas para Proveedores
    Route::post('/proveedores/guardar', [AlmacenController::class, 'storeProveedor'])->name('proveedores.store');
    Route::put('/proveedores/actualizar/{id}', [AlmacenController::class, 'updateProveedor'])->name('proveedores.update');
    Route::delete('/proveedores/eliminar/{id}', [AlmacenController::class, 'destroyProveedor'])->name('proveedores.destroy');
    
});

// ==========================================================
// RUTAS PROTEGIDAS PARA: ADMIN Y VENDEDOR
// ==========================================================
Route::middleware(['auth', 'verified', 'role:vendedor'])->group(function () {
    
    // Rutas de Compra
    Route::get('/RegistrarOrdenCompra', [OrdenCompraController::class, 'create'])->name('orden-compra.create');
    Route::post('/RegistrarOrdenCompra/guardar', [OrdenCompraController::class, 'store'])->name('orden-compra.store');
    
    // Rutas de Venta
    Route::get('/RegistrarOrdenVenta', [OrdenVentaController::class, 'create'])->name('orden-venta.create');
    Route::post('/RegistrarOrdenVenta/guardar', [OrdenVentaController::class, 'store'])->name('orden-venta.store');
    
    // Descargar PDF de la venta
    Route::get('/RegistrarOrdenVenta/pdf/{id}', [OrdenVentaController::class, 'descargarPdf'])->name('orden-venta.pdf');

    // Rutas de Clientes (API y CRUD SPA)
    Route::get('/api/clientes/buscar/{ruc}', [ClienteController::class, 'buscarPorRuc']);
    Route::post('/clientes/guardar', [ClienteController::class, 'store'])->name('clientes.store');
    Route::put('/clientes/actualizar/{id}', [ClienteController::class, 'update'])->name('clientes.update');
    Route::delete('/clientes/eliminar/{id}', [ClienteController::class, 'destroy'])->name('clientes.destroy');

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
