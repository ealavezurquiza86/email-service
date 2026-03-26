<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ...
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // 1. Manejo de Errores de Validación (FormRequests)
        $exceptions->renderable(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => 'Datos de entrada inválidos.',
                    'detalles' => $e->errors()
                ], 422);
            }
        });

        // 2. Manejo de Recursos no encontrados (findOrFail)
        $exceptions->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                // Verificamos si la excepción base fue por un modelo de Eloquent
                if ($e->getPrevious() instanceof ModelNotFoundException) {
                    return response()->json([
                        'error' => 'El recurso solicitado no existe o no pudo ser encontrado.'
                    ], 404);
                }

                return response()->json(['error' => 'Ruta (Endpoint) no encontrada.'], 404);
            }
        });

        // 3. Manejo de Errores de Negocio (Ej. Overrides no permitidos o faltan variables en plantilla)
        $exceptions->renderable(function (\InvalidArgumentException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => 'Inconsistencia en la regla de negocio.',
                    'detalle' => $e->getMessage() // Aquí sí es seguro mostrar el mensaje de nuestro UseCase
                ], 400);
            }
        });

        // 4. Forzar que cualquier otra excepción no manejada devuelva JSON en la API
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return $request->is('api/*');
        });

        // NOTA DE SEGURIDAD:
        // Si APP_DEBUG=false en tu archivo .env, Laravel por defecto ocultará las
        // excepciones críticas (como errores SQL) y devolverá un mensaje genérico "Server Error"
        // con código 500. No necesitamos configurarlo aquí, el framework lo hace por nosotros.
    })->create();
