<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListaDistribucionRequest;
use App\Http\Resources\ListaDistribucionResource;
use App\Models\ListaDistribucion;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * Gestión del catálogo de Listas de Distribución.
 * @tags Catálogos
 */
class ListaDistribucionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $listas = ListaDistribucion::latest()->paginate(15);
        return ListaDistribucionResource::collection($listas);
    }

    public function store(ListaDistribucionRequest $request): ListaDistribucionResource
    {
        $lista = ListaDistribucion::create($request->validated());
        return new ListaDistribucionResource($lista);
    }

    public function show(ListaDistribucion $listasDistribucion): ListaDistribucionResource
    {
        return new ListaDistribucionResource($listasDistribucion);
    }

    public function update(ListaDistribucionRequest $request, ListaDistribucion $listasDistribucion): ListaDistribucionResource
    {
        $listasDistribucion->update($request->validated());
        return new ListaDistribucionResource($listasDistribucion);
    }

    public function destroy(ListaDistribucion $listasDistribucion): Response
    {
        $listasDistribucion->delete();
        return response()->noContent();
    }
}
