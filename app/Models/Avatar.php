<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Log;

class Avatar extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'avatars';
    protected $primaryKey = 'id';
    public $timestamps = true;  // Cambiar a true para activar timestamps

    // Definir constantes para los tipos de avatar
    const TYPE_DEFAULT = 'default';
    const TYPE_CUSTOM = 'custom';

    // Si ya no necesitas el campo image_path, puedes eliminarlo de $fillable
    protected $fillable = [
        'name',
        'type',
        'url'
    ];

    // Agrega el atributo 'image_route' a la serialización del modelo
    protected $appends = ['image_route'];

    /**
     * Si el avatar tiene alguna relación, como con UserAvatar, la defines aquí.
     */
    public function userAvatars()
    {
        return $this->hasMany(UserAvatar::class, 'avatar_id');
    }

    /**
     * Define la colección de medios para los avatares.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatars')
            ->useFallbackUrl('/images/placeholder.jpg') // URL de fallback si no hay imagen
            ->useFallbackPath(public_path('/images/placeholder.jpg'));
    }

    /**
     * (Opcional) Define conversiones de imagen, por ejemplo para thumbnails.
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(100)
            ->height(100);
    }

    /**
     * Accessor para obtener la URL de la imagen del avatar.
     */
    public function getImageRouteAttribute()
    {
        return $this->getFirstMediaUrl('avatars', 'thumb') ?: asset('images/avatar-placeholder.jpg');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_avatars')
            ->withTimestamps();
    }

    // Método helper para obtener la URL del avatar de manera segura
    public function getAvatarUrl()
    {
        try {
            return $this->url ?? asset('storage/avatars/default.png');
        } catch (\Exception $e) {
            Log::error('Error getting avatar URL: ' . $e->getMessage());
            return asset('images/placeholder.jpg');
        }
    }

    // Agregar scope para filtrar avatares
    public function scopeAvailableFor($query, $userId)
    {
        return $query->where('type', self::TYPE_DEFAULT)
            ->orWhere(function ($q) use ($userId) {
                $q->where('type', self::TYPE_CUSTOM)
                    ->whereHas('users', function ($subQ) use ($userId) {
                        $subQ->where('users.id', $userId);
                    });
            });
    }
}
