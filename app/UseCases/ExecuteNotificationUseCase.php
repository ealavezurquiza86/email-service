<?php

namespace App\UseCases;

use App\Models\ConfiguracionCasoUso;
use App\Models\CuentaSmtp;
use App\Models\PlantillaCorreo;
use App\Mail\DynamicNotificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use InvalidArgumentException;

/**
 * Caso de Uso central para la orquestación y envío de notificaciones.
 * * Aplica las reglas de negocio, resuelve las sobrescrituras (overrides)
 * permitidas, inyecta credenciales dinámicamente y despacha el correo.
 */
class ExecuteNotificationUseCase
{
    /**
     * Ejecuta el proceso de armado y envío de correo.
     *
     * @param ConfiguracionCasoUso $config El modelo de configuración precargado con sus relaciones.
     * @param array $payload Los datos validados recibidos del cliente (API).
     * @return array La lista de destinatarios finales a los que se envió el correo.
     * @throws InvalidArgumentException Si se intenta sobrescribir un valor no permitido o faltan datos.
     */
    public function execute(ConfiguracionCasoUso $config, array $payload): array
    {
        // 1. Resolver Remitente (Cuenta SMTP)
        $cuenta = $this->resolverCuentaSmtp($config, $payload);

        // 2. Resolver Plantilla de Correo
        $plantilla = $this->resolverPlantilla($config, $payload);

        // 3. Resolver Destinatarios
        $destinatarios = $this->resolverDestinatarios($config, $payload);

        // 4. Inyectar configuración SMTP dinámica al vuelo
        $this->setDynamicSmtpConfig($cuenta);

        // 5. Compilar plantilla y enviar
        $parametros = $payload['parametros'] ?? [];
        $correo = new DynamicNotificationMail(
            $plantilla,
            $parametros,
            $cuenta->email_remitente,
            $cuenta->nombre_remitente
        );

        Mail::mailer('smtp_dinamico')->to($destinatarios)->send($correo);

        return $destinatarios;
    }

    /**
     * Resuelve la cuenta SMTP a utilizar, aplicando overrides si están permitidos.
     */
    private function resolverCuentaSmtp(ConfiguracionCasoUso $config, array $payload): CuentaSmtp
    {
        if (isset($payload['remitente_clave_override'])) {
            if (!$config->permite_override_remitente) {
                throw new InvalidArgumentException("Violación de regla: El caso de uso '{$config->clave}' no permite sobrescribir el remitente.");
            }
            // Buscamos la cuenta por la 'clave' (slug) que envió el cliente
            return CuentaSmtp::where('clave', $payload['remitente_clave_override'])->firstOrFail();
        }

        return $config->cuentaSmtpDefault;
    }

    /**
     * Resuelve la plantilla HTML a utilizar, aplicando overrides si están permitidos.
     */
    private function resolverPlantilla(ConfiguracionCasoUso $config, array $payload): PlantillaCorreo
    {
        if (isset($payload['plantilla_clave_override'])) {
            if (!$config->permite_override_plantilla) {
                throw new InvalidArgumentException("Violación de regla: El caso de uso '{$config->clave}' no permite sobrescribir la plantilla.");
            }
            // Buscamos la plantilla por la 'clave' (slug) que envió el cliente
            return PlantillaCorreo::where('clave', $payload['plantilla_clave_override'])->firstOrFail();
        }

        return $config->plantillaDefault;
    }

    /**
     * Resuelve la lista de destinatarios finales.
     */
    private function resolverDestinatarios(ConfiguracionCasoUso $config, array $payload): array
    {
        $destinatarios = $config->listaDefault ? $config->listaDefault->correos : [];

        if (isset($payload['destinatarios_override']) && !empty($payload['destinatarios_override'])) {
             if (!$config->permite_override_destinatarios) {
                throw new InvalidArgumentException("Violación de regla: El caso de uso '{$config->clave}' no permite sobrescribir los destinatarios.");
            }
            $destinatarios = $payload['destinatarios_override'];
        }

        if (empty($destinatarios)) {
            throw new InvalidArgumentException("Error de lógica: No hay destinatarios definidos para el envío de esta notificación.");
        }

        return $destinatarios;
    }

    /**
     * Sobrescribe la configuración de correo de Laravel en tiempo de ejecución
     * aislando la lógica de infraestructura de la lógica de negocio.
     */
    private function setDynamicSmtpConfig(CuentaSmtp $cuenta): void
    {
        config(['mail.mailers.smtp_dinamico' => [
            'transport'  => 'smtp',
            'host'       => $cuenta->host,
            'port'       => $cuenta->puerto,
            'encryption' => $cuenta->encriptacion,
            'username'   => $cuenta->usuario,
            'password'   => Crypt::decryptString($cuenta->password), // Desencriptación segura on-the-fly
        ]]);
    }
}
