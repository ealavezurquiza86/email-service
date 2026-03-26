<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\Admin\CuentaSmtpController;
use App\Http\Controllers\Api\Admin\ConfiguracionCasoUsoController;
use App\Http\Controllers\Api\Admin\BitacoraEnvioController;
use App\Http\Controllers\Api\Admin\PlantillaCorreoController;
use App\Http\Controllers\Api\Admin\ListaDistribucionController;


// Throttle de 60 peticiones por minuto para prevenir DoS
Route::middleware(['throttle:60,1'])->prefix('notifications')->group(function () {
    Route::post('/send', [NotificationController::class, 'send']);
    Route::get('/{trackingId}/status', [NotificationController::class, 'status']);
    Route::post('/{trackingId}/resend', [NotificationController::class, 'resend']);
});


// --- ENDPOINTS ADMINISTRATIVOS (Dashboard / Frontend Admin) ---
// Posteriormente, todas estas rutas estarán envueltas en el Middleware SSO.
Route::prefix('admin')->group(function () {

    // apiResource genera automáticamente las 5 rutas clásicas (index, store, show, update, destroy)
    Route::apiResource('cuentas-smtp', CuentaSmtpController::class)
         ->parameters(['cuentas-smtp' => 'cuentas_smtp']); // Fuerza a Laravel a inyectar el parámetro correcto para Route Model Binding

    Route::apiResource('casos-uso', ConfiguracionCasoUsoController::class);
    Route::apiResource('plantillas-correo', PlantillaCorreoController::class);
    Route::apiResource('listas-distribucion', ListaDistribucionController::class);

    // Para la bitácora, solo habilitamos explícitamente index y show
    Route::apiResource('bitacora', BitacoraEnvioController::class)->only(['index', 'show']);

});
