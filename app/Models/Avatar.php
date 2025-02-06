<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Avatar extends Model
{
    protected $table = 'avatars';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'image_path',
    ];

    /**
     * Ejemplo de relación: un Avatar puede estar asociado a muchos UserAvatars.
     */
    public function userAvatars()
    {
        return $this->hasMany(UserAvatar::class, 'avatar_id');
    }
}
