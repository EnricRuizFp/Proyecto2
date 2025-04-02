<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Move extends Model
{
    protected $table = 'moves';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'game_id',
        'game_player_id',
        'coordinate',
        'result'
    ];

    /**
     * Relación: Un Movement pertenece a un Game.
     */
    public function game()
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    /**
     * Relación: Un Movement pertenece a un GamePlayer.
     */
    public function gamePlayer()
    {
        return $this->belongsTo(GamePlayer::class, 'game_player_id');
    }
}
