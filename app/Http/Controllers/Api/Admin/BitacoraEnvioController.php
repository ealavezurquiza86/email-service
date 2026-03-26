<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BitacoraEnvioResource;
use App\Models\BitacoraEnvio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Auditoría y Bitácora de Envíos.
 * Endpoints de solo lectura para consulta operativa.
 * @tags Auditoría
 */
class BitacoraEnvioController extends Controller
{
    /**
     * Consultar bitácora
     *
     * Obtiene el historial de envíos. Permite filtrado opcional por estado.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = BitacoraEnvio::with('configuracionCasoUso:id,clave');

        // Filtrado dinámico (KISS)
        if ($request->has('estado')) {
            $query->where('estado', $request->query('estado'));
        }

        // Siempre ordenar por los más recientes primero
        $bitacora = $query->latest()->paginate(20);

        return BitacoraEnvioResource::collection($bitacora);
    }

    /**
     * Detalle de envío en bitácora
     *
     * Muestra toda la traza de un intento de envío específico utilizando su Tracking ID.
     */
    public function show(BitacoraEnvio $bitacora): BitacoraEnvioResource
    {
        $bitacora->load('configuracionCasoUso');
        return new BitacoraEnvioResource($bitacora);
    }
}
