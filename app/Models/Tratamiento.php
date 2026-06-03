<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tratamiento extends Model
{
    use HasFactory;

    protected $table = 'tratamientos';

    protected $fillable = [
        'historia_clinica_id',
        'veterinario_id',
        'descripcion',
        'costo',
        'fecha_inicio',
        'fecha_fin',
        'proximo_control',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'proximo_control' => 'date',
        'costo' => 'decimal:2',
    ];

    public function historiaClinica()
    {
        return $this->belongsTo(HistoriaClinica::class, 'historia_clinica_id', 'id');
    }

    public function veterinario()
    {
        return $this->belongsTo(Veterinarios::class, 'veterinario_id', 'id');
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'producto_tratamiento', 'tratamiento_id', 'producto_id')
            ->withPivot('cantidad')
            ->withTimestamps();
    }

    public function detalleVentas()
    {
        return $this->hasMany(DetalleVenta::class, 'tratamiento_id', 'id');
    }
}
