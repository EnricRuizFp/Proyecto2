<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // Importación necesaria
// Importa los demás modelos según corresponda
use App\Models\User;
use App\Models\GamePlayer;
use App\Models\GameViewer;
use App\Models\Move;
use App\Models\Chat;

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
        'code',
        'creation_date',
        'is_public',
        'is_finished',
        'end_date',
        'created_by'
    ];

    /**
     * Al crear un juego, se genera automáticamente un código
     * único de 4 caracteres (números y letras mayúsculas).
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($game) {
            do {
                $code = strtoupper(Str::random(4));
            } while (self::where('code', $code)->exists());

            $game->code = $code;
        });
    }
    protected $casts = [
        'is_public' => 'boolean',
        'is_finished' => 'boolean',
    ];
    /**
     * Relación: Un Game "pertenece" (belongsTo) a un usuario "creador".
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
        return $this->belongsToMany(User::class, 'game_players', 'game_id', 'user_id')
                    ->withPivot('joined');  // Si la tabla intermedia tiene campos adicionales
    }

    /**
     * Relación: Un Game tiene muchos GameViewers.
     */
    public function observers()
    {
        return $this->belongsToMany(User::class, 'game_viewers', 'game_id', 'user_id')
                    ->withPivot('joined');  // Si la tabla intermedia tiene campos adicionales
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
