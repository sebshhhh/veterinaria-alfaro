<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Veterinarios extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'licencia',
        'telefono',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function citas()
    {
        return $this->hasMany(Citas::class, 'veterinario_id', 'id');
    }

    public function seguimientos()
    {
        return $this->hasMany(Seguimiento::class, 'veterinario_id', 'id');
    }

    public function getNombreAttribute()
    {
        return optional($this->user)->name ?: 'Profesional ' . $this->id;
    }
}
