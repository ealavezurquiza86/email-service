<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListaDistribucionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('listas_distribucion');

        return [
            'clave' => [
                'required', 'string', 'max:255',
                Rule::unique('listas_distribucion', 'clave')->ignore($id)
            ],
            'nombre'    => 'required|string|max:255',
            'correos'   => 'required|array|min:1',
            'correos.*' => 'required|email|max:255',
        ];
    }
}
