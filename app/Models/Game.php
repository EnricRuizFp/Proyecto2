<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    // Si tu tabla no sigue la convención "games", especifica el nombre:
    protected $table = 'games';

    // Clave primaria (por defecto es 'id', así que podrías omitirlo)
    protected $primaryKey = 'id';

    // Desactiva timestamps de Laravel si no tienes created_at / updated_at
    public $timestamps = false;

    // Campos asignables masivamente
    protected $fillable = [
        'creation_date',
        'is_public',
        'is_finished',
        'end_date',
        'created_by',
    ];

    /**
     * Relación: Un Game "pertenece" (belongsTo) a un usuario "creador".
     * Ajusta si tu modelo de usuario está en otro namespace.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación: Un Game tiene muchos GamePlayers.
     */
    public function players()
    {
        return $this->hasMany(GamePlayer::class, 'game_id');
    }

    /**
     * Relación: Un Game tiene muchos GameObservers.
     */
    public function observers()
    {
        return $this->hasMany(GameViewer::class, 'game_id');
    }

    /**
     * Relación: Un Game tiene muchos Movements.
     */
    public function moves()
    {
        return $this->hasMany(Move::class, 'game_id');
    }

    /**
     * Relación: Un Game tiene muchos Chats.
     */
    public function chats()
    {
        return $this->hasMany(Chat::class, 'game_id');
    }
}
