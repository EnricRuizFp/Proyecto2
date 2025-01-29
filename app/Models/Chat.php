<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $table = 'chat';

    protected $fillable = [
        'partida_id',
        'usuario_id',
        'mensaje',
        'fecha',
    ];

    // Relaciones

    public function partida()
    {
        return $this->belongsTo(Partida::class, 'partida_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
