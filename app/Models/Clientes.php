<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clientes extends Model
{
    use HasFactory;

    protected $fillable = [
        'dni',
        'nombre',
        'telefono',
        'direccion',
        'email',
    ];

    // 🔹 Relación: un cliente tiene muchas mascotas
    public function mascotas()
    {
        return $this->hasMany(Mascotas::class, 'cliente_id', 'id');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'cliente_id', 'id');
    }
}
