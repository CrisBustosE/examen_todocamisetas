<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\HealthController;

// Rutas para el CRUD de Clientes
Route::prefix('v1')->group(function () {

    // Ruta para validar disponibilidad de la API
    Route::get('/health', HealthController::class);
    // apiResource genera automaticamente las rutas para GET (index), POST (store), PUT/PATCH (update), DELETE (destroy)
    Route::apiResource('clients', ClientController::class);
});
