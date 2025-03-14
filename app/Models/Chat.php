<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $table = 'chats';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'game_id',
        'user_id',
        'message',
        'date',
    ];

    /**
     * Relación: Un Chat pertenece a un Game.
     */
    public function game()
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    /**
     * Relación: Un Chat pertenece a un User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
