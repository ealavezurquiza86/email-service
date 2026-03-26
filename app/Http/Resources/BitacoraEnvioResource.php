<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\BitacoraEnvio
 */
class BitacoraEnvioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'tracking_id'           => $this->id,
            'caso_uso'              => [
                'id'    => $this->configuracion_caso_uso_id,
                // Si la relación está cargada, mostramos la clave, si no, nulo
                'clave' => $this->whenLoaded('configuracionCasoUso', fn() => $this->configuracionCasoUso->clave),
            ],
            'estado'                => $this->estado,
            'payload_procesado'     => $this->payload_recibido,
            'destinatarios_finales' => $this->destinatarios_finales,
            'detalle_error'         => $this->detalle_error,
            'fecha_registro'        => $this->created_at?->toIso8601String(),
            'ultima_actualizacion'  => $this->updated_at?->toIso8601String(),
        ];
    }
}
