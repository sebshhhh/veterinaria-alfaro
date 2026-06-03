<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'stock',
        'es_servicio',
        'categoria',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'es_servicio' => 'boolean',
    ];

    public function tratamientos()
    {
        return $this->belongsToMany(Tratamiento::class, 'producto_tratamiento', 'producto_id', 'tratamiento_id')
            ->withPivot('cantidad')
            ->withTimestamps();
    }

    public function detalleVentas()
    {
        return $this->hasMany(DetalleVenta::class, 'producto_id', 'id');
    }

    public function historiasClinicasServicio()
    {
        return $this->hasMany(HistoriaClinica::class, 'servicio_producto_id', 'id');
    }
}
