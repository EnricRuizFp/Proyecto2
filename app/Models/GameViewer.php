<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameViewer extends Model
{
    protected $table = 'game_viewers';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'game_id',
        'user_id',
        'joined',
    ];

    /**
     * Relación: Un GameObserver pertenece a un Game.
     */
    public function game()
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    /**
     * Relación: Un GameObserver pertenece a un User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
