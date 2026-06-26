<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'razon_social',
        'ruc',
        'telefono',
        'direccion'
    ];

    
    public function products()
    {
        return $this->hasMany(Product::class, 'proveedor_id');
    }
}