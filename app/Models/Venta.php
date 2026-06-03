<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'cliente_id',
        'mascota_id',
        'historia_clinica_id',
        'total',
        'metodo_pago',
        'estado',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'date',
        'total' => 'decimal:2',
    ];

    public function cliente()
    {
        return $this->belongsTo(Clientes::class, 'cliente_id', 'id');
    }

    public function mascota()
    {
        return $this->belongsTo(Mascotas::class, 'mascota_id', 'id');
    }

    public function historiaClinica()
    {
        return $this->belongsTo(HistoriaClinica::class, 'historia_clinica_id', 'id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id', 'id');
    }
}
