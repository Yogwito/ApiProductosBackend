<?php

namespace App\Mail;

use App\Models\Producto;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProductCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Producto $producto;
    public User $actor;

    public function __construct(Producto $producto, User $actor)
    {
        $this->producto = $producto;
        $this->actor = $actor;
    }

    public function build()
    {
        return $this->subject('Nuevo producto creado')
            ->view('emails.product-created');
    }
}
