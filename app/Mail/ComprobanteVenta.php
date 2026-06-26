<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Venta;
use App\Models\Compra;

class ComprobanteVenta extends Mailable
{
    use Queueable, SerializesModels;

    public $venta;
    public $compra;

    public function __construct(Venta $venta, Compra $compra)
    {
        $this->venta = $venta;
        $this->compra = $compra;
    }

    public function build()
    {
        return $this->subject('¡Pago Exitoso! - Tu Comprobante de Venta')
                    ->view('emails.comprobante');
    }
}