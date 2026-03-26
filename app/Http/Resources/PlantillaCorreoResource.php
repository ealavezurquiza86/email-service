<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlantillaCorreoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'clave'                => $this->clave,
            'nombre'               => $this->nombre,
            'asunto'               => $this->asunto,
            'cuerpo_html'          => $this->cuerpo_html,
            'variables_requeridas' => $this->variables_requeridas ?? [],
            'variables_opcionales' => $this->variables_opcionales ?? [],
            'creado_el'            => $this->created_at?->toIso8601String(),
            'actualizado_el'       => $this->updated_at?->toIso8601String(),
        ];
    }
}
