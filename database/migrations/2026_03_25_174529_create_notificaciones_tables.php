<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cuentas_smtp', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique()->comment('Ej. COMPRAS_O365, VENTAS_GMAIL');
            $table->string('host');
            $table->integer('puerto');
            $table->enum('encriptacion', ['tls', 'ssl'])->nullable();
            $table->string('usuario');
            $table->text('password'); // Se guardará encriptada con Crypt::encryptString()
            $table->string('email_remitente');
            $table->string('nombre_remitente');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('plantillas_correo', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique()->comment('Ej. BIENVENIDA_V1, RECUPERAR_PWD');
            $table->string('nombre');
            $table->string('asunto');
            $table->longText('cuerpo_html');
            $table->json('variables_requeridas')->nullable()->comment('Ej. ["nombre", "token"]');
            $table->json('variables_opcionales')->nullable()->comment('Ej. ["mensaje_extra", "link_ayuda"]');
            $table->timestamps();
        });

        Schema::create('listas_distribucion', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique()->comment('Ej. TODOS_VENTAS, ADMINS_TI');
            $table->string('nombre');
            $table->json('correos'); // Array de correos en JSON
            $table->timestamps();
        });

        Schema::create('configuracion_casos_uso', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique()->comment('Ej. REPORTE_COMPRA');
            $table->foreignId('id_cuenta_smtp_default')->constrained('cuentas_smtp');
            $table->foreignId('id_plantilla_default')->constrained('plantillas_correo');
            $table->foreignId('id_lista_default')->nullable()->constrained('listas_distribucion');
            $table->boolean('permite_override_remitente')->default(false);
            $table->boolean('permite_override_destinatarios')->default(false);
            $table->boolean('permite_override_plantilla')->default(false);
            $table->timestamps();
        });

        Schema::create('bitacora_envios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('configuracion_caso_uso_id')->constrained('configuracion_casos_uso');
            $table->json('payload_recibido');
            $table->json('destinatarios_finales')->nullable();
            $table->enum('estado', ['PENDIENTE', 'PROCESANDO', 'ENVIADO', 'ERROR', 'REINTENTANDO', 'FALLO_DEFINITIVO'])->default('PENDIENTE');
            $table->text('detalle_error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacora_envios');
        Schema::dropIfExists('configuracion_casos_uso');
        Schema::dropIfExists('listas_distribucion');
        Schema::dropIfExists('plantillas_correo');
        Schema::dropIfExists('cuentas_smtp');
    }
};
