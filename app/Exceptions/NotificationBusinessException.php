<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

/**
 * Excepción personalizada para violaciones de reglas de negocio en las Notificaciones.
 * Al implementar el método render(), Laravel formatea automáticamente la respuesta
 * sin necesidad de ensuciar el manejador global (bootstrap/app.php).
 */
class NotificationBusinessException extends Exception
{
    /**
     * @var int El código de estado HTTP a devolver.
     */
    protected int $codigoHttp;

    /**
     * Crea una nueva instancia de la excepción.
     *
     * @param string $mensaje El mensaje de error amigable y seguro para el cliente.
     * @param int $codigoHttp Código HTTP (400 Bad Request por defecto, o 422 para contratos).
     */
    public function __construct(string $mensaje, int $codigoHttp = 400)
    {
        parent::__construct($mensaje);
        $this->codigoHttp = $codigoHttp;
    }

    /**
     * Renderiza la excepción en una respuesta HTTP JSON estandarizada.
     * Laravel llama automáticamente a este método cuando la excepción no es atrapada.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function render($request): JsonResponse
    {
        return response()->json([
            'error'   => 'Inconsistencia en la regla de negocio.',
            'detalle' => $this->getMessage()
        ], $this->codigoHttp);
    }
}
