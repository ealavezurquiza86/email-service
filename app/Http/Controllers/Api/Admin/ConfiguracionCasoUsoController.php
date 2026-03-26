<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConfiguracionCasoUsoRequest;
use App\Http\Resources\ConfiguracionCasoUsoResource;
use App\Models\ConfiguracionCasoUso;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * Gestión de Configuración de Casos de Uso.
 * @tags Configuración
 */
class ConfiguracionCasoUsoController extends Controller
{
    /**
     * Listar casos de uso
     *
     * Obtiene el listado de configuraciones, incluyendo precarga de relaciones base.
     */
    public function index(): AnonymousResourceCollection
    {
        // Usamos Eager Loading para evitar el problema de N+1 consultas (YAGNI / Rendimiento)
        $casos = ConfiguracionCasoUso::with(['cuentaSmtpDefault', 'plantillaDefault'])->paginate(15);
        return ConfiguracionCasoUsoResource::collection($casos);
    }

    /**
     * Crear caso de uso
     */
    public function store(ConfiguracionCasoUsoRequest $request): ConfiguracionCasoUsoResource
    {
        $casoUso = ConfiguracionCasoUso::create($request->validated());
        return new ConfiguracionCasoUsoResource($casoUso);
    }

    /**
     * Obtener caso de uso
     */
    public function show(ConfiguracionCasoUso $casosUso): ConfiguracionCasoUsoResource
    {
        $casosUso->load(['cuentaSmtpDefault', 'plantillaDefault', 'listaDefault']);
        return new ConfiguracionCasoUsoResource($casosUso);
    }

    /**
     * Actualizar caso de uso
     */
    public function update(ConfiguracionCasoUsoRequest $request, ConfiguracionCasoUso $casosUso): ConfiguracionCasoUsoResource
    {
        $casosUso->update($request->validated());
        return new ConfiguracionCasoUsoResource($casosUso);
    }

    /**
     * Eliminar caso de uso
     */
    public function destroy(ConfiguracionCasoUso $casosUso): Response
    {
        $casosUso->delete();
        return response()->noContent();
    }
}
