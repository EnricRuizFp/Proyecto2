<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $table = 'games';

    protected $fillable = [
        'creation_date',
        'is_public',
        'is_finished',
        'finished_date',
        'user_id',
    ];

    // Relaciones

    public function creator()
    {
        return $this->belongsTo(User::class, foreignKey: 'user_id');
    }

    public function players()
    {
        return $this->hasMany(PlayerGame::class, 'game_id');
    }

    public function observers()
    {
        return $this->hasMany(ObserverGame::class, foreignKey: 'game_id');
    }

    public function moves()
    {
        return $this->hasMany(Move::class, foreignKey: 'game_id');
    }

    public function chats()
    {
        return $this->hasMany(Chat::class, 'game_id');
    }
}
