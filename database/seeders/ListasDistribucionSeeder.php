<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ListaDistribucion;

/**
 * Puebla la tabla listas_distribucion con grupos de destinatarios.
 */
class ListasDistribucionSeeder extends Seeder
{
    public function run(): void
    {
        $listas = [
            [
                'clave'   => 'TODO_SISTEMAS',
                'nombre'  => 'Departamento de Sistemas / TI',
                // Al insertar un array nativo, Eloquent se encarga de hacer el json_encode
                // gracias al cast 'array' definido en el modelo.
                'correos' => [
                    'sistemas@saurot.com.mx',
                    'eduardo.alavez@saurot.com.mx',
                    'ealavezurquiza@gmail.com'
                ],
            ],
            [
                'clave'   => 'DIABETEST',
                'nombre'  => 'Diabetest',
                'correos' => [
                    'sistemas@diabetest.com.mx',
                    'ealavezurquiza@gmail.com',
                ],
            ],
            [
                'clave'   => 'WLADI',
                'nombre'  => 'Auditores de Compras (Copia Oculta)',
                'correos' => [
                    'sistemas@serviciosespecializadoswladi.com',
                ],
            ]
        ];

        foreach ($listas as $lista) {
            ListaDistribucion::create($lista);
        }
    }
}
