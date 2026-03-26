<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlantillaCorreoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('plantillas_correo');

        return [
            'clave' => [
                'required', 'string', 'max:255',
                Rule::unique('plantillas_correo', 'clave')->ignore($id)
            ],
            'nombre'                   => 'required|string|max:255',
            'asunto'                   => 'required|string|max:255',
            'cuerpo_html'              => 'required|string',
            'variables_requeridas'     => 'nullable|array',
            'variables_requeridas.*'   => 'string|max:100|regex:/^[a-zA-Z0-9_]+$/',
            'variables_opcionales'     => 'nullable|array',
            'variables_opcionales.*'   => 'string|max:100|regex:/^[a-zA-Z0-9_]+$/',
        ];
    }

    public function messages(): array
    {
        return [
            'variables_requeridas.*.regex' => 'Los nombres de las variables requeridas solo pueden contener letras, números y guiones bajos (sin el signo $).',
            'variables_opcionales.*.regex' => 'Los nombres de las variables opcionales solo pueden contener letras, números y guiones bajos (sin el signo $).',
        ];
    }
}
