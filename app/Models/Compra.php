<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    protected $fillable = ['fecha_obra', 'ubicacion', 'total', 'estado'];

    public function detalles()
    {
        return $this->hasMany(CompraDetalle::class, 'compra_id');
    }
}