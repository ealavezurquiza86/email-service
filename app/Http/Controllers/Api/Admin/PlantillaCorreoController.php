<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlantillaCorreoRequest;
use App\Http\Resources\PlantillaCorreoResource;
use App\Models\PlantillaCorreo;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * Gestión del catálogo de Plantillas de Correo.
 * @tags Catálogos
 */
class PlantillaCorreoController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $plantillas = PlantillaCorreo::latest()->paginate(15);
        return PlantillaCorreoResource::collection($plantillas);
    }

    public function store(PlantillaCorreoRequest $request): PlantillaCorreoResource
    {
        $plantilla = PlantillaCorreo::create($request->validated());
        return new PlantillaCorreoResource($plantilla);
    }

    public function show(PlantillaCorreo $plantillasCorreo): PlantillaCorreoResource
    {
        return new PlantillaCorreoResource($plantillasCorreo);
    }

    public function update(PlantillaCorreoRequest $request, PlantillaCorreo $plantillasCorreo): PlantillaCorreoResource
    {
        $plantillasCorreo->update($request->validated());
        return new PlantillaCorreoResource($plantillasCorreo);
    }

    public function destroy(PlantillaCorreo $plantillasCorreo): Response
    {
        $plantillasCorreo->delete();
        return response()->noContent();
    }
}
