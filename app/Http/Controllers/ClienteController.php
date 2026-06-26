<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;

class ClienteController extends Controller
{
    public function buscarPorRuc($ruc)
    {
        $cliente = Cliente::where('ruc', $ruc)->first();
        
        if ($cliente) {
            return response()->json(['success' => true, 'cliente' => $cliente]);
        }
        return response()->json(['success' => false]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ruc' => 'required|string|max:11|unique:clientes',
            'nombre' => 'required|string',
        ]);

        Cliente::create($request->all());
        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'ruc' => 'required|string|max:11',
            'nombre' => 'required|string',
        ]);

        $cliente = Cliente::findOrFail($id);
        $cliente->update($request->all());

        return redirect()->back();
    }

    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();
        
        return redirect()->back();
    }
}