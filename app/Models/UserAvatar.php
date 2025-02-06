<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAvatar extends Model
{
    protected $table = 'user_avatars';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'avatar_id',
        'updated',
    ];

    /**
     * Relación: Un UserAvatar pertenece a un User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación: Un UserAvatar pertenece a un Avatar.
     */
    public function avatar()
    {
        return $this->belongsTo(Avatar::class, 'avatar_id');
    }
}
