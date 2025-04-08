<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Move;
use App\Models\Chat;

class Game extends Model
{
    use HasFactory; // Si usas factories en pruebas

    protected $table = 'games';
    protected $primaryKey = 'id';
    public $timestamps = false; // No usa created_at ni updated_at

    protected $fillable = [
        'code',
        'creation_date',
        'start_date',  // Añadir nueva columna
        'is_public',
        'is_finished',
        'end_date',
        'winner',
        'points',
        'created_by'
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_finished' => 'boolean',
        'creation_date' => 'datetime',
        'start_date' => 'datetime',    // Añadir casting
        'end_date' => 'datetime',
    ];

    /**
     * Generar código único de 4 caracteres antes de crear un juego.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($game) {
            do {
                $code = Str::upper(Str::random(4)); // Genera solo mayúsculas
            } while (self::where('code', $code)->exists());

            $game->code = $code;
        });
    }

    /**
     * Un Game pertenece a un usuario creador.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Un Game pertenece a un usuario ganador (puede ser NULL).
     */
    public function winner()
    {
        return $this->belongsTo(User::class, 'winner');
    }

    /**
     * Un Game tiene muchos jugadores.
     */
    public function players()
    {
        return $this->belongsToMany(User::class, 'game_players', 'game_id', 'user_id')
                    ->withPivot('joined');
    }

    /**
     * Un Game tiene muchos espectadores.
     */
    public function observers()
    {
        return $this->belongsToMany(User::class, 'game_viewers', 'game_id', 'user_id')
                    ->withPivot('joined');
    }

    /**
     * Un Game tiene muchos movimientos.
     */
    public function moves()
    {
        return $this->hasMany(Move::class, 'game_id');
    }

    /**
     * Un Game tiene muchos chats.
     */
    public function chats()
    {
        return $this->hasMany(Chat::class, 'game_id');
    }
}
