<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforma el modelo CuentaSmtp a un arreglo JSON seguro.
 * @mixin \App\Models\CuentaSmtp
 */
class CuentaSmtpResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'clave'            => $this->clave,
            'host'             => $this->host,
            'puerto'           => $this->puerto,
            'encriptacion'     => $this->encriptacion,
            'usuario'          => $this->usuario,
            'email_remitente'  => $this->email_remitente,
            'nombre_remitente' => $this->nombre_remitente,
            'creado_el'        => $this->created_at?->toIso8601String(),
            'actualizado_el'   => $this->updated_at?->toIso8601String(),
            // NUNCA devolvemos el password en la API por seguridad (OWASP)
        ];
    }
}
