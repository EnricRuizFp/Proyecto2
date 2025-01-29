<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioAvatar extends Model
{
    use HasFactory;

    protected $table = 'usuarios_avatares';

    protected $fillable = [
        'usuario_id',
        'avatar_id',
        'actualizado',
    ];

    // Relaciones

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function avatar()
    {
        return $this->belongsTo(Avatar::class, 'avatar_id');
    }
}
