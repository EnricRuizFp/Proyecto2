<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObservadorPartida extends Model
{
    use HasFactory;

    protected $table = 'observadores_partidas';

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
}
