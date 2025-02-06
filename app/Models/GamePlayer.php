<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GamePlayer extends Model
{
    protected $table = 'game_players';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'game_id',
        'user_id',
        'joined',
    ];

    /**
     * Relación: Un GamePlayer pertenece a un Game.
     */
    public function game()
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    /**
     * Relación: Un GamePlayer pertenece a un User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación: Un GamePlayer tiene muchos Movements.
     */
    public function moves()
    {
        return $this->hasMany(Move::class, 'game_player_id');
    }
}
