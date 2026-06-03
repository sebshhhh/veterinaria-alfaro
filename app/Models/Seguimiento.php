<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seguimiento extends Model
{
    use HasFactory;

    protected $table = 'seguimientos';

    protected $fillable = [
        'mascota_id',
        'historia_clinica_id',
        'veterinario_id',
        'cita_id',
        'vacuna_id',
        'tipo',
        'origen',
        'titulo',
        'estado',
        'motivo',
        'notas',
        'evolucion',
        'fecha_inicio',
        'fecha_proximo_control',
        'hora_proximo_control',
        'dias_retorno',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_proximo_control' => 'date',
    ];

    public function mascota()
    {
        return $this->belongsTo(Mascotas::class, 'mascota_id', 'id');
    }

    public function historiaClinica()
    {
        return $this->belongsTo(HistoriaClinica::class, 'historia_clinica_id', 'id');
    }

    public function veterinario()
    {
        return $this->belongsTo(Veterinarios::class, 'veterinario_id', 'id');
    }

    public function cita()
    {
        return $this->belongsTo(Citas::class, 'cita_id', 'id');
    }

    public function vacuna()
    {
        return $this->belongsTo(Vacuna::class, 'vacuna_id', 'id');
    }
}
