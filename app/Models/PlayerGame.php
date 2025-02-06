<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerGame extends Model
{
    use HasFactory;

    protected $table = 'player_games';

    protected $fillable = [
        'game_id',
        'user_id',
        'joined',
    ];

    // Relaciones

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function game()
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    public function moves()
    {
        return $this->hasMany(Move::class, 'player_game_id');
    }
}
