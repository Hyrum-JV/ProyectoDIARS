<div style="font-family: Arial, sans-serif; padding: 20px; color: #333;">
    <h2 style="color: #002A8D;">¡Hola, {{ $venta->cliente->nombre }}!</h2>
    <p>Te confirmamos que tu pago por <strong>S/ {{ number_format($venta->total, 2) }}</strong> ha sido procesado exitosamente en nuestra pasarela segura.</p>
    <p>Los materiales serán entregados y asignados para la obra ubicada en:</p>
    <p style="padding: 10px; background-color: #f4f4f4; border-left: 4px solid #FF7A00;">
        <strong>Ubicación:</strong> {{ $compra->ubicacion }}<br>
        <strong>Fecha acordada:</strong> {{ $compra->fecha_obra }}
    </p>
    <p>Gracias por tu preferencia.</p>
</div>