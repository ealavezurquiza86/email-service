<?php

namespace App\Services;

use App\Models\BitacoraEnvio;
use App\Models\ConfiguracionCasoUso;
use App\Jobs\ProcessNotificationJob;
use App\Exceptions\NotificationBusinessException;
use App\Models\PlantillaCorreo;

class NotificationService
{
    /**
     * Registra un nuevo intento de envío, validando el contrato previamente.
     *
     * @param array $payload Datos validados del FormRequest.
     * @return string UUID de la bitácora.
     * @throws NotificationBusinessException
     */
    public function queueNotification(array $payload): string
    {
        // 1. Obtener el caso de uso con su plantilla por defecto
        $casoUso = ConfiguracionCasoUso::with('plantillaDefault')->where('clave', $payload['caso_uso_clave'])->firstOrFail();

        // 2. Validamos permisos y si mandaron una clave de lista, la transformamos a array de correos
        $payload = $this->validarYResolverOverridesSincronos($casoUso, $payload);

        // 3. Resolver qué plantilla se usará (por defecto o el override)
        $plantilla = $this->resolverPlantillaSincrona($casoUso, $payload);

        // 4. Validar que el payload cumpla con el contrato de la plantilla (variables requeridas y opcionales)
        $parametrosSanitizados = $this->validarYFiltrarParametros($plantilla, $payload['parametros'] ?? []);

        // Sobrescribimos el array original con solo los parámetros que la plantilla entiende, para evitar que el payload tenga datos basura o maliciosos.
        // Esto también hace que el payload almacenado en la bitácora sea más limpio y útil para auditoría.
        $payload['parametros'] = $parametrosSanitizados;

        // 5. Si está correcto, guardamos la intención y encolamos
        $bitacora = BitacoraEnvio::create([
            'configuracion_caso_uso_id' => $casoUso->id,
            'payload_recibido'          => $payload,
            'estado'                    => 'PENDIENTE'
        ]);

        ProcessNotificationJob::dispatch($bitacora->id);

        return $bitacora->id;
    }


    /**
     * Obtiene una bitacora por su ID (UUID).
     * @param string $id
     * @return BitacoraEnvio|null
     */
    public function getBitacoraById(string $id): ?BitacoraEnvio
    {
        return BitacoraEnvio::findOrFail($id);
    }

    /**
     * Clona el payload de un envío previo y genera un nuevo intento asíncrono.
     * Mantiene la inmutabilidad de la bitácora original.
     *
     * @param string $trackingIdOriginal El UUID del envío original.
     * @return string El nuevo UUID de seguimiento.
     */
    public function resendNotification(string $trackingIdOriginal): string
    {
        // Si no lo encuentra, lanzará ModelNotFoundException automáticamente
        $bitacoraOriginal = BitacoraEnvio::findOrFail($trackingIdOriginal);

        $nuevaBitacora = BitacoraEnvio::create([
            'configuracion_caso_uso_id' => $bitacoraOriginal->configuracion_caso_uso_id,
            'payload_recibido'          => $bitacoraOriginal->payload_recibido,
            'estado'                    => 'PENDIENTE',
            'detalle_error'             => 'Reintento manual del envío original: ' . $trackingIdOriginal
        ]);

        ProcessNotificationJob::dispatch($nuevaBitacora->id);

        return $nuevaBitacora->id;
    }

