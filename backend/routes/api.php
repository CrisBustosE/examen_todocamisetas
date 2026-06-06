<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\ShirtController;

// Rutas para la API
Route::prefix('v1')->group(function () {

    // ======== Health Check ======== //
    // Ruta para validar disponibilidad de la API
    Route::get('/health', HealthController::class);

    // ======== Clientes ======== //
    // apiResource genera automaticamente las rutas para GET (index), POST (store), PUT/PATCH (update), DELETE (destroy)
    Route::apiResource('clients', ClientController::class);

    // ======== Camisetas ======== //
    Route::apiResource('shirts', ShirtController::class);

    // Tambien se pide listar camisetas por clientes
    Route::get('clients/{id}/shirts', [ShirtController::class, 'byClient']);

    // En caso de querer rerstaurar camisetas con SoftDelete
    //Route::patch('shirts/{id}/restore', [ShirtController::class, 'restore']);
});
