<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
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
        'username',
        'name',
        'email',
        'password',
        'surname1',
        'surname2',
        'nationality',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'nationality' => 'string',
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
        $this->addMediaCollection('users-avatars')
            ->singleFile()
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

    // public function assignaments()
    // {
    //     return $this->hasMany(UserAssignment::class, 'user_id');
    // }

    //  Relation with games

    public function partidasCreadas()
    {
        return $this->hasMany(Game::class, 'creator_id');
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
            Move::class,
            GamePlayer::class,
            'user_id',
            'game_player_id',
            'id',
            'id'
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
            ->withTimestamps();
    }

    public function getAvatarUrlAttribute()
    {
        return $this->avatares->isNotEmpty()
            ? $this->avatares->first()->getUrl()
            : asset('images/placeholder.jpg');
    }

    // Método para obtener el avatar actual
    public function getCurrentAvatarUrl()
    {
        try {
            $currentAvatar = $this->avatares()->latest()->first();
            if ($currentAvatar) {
                $mediaUrl = $currentAvatar->getFirstMediaUrl('avatars');
                return !empty($mediaUrl) ? $mediaUrl : asset('images/placeholder.jpg');
            }
            return asset('images/placeholder.jpg');
        } catch (\Exception $e) {
            Log::error('Error getting avatar URL: ' . $e->getMessage());
            return asset('images/placeholder.jpg');
        }
    }
}
