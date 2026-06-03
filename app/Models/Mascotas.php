<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mascotas extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'nombre',
        'tipo_animal',
        'raza',
        'color',
        'edad',
        'sexo',
        'foto',
    ];

    public function cliente()
    {
        return $this->belongsTo(Clientes::class, 'cliente_id', 'id');
    }

    public function citas()
    {
        return $this->hasMany(Citas::class, 'mascota_id', 'id');
    }

    public function historiasClinicas()
    {
        return $this->hasMany(HistoriaClinica::class, 'mascota_id', 'id');
    }

    public function vacunas()
    {
        return $this->hasMany(Vacuna::class, 'mascota_id', 'id');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'mascota_id', 'id');
    }

    public function seguimientos()
    {
        return $this->hasMany(Seguimiento::class, 'mascota_id', 'id');
    }
}
