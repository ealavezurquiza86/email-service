<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListaDistribucionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'clave'          => $this->clave,
            'nombre'         => $this->nombre,
            'correos'        => $this->correos,
            'total_correos'  => is_array($this->correos) ? count($this->correos) : 0,
            'creado_el'      => $this->created_at?->toIso8601String(),
            'actualizado_el' => $this->updated_at?->toIso8601String(),
        ];
    }
}
