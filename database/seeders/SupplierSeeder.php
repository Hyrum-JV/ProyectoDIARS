<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $proveedores = [
            [
                'razon_social' => 'Tuboplast S.A.',
                'ruc' => '20100012345',
                'telefono' => '01-555-1000',
                'direccion' => 'Zona Industrial, Lima'
            ],
            [
                'razon_social' => 'Aceros Arequipa S.A.',
                'ruc' => '20100054321',
                'telefono' => '01-555-2000',
                'direccion' => 'Panamericana Sur Km 15'
            ],
            [
                'razon_social' => 'Cementos Pacasmayo S.A.A.',
                'ruc' => '20100098765',
                'telefono' => '044-555-3000',
                'direccion' => 'Planta principal, La Libertad'
            ],
        ];

        foreach ($proveedores as $item) {
            Supplier::create($item);
        }
    }
}