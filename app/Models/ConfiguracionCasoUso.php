<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfiguracionCasoUso extends Model
{
    // Forzamos el nombre de la tabla
    protected $table = 'configuracion_casos_uso';

    protected $fillable = [
        'clave',
        'id_cuenta_smtp_default',
        'id_plantilla_default',
        'id_lista_default',
        'permite_override_remitente',
        'permite_override_destinatarios',
        'permite_override_plantilla',
    ];

    // Casteamos los booleanos
    protected $casts = [
        'permite_override_remitente' => 'boolean',
        'permite_override_destinatarios' => 'boolean',
        'permite_override_plantilla' => 'boolean',
    ];

    // --- RELACIONES ---

    public function cuentaSmtpDefault(): BelongsTo
    {
        return $this->belongsTo(CuentaSmtp::class, 'id_cuenta_smtp_default');
    }

    public function plantillaDefault(): BelongsTo
    {
        return $this->belongsTo(PlantillaCorreo::class, 'id_plantilla_default');
    }

    public function listaDefault(): BelongsTo
    {
        return $this->belongsTo(ListaDistribucion::class, 'id_lista_default');
    }
}
