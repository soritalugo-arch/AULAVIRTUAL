<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso de que el profesor publico una evaluacion (parcial) de una materia.
 *
 * El mensaje habla de parciales y no de "nota final": el estudiante recibe el
 * correo cuando se registra o se corrige un parcial, con el promedio que va
 * acumulando hasta ese momento. Solo se manda si algo cambio de verdad, no cada
 * vez que el profesor vuelve a guardar el formulario.
 */
class NotaPublicada extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $estudiante;
    public $curso;
    public $calificacion;

    /** Etiquetas de las parciales que cambiaron ("Parcial 2", ...). */
    public $parciales = [];

    /**
     * @param  float|int|null  $calificacion  promedio a la fecha del aviso
     * @param  array<int, string>  $parciales  parciales que cambiaron
     */
    public function __construct($estudiante, $curso, $calificacion, array $parciales = [])
    {
        $this->estudiante = $estudiante;
        $this->curso = $curso;
        $this->calificacion = $calificacion;
        $this->parciales = $parciales;
    }

    public function envelope(): Envelope
    {
        // El mailable se encola: al rehidratarse puede llegar sin la lista de
        // parciales, y el asunto tiene que salir igual.
        $evaluaciones = $this->parciales ?: ['calificación'];

        return new Envelope(
            subject: 'Nueva '.implode(', ', $evaluaciones).' en '.$this->curso,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.notas.publicada',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}