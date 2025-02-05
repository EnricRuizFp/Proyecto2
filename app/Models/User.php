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

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, InteractsWithMedia;

    protected $table = 'users';

    protected $fillable = [
        // Campos originales de 'users'
        'name',
        'surname1',
        'surname2',
        'alias',
        'email',
        'password',
        // Campos que vienen de 'Usuario.php'
        'username',        // si quieres conservarlo separado de alias
        'nacionalidad',
        'fecha_registro',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Métodos importantes del antiguo User
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new UserResetPasswordNotification($token));
    }

    // Relación que ya existía en User
    public function assignaments()
    {
        return $this->hasMany(UserAssignment::class, 'user_id');
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
     | Relaciones heredadas de "Usuario.php"
     |---------------------------------------------------------
     | Suponiendo que renombraste las tablas/columns a user_id,
     | o ajustaste la FK en tus otras tablas.
     */

    // Ejemplo: en 'partidas' la FK es 'creada_por' (no user_id/usuario_id)
    public function partidasCreadas()
    {
        return $this->hasMany(Partida::class, 'creada_por');
    }

    // Ejemplo: en 'jugador_partidas' renombraste la FK a 'user_id'
    public function jugadorPartidas()
    {
        return $this->hasMany(JugadorPartida::class, 'user_id');
    }

    public function observadorPartidas()
    {
        return $this->hasMany(ObservadorPartida::class, 'user_id');
    }

    public function chats()
    {
        return $this->hasMany(Chat::class, 'user_id');
    }

    public function rankings()
    {
        return $this->hasMany(Ranking::class, 'user_id');
    }

    // Ejemplo: relación con la pivot "user_avatars"
    public function userAvatars() 
    {
        return $this->hasMany(UserAvatar::class, 'user_id');
    }

    public function avatares()
    {
        // belongsToMany con la pivot "user_avatars"
        return $this->belongsToMany(
            Avatar::class,
            'user_avatars',  // tabla pivot renombrada
            'user_id',       // clave local en pivot
            'avatar_id'      // clave de la otra tabla
        )
        ->withTimestamps()
        ->withPivot('actualizado');
    }
}
