<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAvatar extends Model
{
    use HasFactory;

    // Ahora la tabla pivot se llama "user_avatars"
    protected $table = 'user_avatars';

    // Columns renombradas en la migración
    protected $fillable = [
        'user_id',
        'avatar_id',
        'actualizado',
    ];

    // Relaciones

    // belongsTo "User" con 'user_id'
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // belongsTo "Avatar" con 'avatar_id'
    public function avatar()
    {
        return $this->belongsTo(Avatar::class, 'avatar_id');
    }
}
