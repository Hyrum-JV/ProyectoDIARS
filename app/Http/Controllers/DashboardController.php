<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\Compra;
use App\Models\Product;
use App\Models\Cliente;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Cálculo de métricas financieras y de registros
        $totalVentas = Venta::sum('total');
        $totalCompras = Compra::sum('total');
        $cantidadProductos = Product::count();
        $cantidadClientes = Cliente::count();

        // 2. Conteo de productos en estado crítico (Stock menor a 100 o Agotado)
        $productosCriticos = Product::where('stock', '<', 100)
            ->orWhereIn('estado', ['Menor al mínimo', 'Agotado'])
            ->count();

        // 3. Obtención de las últimas 5 ventas con los datos de sus respectivos clientes
        $ultimasVentas = Venta::with('cliente')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        // 4. Obtención de las últimas 5 órdenes de compra registradas
        $ultimasCompras = Compra::orderBy('id', 'desc')
            ->take(5)
            ->get();

        // 5. Envío de la información estructurada hacia el componente de Vue
        return Inertia::render('Dashboard', [
            'stats' => [
                'totalVentas' => (float)$totalVentas,
                'totalCompras' => (float)$totalCompras,
                'cantidadProductos' => $cantidadProductos,
                'cantidadClientes' => $cantidadClientes,
                'productosCriticos' => $productosCriticos,
            ],
            'ultimasVentas' => $ultimasVentas,
            'ultimasCompras' => $ultimasCompras
        ]);
    }
}