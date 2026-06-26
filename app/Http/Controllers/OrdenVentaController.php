<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Compra;
use App\Models\Cliente;
use App\Models\Venta;
use App\Models\Product;
use App\Models\VentaDetalle;
use Inertia\Inertia;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\ComprobanteVenta;

class OrdenVentaController extends Controller
{
    public function create(Request $request)
    {
        $compra_id = $request->query('compra_id');
        
        if (!$compra_id) {
            return redirect()->route('orden-compra.create');
        }

        $compra = Compra::with('detalles.product')->findOrFail($compra_id);

        $clientes = Cliente::orderBy('nombre', 'asc')->get();

        return Inertia::render('RegistrarOrdenVenta', [
            'compraDb' => $compra,
            'clientesDb' => $clientes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'compra_id' => 'required|exists:compras,id',
            'ruc' => 'required|string|max:11',
            'nombre' => 'required|string',
        ]);

        $cliente = Cliente::firstOrCreate(
            ['ruc' => $request->ruc],
            ['nombre' => $request->nombre, 'telefono' => $request->telefono]
        );

        $compra = Compra::with('detalles')->findOrFail($request->compra_id);

        $venta = new Venta();
        $venta->compra_id = $compra->id;
        $venta->cliente_id = $cliente->id;
        $venta->total = $compra->total;
        $venta->save();

        foreach ($compra->detalles as $detalle) {
            // Guardamos el detalle de la venta final
            VentaDetalle::create([
                'venta_id' => $venta->id,
                'product_id' => $detalle->product_id,
                'cantidad' => $detalle->cantidad,
                'precio_unitario' => $detalle->precio_unitario,
                'subtotal' => $detalle->cantidad * $detalle->precio_unitario,
            ]);

            // Descontamos el stock
            $producto = Product::find($detalle->product_id);
            if ($producto) {
                $producto->stock -= $detalle->cantidad;
                if ($producto->stock <= 0) {
                    $producto->stock = 0;
                    $producto->estado = 'Agotado';
                } elseif ($producto->stock < 100) {
                    $producto->estado = 'Menor al mínimo';
                } else {
                    $producto->estado = 'OK';
                }
                $producto->save();
            }
        }

        $compra->estado = 'Pagado';
        $compra->save();

        $venta->load('cliente');

        // Le enviamos un correo inventado. En Mailtrap
        Mail::to('cliente@empresa.com')->send(new ComprobanteVenta($venta, $compra));

        return back(); 
    }

    // Funcion para descargar el comprobante de venta en PDF
    public function descargarPdf($id)
    {
        $compra = Compra::with('detalles.product')->findOrFail($id);
        $venta = Venta::with('cliente')->where('compra_id', $id)->firstOrFail();

        // Carga una vista visual y la convierte en archivo PDF
        $pdf = Pdf::loadView('pdf.comprobante', compact('compra', 'venta'));
        return $pdf->download('Comprobante_Venta_000'.$venta->id.'.pdf');
    }
}