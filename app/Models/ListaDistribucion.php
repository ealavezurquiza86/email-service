<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListaDistribucion extends Model
{
    // Forzamos el nombre de la tabla (Laravel buscaría 'lista_distribucions')
    protected $table = 'listas_distribucion';

    protected $fillable = [
        'clave',
        'nombre',
        'correos',
    ];

    // Indicamos que 'correos' se debe manejar como un array, aunque en BD sea JSON
    protected $casts = [
        'correos' => 'array',
    ];
}
