<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Compra;          
use App\Models\CompraDetalle;   
use Inertia\Inertia;

class OrdenCompraController extends Controller
{
    public function create()
    {
        $productos = Product::orderBy('producto', 'asc')->get();
        
        $compras = Compra::with('detalles')->orderBy('id', 'desc')->get();

        return Inertia::render('RegistrarOrdenCompra', [
            'productosDb' => $productos,
            'comprasDb' => $compras // Enviamos el historial al frontend
        ]);
    }

    // --- NUEVA FUNCIÓN PARA GUARDAR LA ORDEN Y REDIRIGIR ---
    public function store(Request $request)
    {
        // 1. Guardar la orden general (Cabecera)
        $compra = new Compra();
        $compra->fecha_obra = $request->fecha_obra;
        $compra->ubicacion = $request->ubicacion;
        $compra->total = $request->total;
        $compra->save();

        // 2. Guardar cada producto del carrito (Detalles)
        foreach ($request->carrito as $item) {
            $detalle = new CompraDetalle();
            $detalle->compra_id = $compra->id;
            $detalle->product_id = $item['id'];
            $detalle->cantidad = $item['cantidadSeleccionada'];
            $detalle->precio_unitario = $item['precio'];
            $detalle->save();
        }

        // 3. Redirigir a la pestaña de Venta enviando el ID que acabamos de crear
        return redirect()->route('orden-venta.create', ['compra_id' => $compra->id]);
    }
}