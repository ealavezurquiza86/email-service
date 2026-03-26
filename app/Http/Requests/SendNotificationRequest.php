<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SendNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Autenticación vía SSO se manejará en middleware posterior
    }

    public function rules(): array
    {
        $rules = [
            'caso_uso_clave'            => 'required|string|exists:configuracion_casos_uso,clave',
            'plantilla_clave_override'  => 'nullable|string|exists:plantillas_correo,clave',
            'remitente_clave_override'  => 'nullable|string|exists:cuentas_smtp,clave',
            'parametros'                => 'nullable|array',
        ];

        // Validación Polimórfica: Evaluamos el tipo de dato recibido para destinatarios
        if ($this->has('destinatarios_override')) {
            if (is_array($this->input('destinatarios_override'))) {
                // Si mandan un arreglo: Máximo 10 elementos y todos deben ser emails
                $rules['destinatarios_override']   = 'array|max:10';
                $rules['destinatarios_override.*'] = 'email';
            } else {
                // Si mandan texto: Validamos que sea una clave existente en el catálogo
                $rules['destinatarios_override']   = 'string|exists:listas_distribucion,clave';
            }
        }

        return $rules;
    }

    // Personalizamos los mensajes de error para que sean más claros para los clientes que consumen la API
    public function messages(): array
    {        return [
            'caso_uso_clave.required' => 'El campo "caso_uso_clave" es obligatorio.',
            'caso_uso_clave.string' => 'El campo "caso_uso_clave" debe ser una cadena de texto.',
            'caso_uso_clave.exists' => 'El valor proporcionado para "caso_uso_clave" no corresponde a ningún caso de uso configurado.',
            'plantilla_clave_override.string' => 'El campo "plantilla_clave_override" debe ser una cadena de texto.',
            'plantilla_clave_override.exists' => 'El valor proporcionado para "plantilla_clave_override" no corresponde a ninguna plantilla de correo configurada.',
            'remitente_clave_override.string' => 'El campo "remitente_clave_override" debe ser una cadena de texto.',
            'remitente_clave_override.exists' => 'El valor proporcionado para "remitente_clave_override" no corresponde a ningún remitente configurado.',
            'parametros.array' => 'El campo "parametros" debe ser un arreglo de clave-valor con los datos necesarios para la plantilla de correo.',
            // Mensajes específicos para el campo polimórfico "destinatarios_override"
            'destinatarios_override.array'   => 'La lista de destinatarios debe ser un arreglo de correos.',
            'destinatarios_override.max'     => 'Por seguridad, no se permiten más de 10 correos ingresados manualmente.',
            'destinatarios_override.string'  => 'El override de destinatarios debe ser una clave de lista o un arreglo de correos.',
            'destinatarios_override.exists'  => 'La clave de la lista de distribución proporcionada no existe.',
            'destinatarios_override.*.email' => 'Uno o más correos en la lista no tienen un formato válido.',
        ];
    }
}
