<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Move extends Model
{
    use HasFactory;

    protected $table = 'moves';

    protected $fillable = [
        'game_id',
        'player_game_id',
        'coordenada',
        'resultado',
        'realizado_en',
    ];

    // Relaciones

    public function partida()
    {
        return $this->belongsTo(Partida::class, 'partida_id');
    }

    public function jugadorPartida()
    {
        return $this->belongsTo(JugadorPartida::class, 'jugador_partida_id');
    }
}