    /**
     * Valida de forma síncrona los permisos de sobrescritura y resuelve
     * polimórficamente los destinatarios (convirtiendo Strings a Arrays).
     *
     * @param ConfiguracionCasoUso $casoUso
     * @param array $payload
     * @return array Payload modificado con los destinatarios resueltos.
     * @throws NotificationBusinessException
     */
    private function validarYResolverOverridesSincronos(ConfiguracionCasoUso $casoUso, array $payload): array
    {
        // Validar Remitente
        if (isset($payload['remitente_clave_override'])) {
            if (!$casoUso->permite_override_remitente) {
                throw new NotificationBusinessException(
                    "Violación de regla: El caso de uso '{$casoUso->clave}' no permite sobrescribir la cuenta SMTP remitente."
                );
            }

            // Validamos que exista ahora mismo (Lanza 404 si no existe)
            CuentaSmtp::where('clave', $payload['remitente_clave_override'])->firstOrFail();
        }

        // Validar y Resolver Destinatarios
        if (isset($payload['destinatarios_override']) && !empty($payload['destinatarios_override'])) {
            if (!$casoUso->permite_override_destinatarios) {
                throw new NotificationBusinessException(
                    "Violación de regla: El caso de uso '{$casoUso->clave}' no permite enviar a destinatarios dinámicos."
                );
            }

            // Si es un string (clave de lista de distribución), la resolvemos a sus correos
            if (is_string($payload['destinatarios_override'])) {
                // Buscamos la lista en la BD. Si no existe, lanza 404.
                $lista = ListaDistribucion::where('clave', $payload['destinatarios_override'])->firstOrFail();

                // Sobrescribimos el string original por el array de correos real
                $payload['destinatarios_override'] = $lista->correos;
            }
        }

        return $payload;
    }

    /**
     * Determina qué plantilla se utilizará para poder validar sus variables.
     *
     * @param ConfiguracionCasoUso $casoUso El caso de uso con su plantilla por defecto ya cargada.
     * @param array $payload El payload recibido del cliente, que puede contener overrides.
     * @return PlantillaCorreo La plantilla que se usará para este envío (ya sea la default o la override).
     * @throws NotificationBusinessException Si se intenta usar un override no permitido.
     */
    private function resolverPlantillaSincrona(ConfiguracionCasoUso $casoUso, array $payload): PlantillaCorreo
    {
        if (isset($payload['plantilla_clave_override'])) {
            if (!$casoUso->permite_override_plantilla) {
                throw new NotificationBusinessException(
                    "Violación de regla: El caso de uso '{$casoUso->clave}' no permite sobrescribir la plantilla."
                );
            }
            return PlantillaCorreo::where('clave', $payload['plantilla_clave_override'])->firstOrFail();
        }

        return $casoUso->plantillaDefault;
    }

    /**
     * Valida que existan los requeridos y devuelve un array solo con los
     * parámetros (requeridos y opcionales) que la plantilla acepta, descartando el resto.
     *
     * @param PlantillaCorreo $plantilla
     * @param array $parametrosRecibidos
     * @return array Parámetros sanitizados.
     * @throws NotificationBusinessException
     */
    private function validarYFiltrarParametros(PlantillaCorreo $plantilla, array $parametrosRecibidos): array
    {
        $requeridas = $plantilla->variables_requeridas ?? [];
        $opcionales = $plantilla->variables_opcionales ?? [];

        $llavesRecibidas = array_keys($parametrosRecibidos);

        // 1. Validar FALTANTES (Rechazar si falta algo crítico)
        $faltantes = array_diff($requeridas, $llavesRecibidas);
        if (!empty($faltantes)) {
            $nombresFaltantes = implode(', ', $faltantes);
            throw new NotificationBusinessException(
                "Error de Contrato: La plantilla '{$plantilla->clave}' requiere los parámetros obligatorios: [{$nombresFaltantes}]. Faltan en tu petición.",
                422
            );
        }

        // 2. FILTRAR Y DESCARTAR EXCEDENTES (Postel's Law)
        // Creamos una lista maestra de todo lo que la plantilla entiende
        $todasPermitidas = array_merge($requeridas, $opcionales);

        // IntersectKey devuelve solo los elementos de $parametrosRecibidos cuyas llaves
        // existan en nuestro array de llaves permitidas. (array_flip convierte los valores permitidos en llaves)
        return array_intersect_key($parametrosRecibidos, array_flip($todasPermitidas));

    }
}
