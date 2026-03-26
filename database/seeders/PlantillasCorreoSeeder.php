<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PlantillaCorreo;

/**
 * Puebla la tabla de plantillas_correo con diseños HTML base.
 */
class PlantillasCorreoSeeder extends Seeder
{
    public function run(): void
    {
        $plantillas = [
            [
                'clave'       => 'BIENVENIDA_V1',
                'nombre'      => 'Bienvenida Nuevo Usuario',
                'asunto'      => '¡Bienvenido a la plataforma, {{ $nombre }}!',
                // Usamos sintaxis de Blade. La variable $mensaje_extra es opcional.
                'cuerpo_html' => '
                    <div style="font-family: Arial, sans-serif; padding: 20px;">
                        <h2 style="color: #2c3e50;">¡Hola {{ $nombre }}!</h2>
                        <p>Tu cuenta ha sido creada exitosamente en nuestro sistema.</p>
                        <p>Puedes acceder con tu correo electrónico en cualquier momento.</p>
                        @if(isset($mensaje_extra))
                            <div style="background-color: #f8f9fa; padding: 10px; border-left: 4px solid #007bff;">
                                <strong>Nota importante:</strong> {{ $mensaje_extra }}
                            </div>
                        @endif
                        <hr>
                        <p style="font-size: 12px; color: #6c757d;">Este es un correo automático, por favor no respondas.</p>
                    </div>
                ',
                'variables_requeridas' => ['nombre'],
                'variables_opcionales' => ['mensaje_extra'],
            ],
            [
                'clave'       => 'RECUPERAR_PWD',
                'nombre'      => 'Recuperación de Contraseña',
                'asunto'      => 'Instrucciones para restablecer tu contraseña',
                'cuerpo_html' => '
                    <div style="font-family: Arial, sans-serif; text-align: center;">
                        <img src="https://via.placeholder.com/150x50?text=Logo+Empresa" alt="Logo">
                        <h2>Recuperación de acceso</h2>
                        <p>Hemos recibido una solicitud para restablecer tu contraseña.</p>
                        <a href="{{ $enlace_recuperacion }}" style="display: inline-block; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0;">Restablecer Contraseña</a>
                        <p>Si no solicitaste este cambio, ignora este correo.</p>
                    </div>
                ',
                'variables_requeridas' => ['enlace_recuperacion'],
                'variables_opcionales' => [], // Si no hay opcionales, puede ser array vacío o null
            ],
            [
                'clave'       => 'ALERTA_SISTEMA',
                'nombre'      => 'Alerta de Infraestructura Crítica',
                'asunto'      => 'ALERTA TÉCNICA: {{ $nivel_severidad }} - {{ $servicio_afectado }}',
                'cuerpo_html' => '
                    <div style="font-family: monospace; background-color: #2b2b2b; color: #a9b7c6; padding: 15px;">
                        <h3 style="color: #ff6b68;">⚠️ REPORTE DE INCIDENCIA</h3>
                        <ul>
                            <li><strong>Servicio:</strong> {{ $servicio_afectado }}</li>
                            <li><strong>Severidad:</strong> {{ $nivel_severidad }}</li>
                            <li><strong>Hora del Evento:</strong> {{ date("Y-m-d H:i:s") }}</li>
                        </ul>
                        <p><strong>Detalle Técnico:</strong></p>
                        <pre style="background-color: #1e1e1e; padding: 10px; border: 1px solid #555;">{{ $log_error }}</pre>
                    </div>
                ',
                'variables_requeridas' => ['nivel_severidad', 'servicio_afectado', 'log_error'],
                'variables_opcionales' => [],

            ]
        ];

        foreach ($plantillas as $plantilla) {
            PlantillaCorreo::create($plantilla);
        }
    }
}
