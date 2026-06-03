<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Citas extends Model
{
    use HasFactory;

    protected $table = 'citas';

    protected $fillable = [
        'mascota_id',
        'veterinario_id',
        'fecha',
        'hora',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function mascota()
    {
        return $this->belongsTo(Mascotas::class, 'mascota_id', 'id');
    }

    public function veterinario()
    {
        return $this->belongsTo(Veterinarios::class, 'veterinario_id', 'id');
    }

    public function historiaClinica()
    {
        return $this->hasOne(HistoriaClinica::class, 'cita_id', 'id');
    }

    public function seguimientos()
    {
        return $this->hasMany(Seguimiento::class, 'cita_id', 'id');
    }
}
