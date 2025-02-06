<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $table = 'chats';

    protected $fillable = [
        'game_id',
        'user_id',
        'message',
        'date',
    ];

    // Relaciones

    public function game()
    {
        return $this->belongsTo(Game::class, foreignKey: 'game_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
