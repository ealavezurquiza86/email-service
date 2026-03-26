<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo que representa la bitácora de auditoría y seguimiento de envíos.
 * Utiliza UUIDs para no exponer secuencias numéricas a las aplicaciones cliente.
 */
class BitacoraEnvio extends Model
{
    // Trait esencial: le dice a Laravel que genere automáticamente
    // un UUID al crear un nuevo registro, en lugar de esperar un entero autoincremental.
    use HasUuids;

    /**
     * El nombre exacto de la tabla asociada al modelo.
     * Previene el comportamiento por defecto de pluralización en inglés.
     *
     * @var string
     */
    protected $table = 'bitacora_envios';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'configuracion_caso_uso_id', // El FK numérico que enlaza al caso de uso
        'payload_recibido',
        'destinatarios_finales',
        'estado',
        'detalle_error',
    ];

    /**
     * Los atributos que deben ser convertidos (casteados) a tipos nativos.
     * Esto permite guardar arrays como JSON en la base de datos de forma automática,
     * y cuando consultamos el modelo, Laravel nos devuelve arrays asociativos de PHP.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payload_recibido' => 'array',
        'destinatarios_finales' => 'array',
    ];

    /**
     * Define la relación con la tabla de configuración de casos de uso.
     * Una bitácora pertenece a un caso de uso específico.
     *
     * @return BelongsTo
     */
    public function configuracionCasoUso(): BelongsTo
    {
        // Pasamos explícitamente el nombre de la columna foránea ('configuracion_caso_uso_id')
        // ya que no sigue la convención estricta de Laravel (que buscaría 'configuracion_caso_uso_id').
        return $this->belongsTo(ConfiguracionCasoUso::class, 'configuracion_caso_uso_id');
    }
}
