<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JugadorPartida extends Model
{
    use HasFactory;

    protected $table = 'jugadores_partidas';

    protected $fillable = [
        'partida_id',
        'usuario_id',
        'unido',
    ];

    // Relaciones

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function partida()
    {
        return $this->belongsTo(Partida::class, 'partida_id');
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'jugador_partida_id');
    }
}
