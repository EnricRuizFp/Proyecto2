<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    use HasFactory;

    protected $table = 'usuarios';

    protected $fillable = [
        'username',
        'name',
        'surname',
        'email',
        'password',
        'nacionalidad',
        'fecha_registro',
    ];

    // Relaciones

    public function partidasCreadas()
    {
        return $this->hasMany(Partida::class, 'creada_por');
    }

    public function jugadorPartidas()
    {
        return $this->hasMany(JugadorPartida::class, 'usuario_id');
    }

    public function observadorPartidas()
    {
        return $this->hasMany(ObservadorPartida::class, 'usuario_id');
    }

    public function chats()
    {
        return $this->hasMany(Chat::class, 'usuario_id');
    }

    public function rankings()
    {
        return $this->hasMany(Ranking::class, 'usuario_id');
    }

    public function usuariosAvatares()
    {
        return $this->hasMany(UsuarioAvatar::class, 'usuario_id');
    }

    public function avatares()
    {
        return $this->belongsToMany(Avatar::class, 'usuarios_avatares', 'usuario_id', 'avatar_id')
                    ->withTimestamps()
                    ->withPivot('actualizado');
    }
}
