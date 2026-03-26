<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CuentaSmtp extends Model
{
    use SoftDeletes;

    // 1. Forzamos el nombre exacto de la tabla
    protected $table = 'cuentas_smtp';

    // 2. Permitimos qué campos se pueden insertar/actualizar
    protected $fillable = [
        'clave',
        'host',
        'puerto',
        'encriptacion',
        'usuario',
        'password',
        'email_remitente',
        'nombre_remitente',
    ];

    // Ocultamos el password por seguridad cuando el modelo se convierta a JSON o Array
    protected $hidden = [
        'password',
    ];
}
