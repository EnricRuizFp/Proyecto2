<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ranking extends Model
{
    use HasFactory;

    protected $table = 'ranking';

    protected $fillable = [
        'usuario_id',
        'tipo',
        'partidas_ganadas',
        'partidas_perdidas',
        'partidas_empatadas',
        'puntos',
        'actualizado_en',
    ];

    // Relaciones

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
