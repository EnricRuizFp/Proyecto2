<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partida extends Model
{
    use HasFactory;

    protected $table = 'partidas';

    protected $fillable = [
        'fecha_creacion',
        'publica',
        'finalizada',
        'fecha_finalizacion',
        'creada_por',
    ];

    // Relaciones

    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'creada_por');
    }

    public function jugadores()
    {
        return $this->hasMany(JugadorPartida::class, 'partida_id');
    }

    public function observadores()
    {
        return $this->hasMany(ObservadorPartida::class, 'partida_id');
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'partida_id');
    }

    public function chats()
    {
        return $this->hasMany(Chat::class, 'partida_id');
    }
}
