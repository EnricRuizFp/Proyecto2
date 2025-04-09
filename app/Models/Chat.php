<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $table = 'chats';

    // No usar timestamps ya que no están en la tabla
    public $timestamps = false;

    protected $fillable = [
        'game_id',
        'user_id',
        'message',
        'date'
    ];

    // Relación con el juego
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    // Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
