<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Comprobante de Venta</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #002A8D; padding-bottom: 10px; margin-bottom: 20px; }
        .header h2 { color: #002A8D; margin: 0; }
        .details { margin-bottom: 20px; width: 100%; border-collapse: collapse; }
        .details td { padding: 5px 0; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        .table th { background-color: #f4f4f4; color: #002A8D; }
        .total { text-align: right; font-weight: bold; font-size: 18px; margin-top: 20px; color: #002A8D; }
    </style>
</head>
<body>
    <div class="header">
        <h2>COMPROBANTE DE VENTA ELECTRÓNICO</h2>
        <p>Mantenimiento de Redes de Agua y Alcantarillado</p>
    </div>

    <table class="details">
        <tr>
            <td><strong>Cliente:</strong> {{ $venta->cliente->nombre }}</td>
            <td><strong>Fecha Obra:</strong> {{ $compra->fecha_obra }}</td>
        </tr>
        <tr>
            <td><strong>RUC / DNI:</strong> {{ $venta->cliente->ruc }}</td>
            <td><strong>Ubicación:</strong> {{ $compra->ubicacion }}</td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cant.</th>
                <th>P. Unit.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($compra->detalles as $item)
            <tr>
                <td>{{ $item->product->producto }}</td>
                <td>{{ $item->cantidad }}</td>
                <td>S/ {{ number_format($item->precio_unitario, 2) }}</td>
                <td>S/ {{ number_format($item->cantidad * $item->precio_unitario, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        TOTAL PAGADO: S/ {{ number_format($venta->total, 2) }}
    </div>
</body>
</html>