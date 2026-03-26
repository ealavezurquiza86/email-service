<?php

namespace App\Mail;

use App\Models\PlantillaCorreo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Blade;

class DynamicNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    private PlantillaCorreo $plantilla;
    private array $parametros;

    public function __construct(PlantillaCorreo $plantilla, array $parametros, string $fromEmail, string $fromName)
    {
        $this->plantilla = $plantilla;
        $this->parametros = $parametros;
        $this->from($fromEmail, $fromName);
        $this->subject($plantilla->asunto);
    }

    /**
     * Construye el mensaje renderizando el string de la DB con Blade.
     */
    public function build()
    {
        // Compila el HTML guardado en la DB utilizando el motor Blade en memoria
        $htmlRenderizado = Blade::render($this->plantilla->cuerpo_html, $this->parametros);

        return $this->html($htmlRenderizado);
    }
}
