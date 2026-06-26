<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Supplier; // Importamos el modelo de proveedores
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class AlmacenController extends Controller
{
    public function index()
    {
        // Traemos los productos incluyendo la relación 'supplier' definida en tu modelo Product
        $productos = Product::with('supplier')->orderBy('id', 'desc')->get();
        
        // Traemos todos los proveedores para llenar la lista desplegable en Vue
        $proveedores = Supplier::orderBy('razon_social', 'asc')->get(); 

        return Inertia::render('Almacen', [
            'productosDb' => $productos,
            'proveedoresDb' => $proveedores
        ]);
    }

    public function store(Request $request)
    {
        $producto = new Product();
        $producto->codigo = $request->input('codigo');
        $producto->producto = $request->input('producto');
        $producto->unid = $request->input('unid');
        $producto->precio = $request->input('precio');
        $producto->stock = $request->input('stock');
        $producto->proveedor_id = $request->input('proveedor_id'); // Guardamos el proveedor seleccionado

        $stock = (int) $request->input('stock');
        if ($stock <= 0) {
            $producto->estado = 'Agotado';
        } elseif ($stock < 100) {
            $producto->estado = 'Menor al mínimo';
        } else {
            $producto->estado = 'OK';
        }

        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('productos', 'public');
            $producto->imagen = '/storage/' . $path;
        }

        $producto->save();
        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $producto = Product::findOrFail($id);

        $producto->producto = $request->input('producto');
        $producto->unid = $request->input('unid');
        $producto->precio = $request->input('precio');
        $producto->stock = $request->input('stock');
        $producto->proveedor_id = $request->input('proveedor_id'); // Actualizamos el proveedor

        $stock = (int) $request->input('stock');
        if ($stock <= 0) {
            $producto->estado = 'Agotado';
        } elseif ($stock < 100) {
            $producto->estado = 'Menor al mínimo';
        } else {
            $producto->estado = 'OK';
        }

        if ($request->hasFile('imagen')) {
            if ($producto->imagen) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $producto->imagen));
            }
            $path = $request->file('imagen')->store('productos', 'public');
            $producto->imagen = '/storage/' . $path;
        }

        $producto->save();
        return redirect()->back();
    }

    public function destroy($id)
    {
        $producto = Product::findOrFail($id);
        
        if ($producto->imagen) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $producto->imagen));
        }
        
        $producto->delete();
        return redirect()->back();
    }

    public function storeProveedor(Request $request)
    {
        $request->validate([
            'razon_social' => 'required|string|max:255',
            'ruc' => 'required|string|max:11',
        ]);

        Supplier::create($request->all());
        
        return redirect()->back();
    }

    public function updateProveedor(Request $request, $id)
    {
        $request->validate([
            'razon_social' => 'required|string|max:255',
            'ruc' => 'required|string|max:11',
        ]);

        $proveedor = Supplier::findOrFail($id);
        $proveedor->update($request->all());

        return redirect()->back();
    }

    public function destroyProveedor($id)
    {
        $proveedor = Supplier::findOrFail($id);
        
        Product::where('proveedor_id', $id)->update(['proveedor_id' => null]);
        
        $proveedor->delete();

        return redirect()->back();
    }
}