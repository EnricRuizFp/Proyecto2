<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    use HasFactory;

    protected $table = 'movimientos';

    protected $fillable = [
        'partida_id',
        'jugador_partida_id',
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
