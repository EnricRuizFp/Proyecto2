<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\UserResetPasswordNotification;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

/**
 * Modelo User de tu proyecto, con cambios para:
 *  - 'Moves' (antes Movements)
 *  - 'GameViewers' (antes GameObservers)
 */
class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, InteractsWithMedia;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'surname1',
        'surname2',
        'alias',
        'email',
        'password',
        'username',
        'nationality',
        'register_date',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Método para notificación de reset de contraseña personalizado
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new UserResetPasswordNotification($token));
    }

    /**
     * Librería de medios (Spatie)
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images/users')
             ->useFallbackUrl('/images/placeholder.jpg')
             ->useFallbackPath(public_path('/images/placeholder.jpg'));
    }

    public function registerMediaConversions(Media $media = null): void
    {
        if (env('RESIZE_IMAGE') === true) {
            $this->addMediaConversion('resized-image')
                 ->width(env('IMAGE_WIDTH', 300))
                 ->height(env('IMAGE_HEIGHT', 300));
        }
    }

    /*
     |---------------------------------------------------------
     | Relations with User
     |---------------------------------------------------------
     */

    public function assignaments()
    {
        return $this->hasMany(UserAssignment::class, 'user_id');
    }

    //  Relation with games

    public function partidasCreadas()
    {
        // Ajusta nombres si estás en inglés
        return $this->hasMany(Game::class, 'created_by');
    }

    //  Relation with game_players
    
    public function gamePlayers()
    {
        return $this->hasMany(GamePlayer::class, 'user_id');
    }

    //  Relation with game_viewers

    public function gameViewers()
    {
        return $this->hasMany(GameViewer::class, 'user_id');
    }

    // Si además quieres "los Games en los que el User es viewer":
    public function viewedGames()
    {
        // belongsToMany, asumiendo 'game_viewers' es tu tabla intermedia
        // con 'game_id' y 'user_id' como FKs:
        return $this->belongsToMany(
            Game::class,
            'game_viewers',
            'user_id',
            'game_id'
        );
    }

    // Relation with chats

    public function chats()
    {
        return $this->hasMany(Chat::class, 'user_id');
    }

    // Relation with rankings

    public function rankings()
    {
        return $this->hasMany(Ranking::class, 'user_id');
    }

    //  Relation with moves

    public function moves()
    {
        return $this->hasManyThrough(
            Move::class,          // Modelo final
            GamePlayer::class,    // Modelo intermedio
            'user_id',            // FK en 'game_players'
            'game_player_id',     // FK en 'moves'
            'id',                 // PK local en 'users' (por defecto 'id')
            'id'                  // PK local en 'game_players' (por defecto 'id')
        );
    }

    //  Relation with user_avatars

    public function userAvatars()
    {
        return $this->hasMany(UserAvatar::class, 'user_id');
    }

    public function avatares()
    {
        return $this->belongsToMany(
            Avatar::class,
            'user_avatars',  
            'user_id',       
            'avatar_id'
        )
        ->withPivot('updated'); // si deseas acceder a la columna 'updated'
    }
}
