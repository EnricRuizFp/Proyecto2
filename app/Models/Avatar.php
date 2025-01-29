<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Avatar extends Model
{
    use HasFactory;

    protected $table = 'avatares';

    protected $fillable = [
        'nombre',
        'ruta_imagen',
    ];

    // Relaciones

    public function usuariosAvatares()
    {
        return $this->hasMany(UsuarioAvatar::class, 'avatar_id');
    }

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'usuarios_avatares', 'avatar_id', 'usuario_id')
                    ->withTimestamps()
                    ->withPivot('actualizado');
    }
}
