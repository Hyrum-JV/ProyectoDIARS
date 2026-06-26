<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompraDetalle extends Model
{
    use HasFactory;

    protected $fillable = ['compra_id', 'product_id', 'cantidad', 'precio_unitario'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}