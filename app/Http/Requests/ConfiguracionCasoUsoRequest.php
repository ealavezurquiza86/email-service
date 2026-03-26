<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConfiguracionCasoUsoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('casos_uso');

        return [
            'clave'                          => [
                'required', 'string', 'max:255',
                Rule::unique('configuracion_casos_uso', 'clave')->ignore($id)
            ],
            'id_cuenta_smtp_default'         => 'required|exists:cuentas_smtp,id',
            'id_plantilla_default'           => 'required|exists:plantillas_correo,id',
            'id_lista_default'               => 'nullable|exists:listas_distribucion,id',
            'permite_override_remitente'     => 'required|boolean',
            'permite_override_destinatarios' => 'required|boolean',
            'permite_override_plantilla'     => 'required|boolean',
        ];
    }
}
