<?php

namespace App\Models;

use App\Models\Roles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'dni',
        'name',
        'email',
        'password',
        'role_id',
        'foto',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function role()
    {
        return $this->belongsTo(Roles::class);
    }

    public function veterinario()
    {
        return $this->hasOne(Veterinarios::class, 'user_id', 'id');
    }

    public function getAvatarAttribute()
    {
        if ($this->foto) {
            return asset('storage/users/' . $this->foto);
        }

        $words = explode(' ', $this->name);
        $initials = '';

        foreach ($words as $word) {
            if ($word !== '') {
                $initials .= strtoupper($word[0]);
            }
        }

        return "https://ui-avatars.com/api/?name={$initials}&background=0D8ABC&color=fff&size=128";
    }
}
