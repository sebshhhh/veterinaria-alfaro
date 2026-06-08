<?php

use App\Http\Controllers\AtencionRapidaController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CitasController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoriasClinicasController;
use App\Http\Controllers\MascotasController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecetasController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\SeguimientosController;
use App\Http\Controllers\TratamientosController;
use App\Http\Controllers\VacunasController;
use App\Http\Controllers\VentasController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::get('/storage/{path}', function (string $path) {
    $path = str_replace(['../', '..\\'], '', $path);
    $publicFile = public_path('storage/' . $path);
    $storageFile = storage_path('app/public/' . $path);

    $file = is_file($publicFile) ? $publicFile : $storageFile;

    abort_unless(is_file($file), 404);

    return response()->file($file);
})->where('path', '.*')->name('storage.public');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    // Sesion
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Paneles principales
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/reportes', [ReportesController::class, 'index'])->name('reportes.index');
    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
    Route::patch('/configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');

    // Perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Gestion de clientes y pacientes
    Route::resource('clientes', ClientesController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('mascotas', [MascotasController::class, 'index'])->name('mascotas.index');
    Route::get('mascotas/create/{cliente_id}', [MascotasController::class, 'create'])->name('mascotas.create');
    Route::post('mascotas', [MascotasController::class, 'store'])->name('mascotas.store');
    Route::get('mascotas/{mascota}/edit', [MascotasController::class, 'edit'])->name('mascotas.edit');
    Route::put('mascotas/{mascota}', [MascotasController::class, 'update'])->name('mascotas.update');
    Route::delete('mascotas/{mascota}', [MascotasController::class, 'destroy'])->name('mascotas.destroy');
    Route::get('mascotas/show-json/{id}', [MascotasController::class, 'showJson'])->name('mascotas.show-json');

    // Operacion diaria
    Route::resource('citas', CitasController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('citas/clientes', [CitasController::class, 'storeCliente'])->name('citas.clientes.store');
    Route::post('citas/mascotas', [CitasController::class, 'storeMascota'])->name('citas.mascotas.store');
    Route::patch('citas/{cita}/estado', [CitasController::class, 'updateEstado'])->name('citas.estado');
    Route::post('citas/{cita}/atender', [CitasController::class, 'atender'])->name('citas.atender');

    Route::resource('atencion-rapida', AtencionRapidaController::class)->only(['index', 'store']);
    Route::post('atencion-rapida/clientes', [AtencionRapidaController::class, 'storeCliente'])->name('atencion-rapida.clientes.store');
    Route::post('atencion-rapida/mascotas', [AtencionRapidaController::class, 'storeMascota'])->name('atencion-rapida.mascotas.store');

    // Historial clinico y automatizacion medica
    Route::resource('historias-clinicas', HistoriasClinicasController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('tratamientos', TratamientosController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('recetas', RecetasController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('vacunas', VacunasController::class)->only(['index', 'store', 'update', 'destroy']);

    // Controles de retorno
    Route::get('seguimientos', [SeguimientosController::class, 'index'])->name('seguimientos.index');
    Route::post('seguimientos', [SeguimientosController::class, 'store'])->name('seguimientos.store');
    Route::put('seguimientos/{seguimiento}', [SeguimientosController::class, 'update'])->name('seguimientos.update');
    Route::patch('seguimientos/{seguimiento}/aplicar-vacuna', [SeguimientosController::class, 'aplicarVacuna'])->name('seguimientos.aplicar-vacuna');
    Route::patch('seguimientos/{seguimiento}/cerrar', [SeguimientosController::class, 'cerrar'])->name('seguimientos.cerrar');
    Route::delete('seguimientos/{seguimiento}', [SeguimientosController::class, 'destroy'])->name('seguimientos.destroy');

    // Gestion comercial
    Route::resource('productos', ProductosController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('ventas', VentasController::class)->only(['index', 'store', 'update', 'destroy']);
});
