<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\SendNotificationRequest;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Recibe la petición, la encola y devuelve un ID de seguimiento.
     *
     * @param SendNotificationRequest $request
     * @return JsonResponse
     */
    public function send(SendNotificationRequest $request): JsonResponse
    {
        $payload = $request->validated();

        $bitacoraId = $this->notificationService->queueNotification($payload);

        return response()->json([
            'message' => 'Notificación encolada exitosamente.',
            'tracking_id' => $bitacoraId
        ], 202); // 202 Accepted es el código semántico correcto para asincronía
    }

    /**
     * Consulta el estado de un envío previo usando su ID de seguimiento (UUID).
     * @param string $trackingId
     * @return JsonResponse
     */
    public function status(string $trackingId): JsonResponse
    {
        $bitacora = $this->notificationService->getBitacoraById($trackingId);

        return response()->json([
            'tracking_id' => $bitacora->id,
            'estado' => $bitacora->estado,
            'detalle_error' => $bitacora->detalle_error,
            'fecha_actualizacion' => $bitacora->updated_at,
        ]);
    }

    public function resend(string $trackingId): JsonResponse
    {
        $nuevoTrackingId = $this->notificationService->resendNotification($trackingId);

        return response()->json([
            'message' => 'Notificación encolada para reenvío.',
            'tracking_id_original' => $trackingId,
            'tracking_id' => $nuevoTrackingId
        ], 202);
    }
}
