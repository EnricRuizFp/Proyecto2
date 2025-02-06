<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Avatar extends Model
{
    use HasFactory;

    protected $table = 'avatars';

    protected $fillable = [
        'name',
        'image_path',
    ];

    // Relaciones

    public function usuariosAvatares()
    {
        return $this->hasMany(UserAvatar::class, 'avatar_id');
    }

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'user_avatars', 'avatar_id', 'user_id')
                    ->withTimestamps()
                    ->withPivot('actualizado');
    }
}
