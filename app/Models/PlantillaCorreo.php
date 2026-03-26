<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlantillaCorreo extends Model
{
    // Forzamos el nombre de la tabla (Laravel buscaría 'plantilla_correos')
    protected $table = 'plantillas_correo';

    protected $fillable = [
        'clave',
        'nombre',
        'asunto',
        'cuerpo_html',
        'variables_requeridas',
        'variables_opcionales',
    ];

    protected $casts = [
        'variables_requeridas' => 'array',
        'variables_opcionales' => 'array',
    ];
}
