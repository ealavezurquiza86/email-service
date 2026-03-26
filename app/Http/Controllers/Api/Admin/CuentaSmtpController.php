<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CuentaSmtpRequest;
use App\Http\Resources\CuentaSmtpResource;
use App\Models\CuentaSmtp;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;

/**
 * Gestión del catálogo de cuentas SMTP.
 * @tags Catálogos
 */
class CuentaSmtpController extends Controller
{
    /**
     * Listar cuentas SMTP
     *
     * Devuelve una lista paginada de todas las cuentas SMTP registradas en el sistema.
     */
    public function index(): AnonymousResourceCollection
    {
        $cuentas = CuentaSmtp::latest()->paginate(15);
        return CuentaSmtpResource::collection($cuentas);
    }

    /**
     * Crear cuenta SMTP
     *
     * Registra una nueva configuración de servidor de correo. La contraseña será encriptada automáticamente.
     */
    public function store(CuentaSmtpRequest $request): CuentaSmtpResource
    {
        $data = $request->validated();

        // Encriptación estricta antes de persistir
        $data['password'] = Crypt::encryptString($data['password']);

        $cuenta = CuentaSmtp::create($data);

        return new CuentaSmtpResource($cuenta);
    }

    /**
     * Obtener cuenta SMTP
     *
     * Muestra los detalles de una configuración SMTP específica mediante su ID.
     */
    public function show(CuentaSmtp $cuentasSmtp): CuentaSmtpResource
    {
        return new CuentaSmtpResource($cuentasSmtp);
    }

    /**
     * Actualizar cuenta SMTP
     *
     * Actualiza la información de una cuenta. Si se envía el campo password, se actualizará encriptado, de lo contrario se conserva el anterior.
     */
    public function update(CuentaSmtpRequest $request, CuentaSmtp $cuentasSmtp): CuentaSmtpResource
    {
        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = Crypt::encryptString($data['password']);
        }

        $cuentasSmtp->update($data);

        return new CuentaSmtpResource($cuentasSmtp);
    }

    /**
     * Eliminar cuenta SMTP
     *
     * Realiza un borrado lógico (Soft Delete) de la cuenta SMTP.
     */
    public function destroy(CuentaSmtp $cuentasSmtp): Response
    {
        // Eloquent manejará el SoftDelete automáticamente gracias al Trait en el modelo.
        $cuentasSmtp->delete();

        return response()->noContent(); // Devuelve 204 No Content (Estándar REST)
    }
}
