<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Proveedores:
        // ID 1: Tuboplast S.A. (Tubos, PVC, HDPE, Conectores plásticos)
        // ID 2: Aceros Arequipa S.A. (Acero, Válvulas, Niples)
        // ID 3: Cementos Pacasmayo S.A.A. (Cemento, Concreto)

        $productos = [
            [
                'codigo' => 'PD03001', 
                'producto' => "AB. ACERO CABEZAL 10'' X DN15 PVC", 
                'unid' => 'Unidades', 
                'estado' => 'OK', 
                'precio' => 145.50,
                'proveedor_id' => 2, 
            ],
            [
                'codigo' => 'PD05111', 
                'producto' => "AB. ACERO CABEZAL 4' X 3/4' (AGUJ 25MM) AC", 
                'unid' => 'Unidades', 
                'estado' => 'OK', 
                'stock' => 3, 
                'precio' => 85.00,
                'proveedor_id' => 2, 
            ],
            [
                'codigo' => 'PD03024', 
                'producto' => "AB. ACERO CABEZAL 4'' X CIEGA AC",
                'unid' => 'Unidades', 
                'estado' => 'OK', 
                'stock' => 2, 
                'precio' => 78.20,
                'proveedor_id' => 2, 
            ],
            [
                'codigo' => 'PD05082', 
                'producto' => 'AB. ACERO CABEZAL 4" X 1/2" DN15 (AGUJ 3/4) AC', 
                'unid' => 'Unidades', 
                'estado' => 'OK', 
                'stock' => 2, 
                'precio' => 82.50,
                'proveedor_id' => 2,
            ],
            [
                'codigo' => 'PD03031', 
                'producto' => "AB. ACERO CABEZAL 6'' X CIEGA AC", 
                'unid' => 'Unidades', 
                'estado' => 'Agotado', 
                'stock' => 0, 
                'precio' => 110.00,
                'proveedor_id' => 2, 
            ],
            [
                'codigo' => 'PD01998',
                'producto' => "AB. TOMA 2 CPO PERFOR/OBTURAD 110MM DN15 HDPE 20MM",
                'unid' => 'Unidades',
                'estado' => 'OK',
                'stock' => 5,
                'precio' => 45.00,
                'proveedor_id' => 1,
            ],
            [
                'codigo' => 'PD01941',
                'producto' => "CAJA AL PISO TERMOPLASTICA C/ LOSA 4P",
                'unid' => 'Unidades',
                'estado' => 'Menor al mínimo',
                'stock' => 2,
                'precio' => 120.00,
                'proveedor_id' => 3,
            ],
            [
                'codigo' => 'PD01371',
                'producto' => "CEMENTO TIPO I",
                'unid' => 'Unidades',
                'estado' => 'OK',
                'stock' => 1,
                'precio' => 28.50,
                'proveedor_id' => 3,
            ],
            [
                'codigo' => 'PD01696',
                'producto' => "CODO PVC PRESIÓN 3/4 X 45 TIPO EMBONE JUNTA PEGADA",
                'unid' => 'Unidades',
                'estado' => 'OK',
                'stock' => 5,
                'precio' => 2.50,
                'proveedor_id' => 1,
            ],
            [
                'codigo' => 'PD01365',
                'producto' => "CONCRELISTO RES 175KG/CM2 BOLSA 40 KG",
                'unid' => 'Unidades',
                'estado' => 'Agotado',
                'stock' => 10,
                'precio' => 22.00,
                'proveedor_id' => 3,
            ],
            [
                'codigo' => 'PD01386',
                'producto' => "CONCRETO PREMEZCLADO RES 210KG/CM2 I#57S3-4",
                'unid' => 'Metros cubicos',
                'estado' => 'Agotado',
                'stock' => 0,
                'precio' => 350.00,
                'proveedor_id' => 3,
            ],
            [
                'codigo' => 'PD02087',
                'producto' => "CONECTOR MACHO TERMOP. ROSCA 1/2' P/TUBO PE 20 MM",
                'unid' => 'Unidades',
                'estado' => 'OK',
                'stock' => 5,
                'precio' => 8.50,
                'proveedor_id' => 1,
            ],
            [
                'codigo' => 'PD01708',
                'producto' => "CURVA PVC 1/2 X 90 CL-10",
                'unid' => 'Unidades',
                'estado' => 'OK',
                'stock' => 5,
                'precio' => 3.20,
                'proveedor_id' => 1,
            ],
            [
                'codigo' => 'PD02593',
                'producto' => "NIPLE SIN ROSCA 1/2'",
                'unid' => 'Unidades',
                'estado' => 'OK',
                'stock' => 5,
                'precio' => 15.00,
                'proveedor_id' => 2,
            ],
            [
                'codigo' => 'PD01744',
                'producto' => "REDUCCION DE PVC JUNTA PEGADA 3/4'' A 1/2''",
                'unid' => 'Unidades',
                'estado' => 'OK',
                'stock' => 5,
                'precio' => 4.00,
                'proveedor_id' => 1,
            ],
            [
                'codigo' => 'PD02259',
                'producto' => "TUBERIA POLIETILENO 020 MM (1/2'') SDR 11 AZUL", 
                'unid' => 'Metros', 
                'estado' => 'OK', 
                'stock' => 100, 
                'precio' => 4.20,
                'proveedor_id' => 1, 
            ],
            [
                'codigo' => 'PD01833', 
                'producto' => "TUBERIA PVC 1/2' X 5 MT CL 10 GRIS", 
                'unid' => 'Metros', 
                'estado' => 'Agotado', 
                'stock' => 3, 
                'precio' => 12.50,
                'proveedor_id' => 1, 
            ],
            [
                'codigo' => 'PD01903', 
                'producto' => "UNION PRESION ROSCA 3/4", 
                'unid' => 'Unidades', 
                'estado' => 'Agotado', 
                'stock' => 2, 
                'precio' => 6.00,
                'proveedor_id' => 1, 
            ],
            [
                'codigo' => 'PD02345', 
                'producto' => "VAL. TOMA TERMOP DN 15 C/SALIDA TUB. PE 20M HDP", 
                'unid' => 'Unidades', 
                'estado' => 'OK', 
                'stock' => 5, 
                'precio' => 35.00,
                'proveedor_id' => 1, 
            ],
            [
                'codigo' => 'PD02355', 
                'producto' => "VALV. TOMA TERMOP. DN 15 (NUEVA NORMA)", 
                'unid' => 'Unidades', 
                'estado' => 'OK', 
                'stock' => 5, 
                'precio' => 42.00,
                'proveedor_id' => 2, 
            ]
        ];

        foreach ($productos as $item) {
            Product::create($item);
        }
    }
}