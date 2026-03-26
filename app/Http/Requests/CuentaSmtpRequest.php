<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property string $clave
 * @property string $host
 * @property int $puerto
 * @property string|null $encriptacion
 * @property string $usuario
 * @property string|null $password
 * @property string $email_remitente
 * @property string $nombre_remitente
 */
class CuentaSmtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Asumimos que la autorización se maneja en el Middleware
    }

    public function rules(): array
    {
        // Identificamos si es un POST (creación) o PUT/PATCH (actualización)
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');

        // Obtenemos el ID del modelo que se está editando desde la ruta
        $cuentaId = $this->route('cuentas_smtp');

        return [
            'clave' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cuentas_smtp', 'clave')->ignore($cuentaId)
            ],
            'host'             => 'required|string|max:255',
            'puerto'           => 'required|integer|min:1|max:65535',
            'encriptacion'     => 'nullable|in:tls,ssl',
            'usuario'          => 'required|string|max:255',
            // En creación es obligatorio. En actualización es opcional (solo si quieren cambiarlo)
            'password'         => $isUpdate ? 'nullable|string' : 'required|string',
            'email_remitente'  => 'required|email|max:255',
            'nombre_remitente' => 'required|string|max:255',
        ];
    }
}
