<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CuentaSmtp;
use Illuminate\Support\Facades\Crypt;

/**
 * Clase encargada de poblar la tabla cuentas_smtp con datos iniciales.
 */
class CuentasSmtpSeeder extends Seeder
{
    /**
     * Ejecuta las inserciones en la base de datos.
     *
     * @return void
     */
    public function run(): void
    {
        $cuentas = [
            [
                'clave'            => 'SMTP_TEST_DEV',
                'host'             => 'smtp.hostinger.com',
                'puerto'           => 465,
                'encriptacion'     => 'ssl', // Validado por el ENUM
                'usuario'          => 'sistemas@serviciosespecializadoswladi.com',
                // IMPORTANTE: La contraseña siempre debe insertarse encriptada
                'password'         => Crypt::encryptString('/p6Asi?rsny'),
                'email_remitente'  => 'sistemas@serviciosespecializadoswladi.com',
                'nombre_remitente' => 'Sistema Notificaciones Dev',
            ],
            // [
            //     'clave'            => 'SMTP_O365_PROD',
            //     'host'             => 'smtp.office365.com',
            //     'puerto'           => 587,
            //     'encriptacion'     => 'tls', // Validado por el ENUM
            //     'usuario'          => 'notificaciones@miempresa.com',
            //     'password'         => Crypt::encryptString('PasswordFuerte!2026'),
            //     'email_remitente'  => 'notificaciones@miempresa.com',
            //     'nombre_remitente' => 'Notificaciones Corporativas',
            // ],
            // [
            //     'clave'            => 'SMTP_INTERNO_SIN_SSL',
            //     'host'             => '10.0.0.5', // Una IP de red interna
            //     'puerto'           => 25,
            //     'encriptacion'     => null, // Permitido por el nullable()
            //     'usuario'          => 'relay_user',
            //     'password'         => Crypt::encryptString('relay_pass'),
            //     'email_remitente'  => 'alertas-ti@miempresa.com',
            //     'nombre_remitente' => 'Alertas Infraestructura TI',
            // ],
        ];

        foreach ($cuentas as $cuenta) {
            CuentaSmtp::create($cuenta);
        }
    }
}
