<?php

namespace App\Jobs;

use App\Models\BitacoraEnvio;
use App\UseCases\ExecuteNotificationUseCase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $bitacoraId;

    // --- CONFIGURACIÓN DEL PATRÓN RETRY ---

    /**
     * El número de veces que el trabajo puede intentarse.
     * @var int
     */
    public $tries = 3;

    /**
     * El número de segundos a esperar antes de reintentar el trabajo (Backoff).
     * Puedes usar un array para backoff exponencial: [10, 30, 60] (espera 10s, luego 30s, luego 60s).
     * @var array
     */
    public $backoff = [15, 60];

    public function __construct(string $bitacoraId)
    {
        $this->bitacoraId = $bitacoraId;
    }

    public function handle(ExecuteNotificationUseCase $useCase): void
    {
        $bitacora = BitacoraEnvio::with([
            'configuracionCasoUso.cuentaSmtpDefault',
            'configuracionCasoUso.plantillaDefault',
            'configuracionCasoUso.listaDefault'
        ])->findOrFail($this->bitacoraId);

        // Si es un reintento (attempts > 1), lo marcamos como REINTENTANDO
        $estadoInicial = $this->attempts() > 1 ? 'REINTENTANDO' : 'PROCESANDO';
        $bitacora->update(['estado' => $estadoInicial]);

        try {
            $destinatariosFinales = $useCase->execute(
                $bitacora->configuracionCasoUso,
                $bitacora->payload_recibido
            );

            $bitacora->update([
                'estado' => 'ENVIADO',
                'destinatarios_finales' => $destinatariosFinales,
                'detalle_error' => null // Limpiamos errores previos si los hubo
            ]);

        } catch (Throwable $e) {
            // Logueamos el error parcial
            $bitacora->update([
                'estado' => 'ERROR',
                'detalle_error' => "Intento " . $this->attempts() . " falló: " . $e->getMessage()
            ]);

            // ESTO ES CRUCIAL:
            // Al lanzar (throw) la excepción de nuevo, le decimos a Laravel que el Job falló.
            // Laravel revisará la variable $tries. Si quedan intentos, lo pondrá en espera (backoff).
            // Si ya se acabaron los intentos, llamará al método failed().
            throw $e;
        }
    }

    /**
     * Maneja el fallo definitivo del trabajo (después de agotar los reintentos).
     */
    public function failed(Throwable $exception): void
    {
        $bitacora = BitacoraEnvio::find($this->bitacoraId);

        if ($bitacora) {
            $bitacora->update([
                'estado' => 'FALLO_DEFINITIVO',
                'detalle_error' => "Fallo tras " . $this->tries . " intentos. Último error: " . $exception->getMessage()
            ]);
        }
    }
}
