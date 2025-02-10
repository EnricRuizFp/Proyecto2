<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Avatar extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'avatars';
    protected $primaryKey = 'id';
    public $timestamps = false;

    // Si ya no necesitas el campo image_path, puedes eliminarlo de $fillable
    protected $fillable = [
        'name',
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
            ->useFallbackUrl('/images/avatar-placeholder.jpg') // URL de fallback si no hay imagen
            ->useFallbackPath(public_path('/images/avatar-placeholder.jpg'));
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
        return $this->getFirstMediaUrl('avatars') ?: asset('images/avatar-placeholder.jpg');
    }
}
