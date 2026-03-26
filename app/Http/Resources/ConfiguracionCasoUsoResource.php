<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\ConfiguracionCasoUso
 */
class ConfiguracionCasoUsoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'clave' => $this->clave,
            // Agrupamos las configuraciones por defecto
            'configuracion_default' => [
                'id_cuenta_smtp' => $this->id_cuenta_smtp_default,
                'id_plantilla'   => $this->id_plantilla_default,
                'id_lista'       => $this->id_lista_default,
            ],
            // Agrupamos los permisos
            'permisos_override' => [
                'remitente'     => $this->permite_override_remitente,
                'destinatarios' => $this->permite_override_destinatarios,
                'plantilla'     => $this->permite_override_plantilla,
            ],
            'creado_el'      => $this->created_at?->toIso8601String(),
            'actualizado_el' => $this->updated_at?->toIso8601String(),

            // Si las relaciones están cargadas, las anexamos
            'cuenta_smtp' => new CuentaSmtpResource($this->whenLoaded('cuentaSmtpDefault')),
        ];
    }
}
