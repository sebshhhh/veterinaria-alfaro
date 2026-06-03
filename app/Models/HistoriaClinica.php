<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriaClinica extends Model
{
    use HasFactory;

    protected $table = 'historias_clinicas';

    protected $fillable = [
        'mascota_id',
        'cita_id',
        'origen_atencion',
        'tipo_atencion',
        'diagnostico',
        'observaciones',
        'fecha',
        'peso',
        'temperatura',
        'servicio_producto_id',
        'precio_servicio',
    ];

    protected $casts = [
        'fecha' => 'date',
        'peso' => 'decimal:2',
        'temperatura' => 'decimal:1',
        'precio_servicio' => 'decimal:2',
    ];

    public function mascota()
    {
        return $this->belongsTo(Mascotas::class, 'mascota_id', 'id');
    }

    public function cita()
    {
        return $this->belongsTo(Citas::class, 'cita_id', 'id');
    }

    public function tratamientos()
    {
        return $this->hasMany(Tratamiento::class, 'historia_clinica_id', 'id');
    }

    public function recetas()
    {
        return $this->hasMany(Receta::class, 'historia_clinica_id', 'id');
    }

    public function vacunas()
    {
        return $this->hasMany(Vacuna::class, 'historia_clinica_id', 'id');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'historia_clinica_id', 'id');
    }

    public function servicioProducto()
    {
        return $this->belongsTo(Producto::class, 'servicio_producto_id', 'id');
    }

    public function seguimientos()
    {
        return $this->hasMany(Seguimiento::class, 'historia_clinica_id', 'id');
    }
}
