<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'producto',
        'unid',
        'estado',
        'stock',
        'precio',
        "proveedor_id",
        'imagen'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'proveedor_id');
    }
}