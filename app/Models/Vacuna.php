<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vacuna extends Model
{
    use HasFactory;

    protected $table = 'vacunas';

    protected $fillable = [
        'mascota_id',
        'historia_clinica_id',
        'nombre',
        'estado_aplicacion',
        'fecha_programada',
        'fecha_aplicacion',
        'proxima_dosis',
    ];

    protected $casts = [
        'fecha_programada' => 'date',
        'fecha_aplicacion' => 'date',
        'proxima_dosis' => 'date',
    ];

    public function mascota()
    {
        return $this->belongsTo(Mascotas::class, 'mascota_id', 'id');
    }

    public function historiaClinica()
    {
        return $this->belongsTo(HistoriaClinica::class, 'historia_clinica_id', 'id');
    }

    public function seguimientos()
    {
        return $this->hasMany(Seguimiento::class, 'vacuna_id', 'id');
    }
}
